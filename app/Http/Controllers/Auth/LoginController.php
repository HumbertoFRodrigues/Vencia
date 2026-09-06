<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Single-admin login, no registration. Plain controller (not Livewire) per
 * Fase 2 of the plan — simpler than wiring auth through a Livewire round-trip.
 *
 * Login attempts are throttled per email+IP combination (5/minute) using the
 * RateLimiter facade directly — the same approach Laravel's own starter kits
 * (Breeze/Fortify) use, since the old Illuminate\Foundation\Auth\ThrottlesLogins
 * trait no longer ships with the framework. The `throttle:login` middleware
 * on the route (see routes/web.php + AppServiceProvider) adds a coarser
 * per-IP backstop against raw request flooding.
 */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($this->throttleKey($request));

            return back()
                ->withErrors(['email' => trans('auth.failed')])
                ->onlyInput('email');
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey($request)),
            ]),
        ]);
    }

    /** Keyed by email+IP, not IP alone, so one blocked email never locks out the rest of the network. */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
