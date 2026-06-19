<?php

namespace App\Http\Controllers;

use App\Notifications\PendingEmailChangeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountConnectionsController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.index', [
            'user' => $request->user()->load(['socialAccounts', 'userConsents']),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($data);

        return back()->with('status', $this->message('Perfil atualizado.', 'Profile updated.'));
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => [$user->hasPasswordLogin() ? 'required' : 'nullable', 'string'],
        ]);

        if ($user->hasPasswordLogin() && ! Hash::check((string) $data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => $this->message('Senha atual inválida.', 'Current password is invalid.'),
            ]);
        }

        if ($data['email'] === $user->email) {
            return back()->with('status', $this->message('Este e-mail já está em uso na sua conta.', 'This email is already used by your account.'));
        }

        $token = Str::random(48);
        $user->forceFill([
            'pending_email' => $data['email'],
            'pending_email_token' => $token,
            'pending_email_requested_at' => now(),
        ])->save();

        Notification::route('mail', $data['email'])->notify(new PendingEmailChangeNotification($user, $token));

        return back()->with('status', $this->message(
            'Enviamos uma confirmação para o novo e-mail. A troca só será concluída após o clique no link.',
            'We sent a confirmation to the new email. The change is completed only after clicking the link.',
        ));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'current_password' => [$user->hasPasswordLogin() ? 'required' : 'nullable', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($user->hasPasswordLogin() && ! Hash::check((string) $data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => $this->message('Senha atual inválida.', 'Current password is invalid.'),
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'password_set_at' => now(),
        ])->save();

        return back()->with('status', $this->message('Senha atualizada.', 'Password updated.'));
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', $this->message('Seu e-mail já está verificado.', 'Your email is already verified.'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', $this->message('Enviamos um novo link de confirmação.', 'We sent a new verification link.'));
    }

    public function disconnect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'linkedin'], true), 404);

        $user = $request->user();
        $account = $user->socialAccounts()->where('provider', $provider)->firstOrFail();

        if (! $user->canDisconnectSocialAccount($account)) {
            return back()->withErrors([
                'social' => $this->message(
                    'Crie uma senha ou conecte outro método antes de desconectar esta conta.',
                    'Create a password or connect another method before disconnecting this account.',
                ),
            ]);
        }

        $account->delete();

        return back()->with('status', $this->message('Conta social desconectada.', 'Social account disconnected.'));
    }

    private function message(string $pt, string $en): string
    {
        return app()->getLocale() === 'en' ? $en : $pt;
    }
}
