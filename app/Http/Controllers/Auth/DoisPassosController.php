<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/** Second login step: the 6-digit code from the authenticator app, or a recovery code. */
class DoisPassosController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        return $this->pendente($request) ? view('auth.dois-passos') : redirect()->route('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->pendente($request);
        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'O tempo para o segundo passo acabou. Entra de novo.']);
        }

        $chave = 'dois-passos:'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($chave, 5)) {
            return back()->withErrors(['codigo' => 'Demasiadas tentativas. Espera '.RateLimiter::availableIn($chave).' segundos.']);
        }

        $request->validate(['codigo' => ['required', 'string', 'max:20']], ['codigo.required' => 'Escreve o código da app autenticadora.']);

        if (! $user->validarSegundoPasso((string) $request->input('codigo'))) {
            RateLimiter::hit($chave);

            return back()->withErrors(['codigo' => 'Código inválido ou já usado. Confirma a hora do telemóvel e tenta o código seguinte.']);
        }

        RateLimiter::clear($chave);
        $request->session()->forget('dois_passos');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function pendente(Request $request): ?User
    {
        $pendente = $request->session()->get('dois_passos');

        if (! $pendente || $pendente['expira'] < now()->timestamp) {
            $request->session()->forget('dois_passos');

            return null;
        }

        return User::find($pendente['id']);
    }
}
