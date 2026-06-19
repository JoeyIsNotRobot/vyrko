<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Legal\ConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'linkedin'];

    public function consentCreate(Request $request, string $provider): View
    {
        $provider = $this->provider($provider);

        return view('auth.social-consent', [
            'provider' => $provider,
            'providerLabel' => $this->providerLabel($provider),
        ]);
    }

    public function consentStore(Request $request, string $provider, ConsentService $consentService): RedirectResponse
    {
        $provider = $this->provider($provider);

        $request->validate([
            'terms_of_use' => ['accepted'],
            'privacy_policy' => ['accepted'],
            'ai_data_processing' => ['accepted'],
            'social_data_usage' => ['accepted'],
        ]);

        if ($request->user()) {
            $consentService->acceptRequired($request->user(), $request, includeSocial: true, metadata: [
                'source' => 'social_oauth',
                'provider' => $provider,
            ]);
        } else {
            $request->session()->put('social_consents_accepted', true);
        }

        return redirect()->route('auth.social.redirect', $provider);
    }

    public function redirect(Request $request, string $provider, ConsentService $consentService): RedirectResponse
    {
        $provider = $this->provider($provider);

        if ($request->user() && ! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (! $this->hasSocialConsent($request, $consentService)) {
            return redirect()->route('auth.social.consent', $provider);
        }

        if (! $this->providerIsConfigured($provider)) {
            return $this->providerErrorRedirect($provider, $this->message(
                "Não foi possível conectar ao {$this->providerLabel($provider)} agora. Configure as credenciais OAuth no ambiente.",
                "Could not connect to {$this->providerLabel($provider)} right now. Configure OAuth credentials in the environment.",
            ));
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider, ConsentService $consentService): RedirectResponse
    {
        $provider = $this->provider($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return $this->providerErrorRedirect($provider, $this->message(
                "Não foi possível entrar com {$this->providerLabel($provider)}. Tente novamente ou use e-mail e senha.",
                "Could not sign in with {$this->providerLabel($provider)}. Try again or use email and password.",
            ));
        }

        $profile = [
            'provider' => $provider,
            'provider_user_id' => (string) $socialiteUser->getId(),
            'email' => $socialiteUser->getEmail(),
            'email_verified' => $this->emailVerifiedByProvider($provider, $socialiteUser->getRaw()),
            'name' => $socialiteUser->getName(),
            'avatar_url' => $socialiteUser->getAvatar(),
            'access_token' => $socialiteUser->token ?? null,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'token_expires_at' => filled($socialiteUser->expiresIn ?? null) ? now()->addSeconds((int) $socialiteUser->expiresIn) : null,
            'raw_profile' => $socialiteUser->getRaw(),
        ];

        if (blank($profile['email']) && ! $request->user()) {
            $request->session()->put('pending_social_profile', [
                ...collect($profile)->except(['access_token', 'refresh_token', 'token_expires_at'])->all(),
                'email_verified' => false,
            ]);

            return redirect()->route('auth.social.email.create', $provider);
        }

        return $this->completeSocialLogin($request, $profile, $consentService);
    }

    public function emailCreate(Request $request, string $provider): View|RedirectResponse
    {
        $provider = $this->provider($provider);

        if ($request->session()->missing('pending_social_profile.provider')) {
            return redirect()->route('login');
        }

        return view('auth.social-email', [
            'provider' => $provider,
            'profile' => $request->session()->get('pending_social_profile'),
        ]);
    }

    public function emailStore(Request $request, string $provider, ConsentService $consentService): RedirectResponse
    {
        $provider = $this->provider($provider);
        $pending = $request->session()->get('pending_social_profile');

        abort_unless(($pending['provider'] ?? null) === $provider, 404);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        if (! $request->user() && User::where('email', $data['email'])->exists()) {
            return back()->withErrors([
                'email' => $this->message(
                    'Este e-mail já está cadastrado. Entre na conta para vincular o provedor.',
                    'This email is already registered. Sign in to link this provider.',
                ),
            ]);
        }

        $request->session()->forget('pending_social_profile');

        return $this->completeSocialLogin($request, [
            ...$pending,
            'email' => $data['email'],
            'email_verified' => false,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ], $consentService);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function completeSocialLogin(Request $request, array $profile, ConsentService $consentService): RedirectResponse
    {
        $currentUser = $request->user();
        $existingSocialAccount = SocialAccount::with('user')
            ->where('provider', $profile['provider'])
            ->where('provider_user_id', $profile['provider_user_id'])
            ->first();

        if ($existingSocialAccount && $currentUser && $existingSocialAccount->user_id !== $currentUser->id) {
            return redirect()->route('account.index')->withErrors([
                'social' => $this->message(
                    'Esta conta social já está vinculada a outro usuário.',
                    'This social account is already linked to another user.',
                ),
            ]);
        }

        if ($existingSocialAccount && ! $currentUser) {
            $this->updateSocialAccount($existingSocialAccount->user, $profile);
            $consentService->acceptRequired($existingSocialAccount->user, $request, includeSocial: true, metadata: [
                'source' => 'social_login',
                'provider' => $profile['provider'],
            ]);
            $request->session()->forget('social_consents_accepted');
            Auth::login($existingSocialAccount->user, remember: true);

            return redirect()->intended($this->afterLoginRoute($existingSocialAccount->user));
        }

        $createdUser = false;
        $user = $currentUser;

        if (! $user && filled($profile['email'])) {
            $user = User::where('email', $profile['email'])->first();
        }

        if (! $user) {
            $createdUser = true;
            $user = User::create([
                'name' => $profile['name'] ?: Str::before((string) $profile['email'], '@') ?: 'Vyrko User',
                'email' => $profile['email'],
                'email_verified_at' => $profile['email_verified'] ? now() : null,
                'password' => Hash::make(Str::random(48)),
                'password_set_at' => null,
            ]);
        }

        $this->updateSocialAccount($user, $profile);
        $consentService->acceptRequired($user, $request, includeSocial: true, metadata: [
            'source' => $createdUser ? 'social_registration' : 'social_connection',
            'provider' => $profile['provider'],
        ]);
        $request->session()->forget('social_consents_accepted');

        if (! $currentUser) {
            Auth::login($user, remember: true);
        }

        if ($currentUser) {
            return redirect()->route('account.index')->with('status', $this->message(
                "{$this->providerLabel($profile['provider'])} conectado com sucesso.",
                "{$this->providerLabel($profile['provider'])} connected successfully.",
            ));
        }

        return redirect()->route($createdUser && $user->hasVerifiedEmail() ? 'onboarding.index' : ($user->hasVerifiedEmail() ? 'dashboard' : 'verification.notice'));
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function updateSocialAccount(User $user, array $profile): SocialAccount
    {
        if ($profile['email_verified'] && filled($profile['email']) && $user->email === $profile['email'] && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return SocialAccount::updateOrCreate(
            [
                'provider' => $profile['provider'],
                'provider_user_id' => $profile['provider_user_id'],
            ],
            [
                'user_id' => $user->id,
                'email' => $profile['email'] ?? null,
                'name' => $profile['name'] ?? null,
                'avatar_url' => $profile['avatar_url'] ?? null,
                'access_token' => $profile['access_token'] ?? null,
                'refresh_token' => $profile['refresh_token'] ?? null,
                'token_expires_at' => $profile['token_expires_at'] ?? null,
                'raw_profile' => $profile['raw_profile'] ?? null,
            ],
        );
    }

    private function hasSocialConsent(Request $request, ConsentService $consentService): bool
    {
        if ($request->user()) {
            return $consentService->hasAccepted($request->user(), includeSocial: true);
        }

        return $request->session()->boolean('social_consents_accepted');
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function emailVerifiedByProvider(string $provider, array $raw): bool
    {
        return match ($provider) {
            'google' => (bool) data_get($raw, 'email_verified', false),
            'linkedin' => (bool) data_get($raw, 'email_verified', false),
            default => false,
        };
    }

    private function afterLoginRoute(User $user): string
    {
        if (! $user->hasVerifiedEmail()) {
            return route('verification.notice', absolute: false);
        }

        return route('dashboard', absolute: false);
    }

    private function provider(string $provider): string
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return $provider;
    }

    private function providerIsConfigured(string $provider): bool
    {
        return filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"))
            && filled(config("services.{$provider}.redirect"));
    }

    private function providerErrorRedirect(string $provider, string $message): RedirectResponse
    {
        return redirect()->route(Auth::check() ? 'account.index' : 'login')
            ->withErrors([$provider => $message]);
    }

    private function providerLabel(string $provider): string
    {
        return $provider === 'linkedin' ? 'LinkedIn' : 'Google';
    }

    private function message(string $pt, string $en): string
    {
        return app()->getLocale() === 'en' ? $en : $pt;
    }
}
