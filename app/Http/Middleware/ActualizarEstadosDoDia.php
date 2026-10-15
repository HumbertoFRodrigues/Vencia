<?php

namespace App\Http\Middleware;

use App\Services\ServicoStatusService;
use App\Support\ContaActual;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Statuses (activo → a vencer → vencido) depend only on the date, so they are
 * brought up to date once a day on the first page load — every total, badge
 * and list is right even on a day the scheduled job didn't run (server
 * cron missing, PC switched off). The daily job does the same on its own.
 */
class ActualizarEstadosDoDia
{
    public function handle(Request $request, Closure $next): Response
    {
        $conta = ContaActual::id();
        $chave = 'estados-servicos:'.$conta.':'.Carbon::today()->toDateString();

        if ($conta && Cache::add($chave, true, Carbon::tomorrow())) {
            try {
                app(ServicoStatusService::class)->sincronizarTodos();
            } catch (Throwable $e) {
                Cache::forget($chave);
                Log::error('Falha ao actualizar os estados dos serviços: '.$e->getMessage());
            }
        }

        return $next($request);
    }
}
