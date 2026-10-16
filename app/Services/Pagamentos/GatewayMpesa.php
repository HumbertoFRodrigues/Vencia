<?php

namespace App\Services\Pagamentos;

use App\Contracts\CobrancaImediata;
use App\Contracts\GatewayPagamento;
use App\Models\Fatura;
use App\Services\Faturacao;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * "Pagar já com M-Pesa": charges the plan invoice with a PIN prompt on the
 * customer's phone and turns the plan on by itself. The manual instructions
 * stay available underneath, for e-Mola, transfers or when M-Pesa fails.
 */
class GatewayMpesa implements CobrancaImediata, GatewayPagamento
{
    public function __construct(private MpesaCliente $cliente, private GatewayManual $manual) {}

    public function nome(): string
    {
        return 'mpesa';
    }

    public function manual(): bool
    {
        return false;
    }

    public function instrucoes(Fatura $fatura): array
    {
        return $this->manual->instrucoes($fatura);
    }

    public function cobrar(Fatura $fatura, string $telefone): array
    {
        $numero = MpesaCliente::normalizarTelefone($telefone);
        if (! $numero) {
            return ['ok' => false, 'mensagem' => 'Escreve um número M-Pesa (84 ou 85), por exemplo 84 123 4567.', 'referencia' => null];
        }

        if (! $fatura->aberta()) {
            return ['ok' => false, 'mensagem' => 'Esta fatura já não está por pagar.', 'referencia' => null];
        }

        // The customer has up to a couple of minutes to type the PIN.
        @set_time_limit((int) config('pagamentos.mpesa.timeout', 150) + 30);

        $referencia = $fatura->referenciaInterna();
        $terceiro = 'V'.$fatura->id.'T'.now()->format('His');

        try {
            $r = $this->cliente->c2b($numero, (float) $fatura->valor, str_replace('-', '', $referencia), $terceiro);
        } catch (Throwable $e) {
            Log::error('M-Pesa C2B falhou para '.$referencia.': '.$e->getMessage());

            return ['ok' => false, 'mensagem' => MpesaCliente::mensagemErro(null), 'referencia' => null];
        }

        if ($r['codigo'] !== 'INS-0' || ! $r['transaccao']) {
            Log::warning('M-Pesa recusou '.$referencia.': '.($r['codigo'] ?? 'sem código').' '.($r['descricao'] ?? ''));

            return ['ok' => false, 'mensagem' => MpesaCliente::mensagemErro($r['codigo']), 'referencia' => null];
        }

        $fatura->update(['metodo' => 'mpesa', 'referencia' => $r['transaccao'], 'gateway' => 'mpesa', 'enviada_em' => now()]);
        app(Faturacao::class)->marcarPaga($fatura->fresh(), null, $r['transaccao']);

        return ['ok' => true, 'mensagem' => 'Pagamento confirmado. Obrigado!', 'referencia' => $r['transaccao']];
    }
}
