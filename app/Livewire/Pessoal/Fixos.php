<?php

namespace App\Livewire\Pessoal;

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
        // Running ones first (by due day), then future ones, then the ones that already ended.
        return FixoPessoal::query()->with('categoria')
            ->orderBy('dia_mes')->orderBy('descricao')
            ->get()
            ->sortBy(fn (FixoPessoal $f) => $f->emCurso() ? ($f->futuro() ? 1 : 0) : 2)
            ->groupBy(fn (FixoPessoal $f) => $f->tipo->value);
    }

    #[Computed]
    public function totais(): array
    {
        ['entradas' => $entradas, 'saidas' => $saidas] = app(FinancasPessoais::class)->fixosEmCurso();

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
