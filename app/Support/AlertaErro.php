<?php

namespace App\Support;

use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails the platform's super-admin when an unexpected error happens in production, so a
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

            $destino = User::query()->where('super_admin', true)->orderBy('id')->value('email') ?? config('app.admin_email');
            if (! $destino) {
                return;
            }

            [$assunto, $corpo] = self::mensagem($e);

            Configuracao::aplicarSmtpDaPlataforma();
            Mail::raw($corpo, fn ($m) => $m->to($destino)->subject($assunto));
        } catch (Throwable) {
            // Reporting must never cause a second failure.
        } finally {
            self::$aEnviar = false;
        }
    }

    /** @return array{0: string, 1: string} subject and plain-text body of the alert */
    public static function mensagem(Throwable $e): array
    {
        $pedido = app()->runningInConsole() ? 'Linha de comandos: '.implode(' ', $_SERVER['argv'] ?? []) : request()->method().' '.request()->fullUrl();
        $relativo = fn (?string $caminho) => ltrim(str_replace([base_path(), DIRECTORY_SEPARATOR], ['', '/'], (string) $caminho), '/');

        // Only Vencia's own code (app/), a few lines: enough to find the spot, short enough to read on a phone.
        $passos = collect($e->getTrace())
            ->filter(fn (array $p) => isset($p['file']) && str_starts_with($relativo($p['file']), 'app/'))
            ->take(5)
            ->map(fn (array $p) => '  · '.$relativo($p['file']).':'.$p['line'].(isset($p['function']) ? ' ('.class_basename($p['class'] ?? '').($p['class'] ?? false ? '::' : '').$p['function'].')' : ''))
            ->implode("\n");

        return [
            '⚠ Erro na Vencia: '.mb_strimwidth($e->getMessage() ?: $e::class, 0, 80, '…'),
            "Aconteceu um erro na Vencia.\n\n"
            .'O quê:      '.($e->getMessage() ?: class_basename($e))."\n"
            .'Quando:     '.Carbon::now()->format('d/m/Y H:i:s')."\n"
            .'Quem:       '.(auth()->user()?->email ?? '—').(ContaActual::get() ? ' (conta '.ContaActual::get()->nome.')' : '')."\n"
            .'Onde:       '.$pedido."\n\n"
            ."Para quem vai corrigir:\n"
            .'  '.class_basename($e).' em '.$relativo($e->getFile()).':'.$e->getLine()."\n"
            .($passos !== '' ? $passos."\n" : '')
            ."\nO mesmo erro não volta a ser enviado durante 1 hora. O detalhe completo está em storage/logs/laravel.log"
            .' e o último erro aparece em Administração → Visão geral → Estado do sistema.',
        ];
    }
}
