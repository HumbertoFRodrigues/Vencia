<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/** "Confirm your email" notice, the signed link it points to, and sending it again. */
class VerificacaoEmailController extends Controller
{
    public function aviso(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('auth.verificar-email');
    }

    public function verificar(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('dashboard')->with('toast', ['title' => 'Email confirmado', 'body' => 'A tua conta está activa.']);
    }

    public function reenviar(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            Log::error('Falha ao reenviar o email de confirmação: '.$e->getMessage());

            return back()->withErrors(['email' => 'Não foi possível enviar o email agora. Tenta daqui a pouco.']);
        }

        return back()->with('status', 'Link enviado outra vez.');
    }
}
