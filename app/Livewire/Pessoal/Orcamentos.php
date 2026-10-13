<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Livewire\Pessoal\Concerns\NavegaMeses;
use App\Models\CategoriaPessoal;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orçamentos e categorias')]
class Orcamentos extends Component
{
    use NavegaMeses;

    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();
    }

    /**
     * Every category of one tipo with this month's total and, for saídas
     * with a limit, the same budget figures the Resumo shows.
     *
     * @return list<array{categoria: CategoriaPessoal, total: float, orcamento: ?array}>
     */
    private function linhas(TipoMovimento $tipo): array
    {
        $mes = $this->mesActual();

        $totais = MovimentoPessoal::query()->contabilizados()->noMes($mes)
            ->where('tipo', $tipo->value)
            ->selectRaw('categoria_pessoal_id, sum(valor) as total')
            ->groupBy('categoria_pessoal_id')
            ->pluck('total', 'categoria_pessoal_id');

        $orcamentos = $tipo === TipoMovimento::Saida
            ? collect(app(FinancasPessoais::class)->orcamentos($mes))->keyBy(fn ($o) => $o['categoria']->id)
            : collect();

        return CategoriaPessoal::query()->doTipo($tipo)
            ->orderBy('arquivado')->orderBy('ordem')->orderBy('nome')
            ->get()
            ->map(fn (CategoriaPessoal $c) => [
                'categoria' => $c,
                'total' => (float) ($totais[$c->id] ?? 0),
                'orcamento' => $orcamentos->get($c->id),
            ])
            ->all();
    }

    #[Computed]
    public function saidas(): array
    {
        return $this->linhas(TipoMovimento::Saida);
    }

    #[Computed]
    public function entradas(): array
    {
        return $this->linhas(TipoMovimento::Entrada);
    }

    #[Computed]
    public function totais(): array
    {
        $orc = collect($this->saidas)->pluck('orcamento')->filter();
        $limite = (float) $orc->sum('limite');
        $gasto = (float) $orc->sum('gasto');

        return [
            'limite' => $limite,
            'gasto' => $gasto,
            'disponivel' => $limite - $gasto - (float) $orc->sum('pendente'),
            'nAlerta' => $orc->whereIn('estado', ['alerta', 'excedido'])->count(),
            'nComLimite' => $orc->count(),
        ];
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->saidas, $this->entradas, $this->totais);
    }

    public function render()
    {
        return view('livewire.pessoal.orcamentos');
    }
}
