<?php

namespace App\Livewire\Financas;

use App\Enums\MetodoPagamento;
use App\Enums\ServicoStatus;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Services\Totais;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Finanças')]
class FinancasIndex extends Component
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
     * "Como o dinheiro entrou" — one row per MetodoPagamento case (even a
     * método with zero pagamentos this month still gets a row, per the
     * brief), current calendar month, sorted by amount descending.
     *
     * @return list<array{metodo: string, valor: float}>
     */
    #[Computed]
    public function porMetodo(): array
    {
        $inicioMes = Carbon::today()->startOfMonth();
        $fimMes = Carbon::today()->endOfMonth();

        $somas = Pagamento::query()
            ->whereBetween('data', [$inicioMes->toDateString(), $fimMes->toDateString()])
            ->selectRaw('metodo, sum(valor) as total')
            ->groupBy('metodo')
            ->pluck('total', 'metodo');

        $linhas = [];

        foreach (MetodoPagamento::cases() as $case) {
            $linhas[] = ['metodo' => $case->value, 'valor' => (float) ($somas[$case->value] ?? 0)];
        }

        usort($linhas, fn (array $a, array $b) => $b['valor'] <=> $a['valor']);

        return $linhas;
    }

    /**
     * "Entradas por mês" — this month plus the previous 4, oldest first.
     *
     * @return list<array{label: string, valor: float}>
     */
    #[Computed]
    public function entradasPorMes(): array
    {
        $meses = [];

        for ($i = 4; $i >= 0; $i--) {
            $referencia = Carbon::today()->subMonthsNoOverflow($i);
            $inicio = $referencia->copy()->startOfMonth()->toDateString();
            $fim = $referencia->copy()->endOfMonth()->toDateString();

            $meses[] = [
                'label' => ucfirst($referencia->locale('pt_PT')->translatedFormat('F')),
                'valor' => (float) Pagamento::whereBetween('data', [$inicio, $fim])->sum('valor'),
            ];
        }

        return $meses;
    }

    /** @return Collection<int, Servico> */
    #[Computed]
    public function aReceber(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->get();
    }

    #[On('servico-actualizado')]
    public function actualizar(): void
    {
        unset($this->totais, $this->porMetodo, $this->entradasPorMes, $this->aReceber);
    }

    public function render()
    {
        return view('livewire.financas.financas-index');
    }
}
