<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Models\FixoPessoal;
use App\Services\FinancasPessoais;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Fixos mensais')]
class Fixos extends Component
{
    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();
    }

    #[Computed]
    public function fixos()
    {
        return FixoPessoal::query()->with('categoria')
            ->orderByDesc('activo')->orderBy('dia_mes')->orderBy('descricao')
            ->get()
            ->groupBy(fn (FixoPessoal $f) => $f->tipo->value);
    }

    #[Computed]
    public function totais(): array
    {
        $activos = FixoPessoal::query()->where('activo', true)->get(['tipo', 'valor']);
        $entradas = (float) $activos->where('tipo', TipoMovimento::Entrada)->sum('valor');
        $saidas = (float) $activos->where('tipo', TipoMovimento::Saida)->sum('valor');

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'sobra' => $entradas - $saidas,
            'peso' => $entradas > 0 ? ($saidas / $entradas) * 100 : null,
        ];
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->fixos, $this->totais);
    }

    public function render()
    {
        return view('livewire.pessoal.fixos');
    }
}
