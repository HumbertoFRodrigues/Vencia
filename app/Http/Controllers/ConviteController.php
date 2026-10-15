<?php

namespace App\Http\Controllers;

use App\Models\Convite;
use App\Services\Equipa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The link in an invite email: shows who invited you, then joins you to the conta. */
class ConviteController extends Controller
{
    public function mostrar(Request $request, string $token): View
    {
        $convite = Convite::porToken($token);

        if ($convite && $convite->valido() && ! $request->user()) {
            // After logging in (or the password reset that may come first), come back here.
            $request->session()->put('url.intended', route('convite.mostrar', $token));
        }

        return view('conta.convite', [
            'convite' => $convite?->valido() ? $convite->load('conta', 'convidadoPor') : null,
            'token' => $token,
            'user' => $request->user(),
        ]);
    }

    public function aceitar(Request $request, string $token): RedirectResponse
    {
        $convite = Convite::porToken($token);

        if (! $convite || ! $convite->valido()) {
            return redirect()->route('convite.mostrar', $token);
        }

        // Only the invited address can accept: a forwarded link is useless to anyone else.
        if (strcasecmp($convite->email, $request->user()->email) !== 0) {
            return redirect()->route('convite.mostrar', $token)
                ->withErrors(['email' => 'Este convite foi enviado para '.$convite->email.'. Entra com esse email para o aceitar.']);
        }

        app(Equipa::class)->aceitar($convite, $request->user());

        return redirect()->route('dashboard')->with('toast', ['title' => 'Bem-vindo à equipa', 'body' => $convite->conta->nome]);
    }
}
