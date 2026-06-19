<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountConnectionsController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.connections', [
            'user' => $request->user()->load('socialAccounts'),
        ]);
    }

    public function disconnect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'linkedin'], true), 404);

        $user = $request->user();
        $account = $user->socialAccounts()->where('provider', $provider)->firstOrFail();

        if (! $user->canDisconnectSocialAccount($account)) {
            return back()->withErrors([
                'social' => app()->getLocale() === 'en'
                    ? 'Add another sign-in method before disconnecting this account.'
                    : 'Adicione outro método de entrada antes de desconectar esta conta.',
            ]);
        }

        $account->delete();

        return back()->with('status', app()->getLocale() === 'en'
            ? 'Social account disconnected.'
            : 'Conta social desconectada.');
    }
}
