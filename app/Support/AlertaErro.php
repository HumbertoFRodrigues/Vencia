<?php

namespace App\Support;

use App\Models\Configuracao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails the empresa when an unexpected error happens in production, so a
 * problem is noticed without reading laravel.log. The same error (class +
 * file + line) is reported at most once an hour. Never throws: if the
 * database or the mail server is the thing that is down, it just gives up.
 */
class AlertaErro
{
    private static bool $aEnviar = false;

    public static function notificar(Throwable $e): void
    {
        if (! config('app.alertas_erro') || self::$aEnviar) {
            return;
        }

        self::$aEnviar = true;

        try {
            $chave = 'alerta-erro:'.md5($e::class.$e->getFile().$e->getLine());
            if (! Cache::add($chave, true, Carbon::now()->addHour())) {
                return;
            }

            $destino = Configuracao::obter('empresa', [])['email'] ?? config('app.admin_email');
            if (! $destino) {
                return;
            }

            $pedido = app()->runningInConsole() ? 'Linha de comandos: '.implode(' ', $_SERVER['argv'] ?? []) : request()->method().' '.request()->fullUrl();
            $trace = implode("\n", array_slice(explode("\n", $e->getTraceAsString()), 0, 15));

            Configuracao::aplicarSmtpEmTempoDeExecucao();
            Mail::raw(
                'Ocorreu um erro na Vencia ('.config('app.url').").\n\n"
                .'Quando: '.Carbon::now()->format('d/m/Y H:i:s')."\n"
                ."Pedido: {$pedido}\n"
                .'Utilizador: '.(auth()->user()?->email ?? '—')."\n\n"
                .'Erro: '.$e::class."\n".$e->getMessage()."\n"
                .'Em: '.$e->getFile().':'.$e->getLine()."\n\n"
                ."{$trace}\n\n"
                .'O mesmo erro não volta a ser enviado durante 1 hora. Detalhe completo em storage/logs/laravel.log.',
                fn ($m) => $m->to($destino)->subject('⚠ Erro na Vencia: '.mb_strimwidth($e->getMessage() ?: $e::class, 0, 80, '…')),
            );
        } catch (Throwable) {
            // Reporting must never cause a second failure.
        } finally {
            self::$aEnviar = false;
        }
    }
}
