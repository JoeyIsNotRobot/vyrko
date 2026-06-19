<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
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

    public function redirect(string $provider): RedirectResponse
    {
        $provider = $this->provider($provider);

        if (! $this->providerIsConfigured($provider)) {
            return $this->providerErrorRedirect($provider, $this->message(
                "Não foi possível conectar ao {$this->providerLabel($provider)} agora. Configure as credenciais OAuth no ambiente.",
                "Could not connect to {$this->providerLabel($provider)} right now. Configure OAuth credentials in the environment.",
            ));
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
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
            'email_verified' => (bool) data_get($socialiteUser->getRaw(), 'email_verified', true),
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

        return $this->completeSocialLogin($request, $profile);
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

    public function emailStore(Request $request, string $provider): RedirectResponse
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
        ]);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function completeSocialLogin(Request $request, array $profile): RedirectResponse
    {
        $currentUser = $request->user();
        $existingSocialAccount = SocialAccount::with('user')
            ->where('provider', $profile['provider'])
            ->where('provider_user_id', $profile['provider_user_id'])
            ->first();

        if ($existingSocialAccount && $currentUser && $existingSocialAccount->user_id !== $currentUser->id) {
            return redirect()->route('account.connections')->withErrors([
                'social' => $this->message(
                    'Esta conta social já está vinculada a outro usuário.',
                    'This social account is already linked to another user.',
                ),
            ]);
        }

        if ($existingSocialAccount && ! $currentUser) {
            $this->updateSocialAccount($existingSocialAccount->user, $profile);
            Auth::login($existingSocialAccount->user, remember: true);

            return redirect()->intended(route('dashboard', absolute: false));
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

        if (! $currentUser) {
            Auth::login($user, remember: true);
        }

        if ($currentUser) {
            return redirect()->route('account.connections')->with('status', $this->message(
                "{$this->providerLabel($profile['provider'])} conectado com sucesso.",
                "{$this->providerLabel($profile['provider'])} connected successfully.",
            ));
        }

        return redirect()->route($createdUser ? 'onboarding.index' : 'dashboard');
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function updateSocialAccount(User $user, array $profile): SocialAccount
    {
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
        return redirect()->route(Auth::check() ? 'account.connections' : 'login')
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
