<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * "Esqueceu-se da password?" — sends a reset link to the admin's email via
 * Laravel's built-in password broker. Plain controller, matching
 * LoginController's style (this app has no registration and only one user,
 * so there is nothing multi-user to design around here).
 */
class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar o email de recuperação de password: '.$e->getMessage());

            return back()
                ->withErrors(['email' => 'Não foi possível enviar o email agora. Tente de novo dentro de alguns minutos.'])
                ->onlyInput('email');
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => trans($status)])->onlyInput('email');
        }

        // Same answer whether or not the email exists, so the form can't be used to discover accounts.
        return back()->with('status', 'Se este email estiver registado, enviámos um link para redefinir a password.');
    }
}
