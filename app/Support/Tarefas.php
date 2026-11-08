<?php

namespace App\Support;

use App\Models\Configuracao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * When each scheduled command last ran, so Admin → Visão geral can tell the
 * super-admin that a cron job is missing or failing on the server.
 */
class Tarefas
{
    /**
     * command => what it does and when it is due each day. Run by cron (see
     * DEPLOY.md); if the cron is missing, correrAtrasadas() runs them after a
     * visit to the site, so reminders still go out.
     */
    public const LISTA = [
        'copia' => ['comando' => 'vencia:copia-seguranca', 'nome' => 'Cópia de segurança', 'hora' => '03:00'],
        'planos' => ['comando' => 'vencia:planos', 'nome' => 'Fim dos planos e avisos', 'hora' => '06:30'],
        'verificar' => ['comando' => 'vencia:verificar-vencimentos', 'nome' => 'Lembretes de vencimento', 'hora' => '07:00'],
    ];

    /** Tasks whose time today has passed but that haven't run since. */
    public static function emFalta(): array
    {
        $todas = Configuracao::plataforma('tarefas', []);
        $agora = Carbon::now();

        return array_keys(array_filter(self::LISTA, function (array $t, string $chave) use ($todas, $agora) {
            $hoje = $agora->copy()->setTimeFromTimeString($t['hora']);
            $ultima = isset($todas[$chave]['em']) ? Carbon::parse($todas[$chave]['em']) : null;

            return $agora->gte($hoje) && (! $ultima || $ultima->lt($hoje));
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * The fallback for hosting without the cron jobs: runs what is due, one
     * process at a time, at most a check every 10 minutes. Called after the
     * response has gone to the browser (CorrerTarefasEmFalta), so nobody waits.
     */
    public static function correrAtrasadas(): array
    {
        if (! Cache::add('tarefas:verificadas', true, 600)) {
            return [];
        }

        $emFalta = self::emFalta();
        if ($emFalta === []) {
            return [];
        }

        $lock = Cache::lock('tarefas:a-correr', 900);
        if (! $lock->get()) {
            return [];
        }

        try {
            @set_time_limit(600);
            foreach ($emFalta as $chave) {
                try {
                    Artisan::call(self::LISTA[$chave]['comando']);
                } catch (Throwable $e) {
                    Log::error('Tarefa automática '.$chave.' falhou: '.$e->getMessage());
                }
            }
        } finally {
            $lock->release();
        }

        return $emFalta;
    }

    public static function registar(string $tarefa, string $resumo = ''): void
    {
        $todas = Configuracao::plataforma('tarefas', []);
        $todas[$tarefa] = ['em' => Carbon::now()->toIso8601String(), 'resumo' => mb_strimwidth($resumo, 0, 200, '…')];
        Configuracao::guardarPlataforma('tarefas', $todas);
    }

    /**
     * @return array<string, array{nome: string, comando: string, em: ?Carbon, resumo: ?string, atrasada: bool}>
     */
    public static function estado(): array
    {
        $todas = Configuracao::plataforma('tarefas', []);

        return collect(self::LISTA)->map(function (array $t, string $chave) use ($todas) {
            $em = isset($todas[$chave]['em']) ? Carbon::parse($todas[$chave]['em']) : null;

            return $t + [
                'em' => $em,
                'resumo' => $todas[$chave]['resumo'] ?? null,
                // Daily jobs: more than ~a day and a bit without running means the cron isn't there.
                'atrasada' => ! $em || $em->lt(Carbon::now()->subHours(26)),
            ];
        })->all();
    }
}
