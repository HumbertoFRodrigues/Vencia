<?php

namespace App\Http\Middleware;

use App\Support\ContaActual;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the logged-in user's conta in ContaActual for the rest of the request,
 * and keeps out anyone who shouldn't be in: blocked users are logged out, a
 * suspended conta sees only its notice, and a user without any conta (their
 * last one was deleted) is sent to create one.
 */
class DefinirConta
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->bloqueado()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Esta conta de utilizador foi bloqueada. Contacta o suporte.']);
        }

        $conta = $user->contaActual();

        if ($conta && $user->conta_actual_id !== $conta->id) {
            $user->forceFill(['conta_actual_id' => $conta->id])->saveQuietly();
        }

        ContaActual::definir($conta);

        // Pages that work without a (usable) conta: creating or choosing one, the admin, plan receipts.
        if ($request->routeIs('conta.criar', 'conta.guardar', 'conta.escolher', 'conta.mudar', 'admin.*', 'faturas.*')) {
            return $next($request);
        }

        if (! $conta) {
            return redirect()->route('conta.criar');
        }

        if ($conta->suspensa() && ! $user->super_admin) {
            return response()->view('conta.suspensa', ['conta' => $conta], 403);
        }

        return $next($request);
    }
}
