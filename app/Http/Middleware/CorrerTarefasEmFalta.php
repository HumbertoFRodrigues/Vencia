<?php

namespace App\Http\Middleware;

use App\Support\Tarefas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safety net for hosting where the daily cron jobs were never set up: after
 * a page has been sent to the browser, runs the daily tasks that are due
 * (reminders, plan endings, backup). With the cron in place it finds nothing
 * to do. See App\Support\Tarefas::correrAtrasadas().
 */
class CorrerTarefasEmFalta
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /** Runs after the response is sent (PHP-FPM closes the connection first), so the visitor never waits. */
    public function terminate(Request $request, Response $response): void
    {
        if (app()->runningUnitTests() || ! config('app.tarefas_sem_cron')) {
            return;
        }

        Tarefas::correrAtrasadas();
    }
}
