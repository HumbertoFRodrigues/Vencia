<?php

namespace App\Livewire;

use App\Enums\ServicoStatus;
use App\Models\Servico;
use App\Services\Totais;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * @return array{
     *     entradas: float, nPagamentos: int,
     *     aReceber: float, nAReceber: int,
     *     emAtraso: float, nEmAtraso: int,
     *     aVencer: int,
     *     clientesActivos: int, servicosActivos: int,
     *     mrr: float, arr: float, esperada: float,
     * }
     */
    #[Computed]
    public function totais(): array
    {
        return app(Totais::class)->calcular();
    }

    /**
     * Próximos vencimentos — every non-suspended, non-cancelled service,
     * nearest due date first.
     *
     * @return Collection<int, Servico>
     */
    #[Computed]
    public function proximosVencimentos(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereNotIn('status', [ServicoStatus::Cancelado, ServicoStatus::Suspenso])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    /**
     * Alerts: services that are overdue or due soon.
     *
     * @return Collection<int, Servico>
     */
    #[Computed]
    public function alertas(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
