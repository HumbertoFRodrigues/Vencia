<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Livewire\Pessoal\Concerns\NavegaMeses;
use App\Models\CategoriaPessoal;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Movimentos pessoais')]
class Movimentos extends Component
{
    use NavegaMeses;

    #[Url(except: 'todos')]
    public string $tipo = 'todos';

    #[Url(except: '')]
    public string $categoria = '';

    #[Url(except: '')]
    public string $q = '';

    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();
    }

    public function setTab(string $tipo): void
    {
        $this->tipo = in_array($tipo, ['todos', 'entrada', 'saida'], true) ? $tipo : 'todos';
        $this->categoria = '';
    }

    private function base(): Builder
    {
        return MovimentoPessoal::query()->contabilizados()->noMes($this->mesActual())
            ->when(trim($this->q) !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->where('descricao', 'like', '%'.trim($this->q).'%')
                ->orWhere('notas', 'like', '%'.trim($this->q).'%')))
            ->when($this->categoria !== '', fn (Builder $b) => $b->where('categoria_pessoal_id', (int) $this->categoria));
    }

    #[Computed]
    public function movimentos()
    {
        return $this->base()
            ->when($this->tipo !== 'todos', fn (Builder $b) => $b->where('tipo', $this->tipo))
            ->with(['categoria', 'fixo'])
            ->orderByDesc('data')->orderByDesc('id')
            ->get();
    }

    /** Counts per tab and totals ignore the tab itself, so the tabs always show the whole picture. */
    #[Computed]
    public function totais(): array
    {
        $linhas = $this->base()->get(['tipo', 'valor']);
        $entradas = (float) $linhas->where('tipo', TipoMovimento::Entrada)->sum('valor');
        $saidas = (float) $linhas->where('tipo', TipoMovimento::Saida)->sum('valor');

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $entradas - $saidas,
            'nTodos' => $linhas->count(),
            'nEntradas' => $linhas->where('tipo', TipoMovimento::Entrada)->count(),
            'nSaidas' => $linhas->where('tipo', TipoMovimento::Saida)->count(),
        ];
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function categoriaOptions(): array
    {
        $opcoes = CategoriaPessoal::query()
            ->when($this->tipo !== 'todos', fn (Builder $b) => $b->where('tipo', $this->tipo))
            ->orderBy('tipo', 'desc')->orderBy('ordem')->orderBy('nome')
            ->get()
            ->map(fn (CategoriaPessoal $c) => [
                'value' => (string) $c->id,
                'label' => ($this->tipo === 'todos' ? ($c->tipo === TipoMovimento::Entrada ? 'Entrada · ' : 'Saída · ') : '').$c->nome.($c->arquivado ? ' (arquivada)' : ''),
            ])
            ->all();

        return array_merge([['value' => '', 'label' => 'Todas as categorias']], $opcoes);
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->movimentos, $this->totais);
    }

    public function render()
    {
        return view('livewire.pessoal.movimentos');
    }
}
