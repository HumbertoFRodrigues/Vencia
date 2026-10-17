<?php

namespace App\Support;

use App\Models\Configuracao;
use Illuminate\Support\Carbon;

/**
 * When each scheduled command last ran, so Admin → Visão geral can tell the
 * super-admin that a cron job is missing or failing on the server.
 */
class Tarefas
{
    /** command => what it does, for the admin overview. Run once a day by cron (see DEPLOY.md). */
    public const LISTA = [
        'verificar' => ['comando' => 'vencia:verificar-vencimentos', 'nome' => 'Lembretes de vencimento'],
        'planos' => ['comando' => 'vencia:planos', 'nome' => 'Fim dos planos e avisos'],
        'copia' => ['comando' => 'vencia:copia-seguranca', 'nome' => 'Cópia de segurança'],
    ];

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
