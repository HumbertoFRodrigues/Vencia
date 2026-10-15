<?php

namespace App\Livewire\Admin;

use App\Models\Conta;
use App\Models\Plano;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Every conta on the platform, with search and a plan filter. */
#[Layout('layouts.app')]
#[Title('Contas')]
class Contas extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $plano = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function contas()
    {
        return Conta::query()
            ->with('plano')
            ->withCount(['membros', 'clientes', 'servicos'])
            ->when($this->q !== '', fn ($q) => $q->where(fn ($w) => $w->where('nome', 'like', '%'.$this->q.'%')
                ->orWhereHas('membros', fn ($m) => $m->where('email', 'like', '%'.$this->q.'%'))))
            ->when($this->plano !== '', fn ($q) => $q->where('plano_id', (int) $this->plano))
            ->latest('id')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.contas', ['planos' => Plano::query()->orderBy('ordem')->get()]);
    }
}
