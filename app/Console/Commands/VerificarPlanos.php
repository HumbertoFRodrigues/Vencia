<?php

namespace App\Console\Commands;

use App\Services\Faturacao;
use Illuminate\Console\Command;

/** Daily (cron, see DEPLOY.md): warns before a paid plan ends and moves ended plans back to Grátis. */
class VerificarPlanos extends Command
{
    protected $signature = 'vencia:planos';

    protected $description = 'Avisa os donos antes do fim do plano e passa para Grátis as contas cujo plano terminou.';

    public function handle(Faturacao $faturacao): int
    {
        $resumo = $faturacao->verificarPlanos();

        $this->info(sprintf('Avisos enviados: %d | Planos terminados: %d', $resumo['avisos'], $resumo['expiradas']));

        return self::SUCCESS;
    }
}
