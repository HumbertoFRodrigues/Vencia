<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out after config('session.inactividade') minutes without a request
 * (15 by default), even if the browser was closed or the tab left open. The
 * page shows a countdown before that (layouts/app.blade.php) and pings
 * /sessao/manter while the person is reading without clicking.
 */
class TerminarSessaoInactiva
{
    public const CHAVE = 'ultima_actividade';

    public function handle(Request $request, Closure $next): Response
    {
        $minutos = (int) config('session.inactividade', 15);

        if ($minutos > 0 && Auth::check()) {
            $ultima = (int) $request->session()->get(self::CHAVE, 0);

            if ($ultima > 0 && time() - $ultima > $minutos * 60) {
                return $this->terminar($request, $minutos);
            }

            $request->session()->put(self::CHAVE, time());
        }

        return $next($request);
    }

    private function terminar(Request $request, int $minutos): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $mensagem = 'A tua sessão terminou depois de '.$minutos.' minutos sem actividade. Entra de novo para continuar.';
        $request->session()->flash('aviso', $mensagem);

        // Livewire and fetch calls can't follow a redirect: the page script sends the person to the login.
        if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
            return response()->json(['message' => $mensagem, 'sessao' => 'terminada', 'login' => route('login')], 401);
        }

        return redirect()->guest(route('login'));
    }
}
