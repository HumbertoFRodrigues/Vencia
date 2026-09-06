<?php

namespace App\Livewire\Servicos;

use App\Enums\ServicoCategoria;
use App\Enums\ServicoStatus;
use App\Models\BibliotecaServico;
use App\Models\Servico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Grid of every serviço across all clients — status tabs + categoria select
 * + search — plus the "Biblioteca de serviços" pill strip at the bottom.
 *
 * The `?cliente=` query param is the link target Cliente detalhe's "Novo
 * serviço" button already points to (Fase 3). It both pre-filters the grid
 * to that client's serviços and pre-opens NewSubscriptionDialog with that
 * client pre-selected (see mount()): the admin followed that link
 * specifically to create a new serviço for this client, so opening the
 * dialog immediately saves a click; filtering the grid keeps the page
 * coherent if they close the dialog without saving.
 */
#[Layout('layouts.app')]
#[Title('Serviços')]
class ServicosIndex extends Component
{
    use WithPagination;

    /** Card grid at minmax(268px,1fr) — 12 fills a tidy 3-4 column x 3-4 row block on typical widths. */
    private const POR_PAGINA = 12;

    /** @var array<string, string> */
    private const CATEGORIA_LABELS = [
        'dominio' => 'Domínio',
        'hospedagem' => 'Hospedagem',
        'email' => 'Email',
        'ia' => 'IA',
        'software' => 'Software',
        'manutencao' => 'Manutenção',
        'desenvolvimento' => 'Desenvolvimento',
        'outro' => 'Outro',
    ];

    #[Url]
    public string $tab = 'todos';

    #[Url]
    public string $categoria = 'todas';

    #[Url]
    public string $q = '';

    #[Url(as: 'cliente')]
    public ?int $clienteId = null;

    public function mount(): void
    {
        if ($this->clienteId !== null) {
            $this->dispatch('assinatura:abrir', clienteId: $this->clienteId);
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    /** Categoria <select> (wire:model.live) — changing it must land back on page 1. */
    public function updatedCategoria(): void
    {
        $this->resetPage();
    }

    /** Search box (wire:model.live.debounce) — same, every change resets to page 1. */
    public function updatedQ(): void
    {
        $this->resetPage();
    }

    /** @return LengthAwarePaginator<int, Servico> */
    #[Computed]
    public function servicos(): LengthAwarePaginator
    {
        return Servico::query()
            ->with('cliente')
            ->when($this->clienteId !== null, fn ($query) => $query->where('cliente_id', $this->clienteId))
            ->when($this->tab !== 'todos', fn ($query) => $query->where('status', $this->tab))
            ->when($this->categoria !== 'todas', fn ($query) => $query->where('categoria', $this->categoria))
            ->when(trim($this->q) !== '', function ($query): void {
                $term = '%'.trim($this->q).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('nome', 'like', $term)
                        ->orWhere('plano', 'like', $term)
                        ->orWhereHas('cliente', fn ($c) => $c->where('nome', 'like', $term));
                });
            })
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->paginate(self::POR_PAGINA);
    }

    /** @return array{todos: int, activo: int, a_vencer: int, vencido: int, suspenso: int} */
    #[Computed]
    public function counts(): array
    {
        $base = Servico::query()->when($this->clienteId !== null, fn ($q) => $q->where('cliente_id', $this->clienteId));

        return [
            'todos' => (clone $base)->count(),
            'activo' => (clone $base)->where('status', ServicoStatus::Activo)->count(),
            'a_vencer' => (clone $base)->where('status', ServicoStatus::AVencer)->count(),
            'vencido' => (clone $base)->where('status', ServicoStatus::Vencido)->count(),
            'suspenso' => (clone $base)->where('status', ServicoStatus::Suspenso)->count(),
        ];
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function categorias(): array
    {
        $options = [['value' => 'todas', 'label' => 'Todas as categorias']];

        foreach (ServicoCategoria::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => self::CATEGORIA_LABELS[$case->value]];
        }

        return $options;
    }

    /** @return Collection<int, BibliotecaServico> */
    #[Computed]
    public function biblioteca(): Collection
    {
        return BibliotecaServico::query()->where('arquivado', false)->orderBy('nome')->get();
    }

    #[On('servico-criado')]
    public function servicoCriado(): void
    {
        unset($this->servicos, $this->counts);
    }

    public function render()
    {
        return view('livewire.servicos.servicos-index');
    }
}
