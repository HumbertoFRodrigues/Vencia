<?php

namespace App\Enums;

enum SuspensaoMotivo: string
{
    case PagamentoNaoRenovado = 'pagamento_nao_renovado';
    case PedidoCliente = 'pedido_cliente';
    case ServicoSubstituido = 'servico_substituido';
    case Outro = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::PagamentoNaoRenovado => 'Pagamento não renovado',
            self::PedidoCliente => 'Pedido do cliente',
            self::ServicoSubstituido => 'Serviço substituído',
            self::Outro => 'Outro',
        };
    }
}
