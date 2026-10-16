<?php

namespace App\Livewire\Clientes;

use App\Enums\ServicoStatus;
use App\Models\Cliente;
use App\Models\Historico;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Support\ContaActual;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ClienteDetail extends Component
{
    public Cliente $cliente;

    /**
     * Livewire reloads the model property without the conta scope: if the user
     * switched business in another tab, this page must not keep acting on the old one.
     */
    public function hydrate(): void
    {
        abort_unless($this->cliente->conta_id === ContaActual::id(), 404);
    }

    public function mount(Cliente $cliente): void
    {
        $this->cliente = $cliente;
    }

    #[Computed]
    public function servicos(): Collection
    {
        return $this->cliente->servicos()->orderBy('nome')->get();
    }

    #[Computed]
    public function historicos(): Collection
    {
        return Historico::query()
            ->where('cliente_id', $this->cliente->id)
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();
    }

    /**
     * Financial summary rail: total paid/pending, this client's MRR/ARR
     * (same normalisation as Totais::calcular(), scoped to this client's
     * non-suspenso/cancelado serviços), latest payment, soonest due date.
     *
     * @return array{
     *     totalPago: float, totalPendente: float, mrr: float, arr: float,
     *     ultimoPagamento: ?Pagamento, proximoServico: ?Servico,
     * }
     */
    #[Computed]
    public function resumoFinanceiro(): array
    {
        $pagamentos = $this->cliente->pagamentos;

        $servicosPendentes = $this->cliente->servicos->filter(
            fn (Servico $s) => in_array($s->status, [ServicoStatus::Vencido, ServicoStatus::AVencer], true)
        );

        $servicosActivos = $this->cliente->servicos->reject(
            fn (Servico $s) => in_array($s->status, [ServicoStatus::Cancelado, ServicoStatus::Suspenso], true)
        );

        $mrr = round($servicosActivos->sum(fn (Servico $s) => $s->valorMensal()));

        $proximoServico = $servicosActivos
            ->filter(fn (Servico $s) => $s->vencimento !== null)
            ->sortBy(fn (Servico $s) => $s->vencimento->timestamp)
            ->first();

        return [
            'totalPago' => (float) $pagamentos->sum(fn (Pagamento $p) => (float) $p->valor),
            'totalPendente' => (float) $servicosPendentes->sum(fn (Servico $s) => (float) $s->valor),
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'ultimoPagamento' => $pagamentos->sortByDesc(fn (Pagamento $p) => $p->data->timestamp)->first(),
            'proximoServico' => $proximoServico,
        ];
    }

    #[On('cliente-guardado')]
    public function clienteGuardado(int $clienteId): void
    {
        if ($clienteId === $this->cliente->id) {
            $this->cliente->refresh();
        }
    }

    public function render()
    {
        // Title depends on the client's name, so it's set fluently here
        // rather than via the static #[Title('...')] attribute.
        return view('livewire.clientes.cliente-detail')
            ->layout('layouts.app')
            ->title($this->cliente->nome.' · Vencia');
    }
}
