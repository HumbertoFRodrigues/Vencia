<?php

namespace App\Console\Commands;

use App\Services\VerificacaoDiariaService;
use Illuminate\Console\Command;

/**
 * Entry point for the Windows Task Scheduler entry (once a day, 07:00 —
 * see OPERACOES.md). Thin wrapper: all the logic lives in
 * VerificacaoDiariaService so it can also be exercised from tinker/tests.
 */
class VerificarVencimentos extends Command
{
    protected $signature = 'vencia:verificar-vencimentos';

    protected $description = 'Recalcula estados de serviços e envia os lembretes de vencimento devidos hoje.';

    public function handle(VerificacaoDiariaService $service): int
    {
        $resumo = $service->executar();

        if (! $resumo['executado']) {
            $this->warn($resumo['nota'] ?? 'Verificação diária desligada.');

            return Command::SUCCESS;
        }

        $this->info(sprintf(
            'Serviços avaliados: %d | Estados alterados: %d | Lembretes enviados: %d | Lembretes falhados: %d',
            $resumo['servicos_avaliados'],
            $resumo['estados_alterados'],
            $resumo['lembretes_enviados'],
            $resumo['lembretes_falhados'],
        ));

        return Command::SUCCESS;
    }
}
