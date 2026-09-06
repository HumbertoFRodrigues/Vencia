<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // The `throttle:login` route middleware (routes/web.php) is a coarse
        // per-IP backstop behind the precise per-email+IP throttle already
        // handled inside LoginController/ForgotPasswordController/
        // ResetPasswordController. It should essentially never fire in
        // practice, but if it does, redirect back with the same friendly
        // pt-PT message instead of letting a raw 429 exception page render.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $seconds = (int) ($e->getHeaders()['Retry-After'] ?? 60);

            return back()
                ->withErrors(['email' => trans('auth.throttle', ['seconds' => $seconds])])
                ->onlyInput('email');
        });
    })->create();
