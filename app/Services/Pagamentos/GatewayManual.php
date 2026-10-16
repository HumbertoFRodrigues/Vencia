<?php

namespace App\Services\Pagamentos;

use App\Contracts\GatewayPagamento;
use App\Models\Configuracao;
use App\Models\Fatura;

/** Pay by M-Pesa, e-Mola or bank transfer to the details set in /admin → Plataforma, then send the reference. */
class GatewayManual implements GatewayPagamento
{
    public function nome(): string
    {
        return 'manual';
    }

    public function manual(): bool
    {
        return true;
    }

    public function instrucoes(Fatura $fatura): array
    {
        $dados = Configuracao::plataforma('faturacao', []);
        $referencia = $fatura->referenciaInterna();
        $instrucoes = [];

        if (filled($dados['mpesa'] ?? null)) {
            $instrucoes[] = ['metodo' => 'mpesa', 'titulo' => 'M-Pesa', 'detalhe' => 'Enviar para '.$dados['mpesa'].(filled($dados['titular'] ?? null) ? ' ('.$dados['titular'].')' : '')];
        }
        if (filled($dados['emola'] ?? null)) {
            $instrucoes[] = ['metodo' => 'emola', 'titulo' => 'e-Mola', 'detalhe' => 'Enviar para '.$dados['emola'].(filled($dados['titular'] ?? null) ? ' ('.$dados['titular'].')' : '')];
        }
        if (filled($dados['banco'] ?? null)) {
            $instrucoes[] = ['metodo' => 'transferencia', 'titulo' => 'Transferência bancária', 'detalhe' => $dados['banco']];
        }
        if ($instrucoes === []) {
            $instrucoes[] = ['metodo' => 'outro', 'titulo' => 'Pagamento', 'detalhe' => 'Os dados de pagamento ainda não foram publicados. Contacta o suporte da Vencia.'];
        }

        foreach ($instrucoes as &$i) {
            $i['detalhe'] .= ' · Referência: '.$referencia;
        }

        return $instrucoes;
    }
}
