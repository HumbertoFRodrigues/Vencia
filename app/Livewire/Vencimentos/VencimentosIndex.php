<?php

namespace App\Livewire\Vencimentos;

use App\Enums\ServicoStatus;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Lista/Calendário toggle over every non-cancelled serviço's due date.
 * "Lista" reuses the same status-tabs + sorted-by-dias table pattern as
 * ServicosIndex/Dashboard; "Calendário" delegates entirely to the nested
 * CalendarioMes component, which owns its own month-navigation state so
 * switching Lista/Calendário here never resets what month it was showing.
 *
 * "Cancelado" is excluded from every tab (including "Todos") because a
 * cancelled serviço has nothing left to track a due date for — same
 * "non-terminal" scoping the brief asks for. Suspenso stays in (its own
 * tab exists, matching DueDatesScreen.jsx) since a suspended serviço still
 * has a vencimento worth seeing at a glance even though its reminders
 * stopped.
 */
#[Layout('layouts.app')]
#[Title('Vencimentos')]
class VencimentosIndex extends Component
{
    #[Url]
    public string $view = 'lista';

    #[Url]
    public string $tab = 'todos';

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    /** @return Collection<int, Servico> */
    #[Computed]
    public function servicos(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->where('status', '!=', ServicoStatus::Cancelado)
            ->when($this->tab !== 'todos', fn ($query) => $query->where('status', $this->tab))
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->get();
    }

    /** @return array{todos: int, activo: int, a_vencer: int, vencido: int, suspenso: int} */
    #[Computed]
    public function counts(): array
    {
        $base = Servico::query()->where('status', '!=', ServicoStatus::Cancelado);

        return [
            'todos' => (clone $base)->count(),
            'activo' => (clone $base)->where('status', ServicoStatus::Activo)->count(),
            'a_vencer' => (clone $base)->where('status', ServicoStatus::AVencer)->count(),
            'vencido' => (clone $base)->where('status', ServicoStatus::Vencido)->count(),
            'suspenso' => (clone $base)->where('status', ServicoStatus::Suspenso)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.vencimentos.vencimentos-index');
    }
}
