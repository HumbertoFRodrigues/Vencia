<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withErrors(['email' => trans($status)])
                ->onlyInput('email');
        }

        return back()->with('status', trans($status));
    }
}
