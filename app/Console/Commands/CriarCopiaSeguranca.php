<?php

namespace App\Console\Commands;

use App\Services\CopiaSeguranca;
use App\Support\Tarefas;
use Illuminate\Console\Command;

class CriarCopiaSeguranca extends Command
{
    protected $signature = 'vencia:copia-seguranca {--sem-email : Não enviar a cópia por email}';

    protected $description = 'Cria uma cópia de segurança da base de dados (guarda as últimas 14) e envia-a por email à empresa';

    public function handle(CopiaSeguranca $copias): int
    {
        $ficheiro = $copias->criar();
        $this->info('Cópia criada: '.basename($ficheiro).' ('.number_format(filesize($ficheiro) / 1024, 0, ',', '.').' KB)');

        if (! $this->option('sem-email')) {
            $this->line($copias->enviarPorEmail($ficheiro)
                ? 'Enviada por email para a empresa.'
                : 'Não enviada por email (SMTP ou email da empresa por configurar, ou ficheiro grande demais).');
        }

        Tarefas::registar('copia', basename($ficheiro).' ('.number_format(filesize($ficheiro) / 1024, 0, ',', '.').' KB)');

        return self::SUCCESS;
    }
}
