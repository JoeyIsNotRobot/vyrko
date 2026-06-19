<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailChangeController extends Controller
{
    public function __invoke(Request $request, User $user, string $token): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($user->pending_email && hash_equals((string) $user->pending_email_token, $token), 404);

        $user->forceFill([
            'email' => $user->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
            'pending_email_token' => null,
            'pending_email_requested_at' => null,
        ])->save();

        if (! Auth::check()) {
            Auth::login($user);
        }

        return redirect()->route('account.index')->with('status', app()->getLocale() === 'en'
            ? 'Email updated and verified.'
            : 'E-mail atualizado e verificado.');
    }
}
