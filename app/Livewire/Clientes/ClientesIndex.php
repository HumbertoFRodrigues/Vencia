<?php

namespace App\Livewire\Clientes;

use App\Enums\ClienteStatus;
use App\Enums\Periodicidade;
use App\Enums\ServicoStatus;
use App\Models\Cliente;
use App\Models\Servico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Clientes')]
class ClientesIndex extends Component
{
    use WithPagination;

    /** Dense table rows — 20 keeps the page short while still beating a "load everything" query at any realistic scale. */
    private const POR_PAGINA = 20;

    #[Url]
    public string $tab = 'todos';

    #[Url]
    public string $q = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    /** Search box drives the query directly (wire:model.live.debounce) — every keystroke that changes it must land back on page 1. */
    public function updatedQ(): void
    {
        $this->resetPage();
    }

    /**
     * Row list for the active tab + search term. `servicos_count` (via
     * withCount) covers every serviço regardless of status (matches the
     * prototype's "nº Serviços" column); the eager-loaded `servicos`
     * relation is pre-filtered to non-suspenso/cancelado for the MRR sum
     * in mrrDe(), mirroring Totais::calcular()'s normalisation.
     *
     * @return LengthAwarePaginator<int, Cliente>
     */
    #[Computed]
    public function clientes(): LengthAwarePaginator
    {
        return Cliente::query()
            ->withCount('servicos')
            ->with(['servicos' => fn ($query) => $query->whereNotIn('status', [
                ServicoStatus::Cancelado, ServicoStatus::Suspenso,
            ])])
            ->when($this->tab === 'activo', fn ($query) => $query->where('status', ClienteStatus::Activo))
            ->when($this->tab === 'inactivo', fn ($query) => $query->where('status', ClienteStatus::Inactivo))
            ->when(trim($this->q) !== '', function ($query): void {
                $term = '%'.trim($this->q).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('nome', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('empresa', 'like', $term);
                });
            })
            ->orderBy('nome')
            ->paginate(self::POR_PAGINA);
    }

    /**
     * @return array{todos: int, activo: int, inactivo: int}
     */
    #[Computed]
    public function counts(): array
    {
        return [
            'todos' => Cliente::query()->count(),
            'activo' => Cliente::query()->where('status', ClienteStatus::Activo)->count(),
            'inactivo' => Cliente::query()->where('status', ClienteStatus::Inactivo)->count(),
        ];
    }

    /**
     * Monthly-normalised revenue for one client's non-suspenso/cancelado
     * serviços — same normalisation Totais::calcular() uses for the
     * global MRR, just scoped to a single client.
     */
    public function mrrDe(Cliente $cliente): float
    {
        return round($cliente->servicos->sum(fn (Servico $s) => match ($s->periodicidade) {
            Periodicidade::Mensal => (float) $s->valor,
            Periodicidade::Trimestral => (float) $s->valor / 3,
            Periodicidade::Semestral => (float) $s->valor / 6,
            Periodicidade::Anual => (float) $s->valor / 12,
            Periodicidade::Unica, Periodicidade::Personalizada => 0.0,
        }));
    }

    #[On('cliente-guardado')]
    public function clienteGuardado(): void
    {
        unset($this->clientes, $this->counts);
    }

    public function render()
    {
        return view('livewire.clientes.clientes-index');
    }
}
