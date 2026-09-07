<?php

namespace App\Livewire\Financas;

use App\Enums\ServicoStatus;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Services\RelatorioPdf;
use App\Services\Totais;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * "Como o dinheiro entrou" — one row per active método (even one with
     * zero pagamentos this month still gets a row, per the brief), current
     * calendar month, sorted by amount descending. Also includes any
     * *archived* método that still has a payment this month (an admin can
     * archive a method mid-month and the breakdown must still account for
     * every real MZN that came in), even though it's no longer offered as a
     * selectable option elsewhere.
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
        $vistos = [];

        foreach (MetodoPagamentoOpcao::nomesActivos() as $nome) {
            $vistos[$nome] = true;
            $linhas[] = ['metodo' => $nome, 'valor' => (float) ($somas[$nome] ?? 0)];
        }

        foreach ($somas->keys() as $nome) {
            if (! isset($vistos[$nome])) {
                $linhas[] = ['metodo' => $nome, 'valor' => (float) $somas[$nome]];
            }
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

    /** Streams the "A receber" table currently shown (vencido/a_vencer serviços) as a pt-PT-formatted CSV. */
    public function exportarCsv(): StreamedResponse
    {
        $servicos = $this->aReceber;

        $this->dispatch('toast', title: 'A exportar CSV', body: 'A transferência começa em breve.', tone: 'success');

        return response()->streamDownload(function () use ($servicos): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Serviço', 'Cliente', 'Vencimento', 'Período', 'Valor'], ';');

            foreach ($servicos as $s) {
                fputcsv($handle, [
                    $s->nome,
                    $s->cliente?->nome ?? '',
                    $s->vencimento?->format('d/m/Y') ?? '',
                    $s->periodicidade->label(),
                    number_format((float) $s->valor, 0, ',', '.').' '.\App\Models\Configuracao::moeda(),
                ], ';');
            }

            fclose($handle);
        }, 'financas_'.now()->format('Y-m-d_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Same "A receber" table as exportarCsv(), through the shared relatório
     * template. Also carries a short MRR/ARR/entradas summary above the
     * table — unlike Dashboard/Pagamentos, this screen has more than one
     * exportable "thing" (the tiles are a second real dataset, not just
     * decoration), so a bare row-list would leave out half of what this
     * screen actually reports.
     */
    public function exportarPdf(): StreamedResponse
    {
        $servicos = $this->aReceber;
        $totais = $this->totais;

        $linhas = $servicos->map(fn (Servico $s) => [
            $s->nome,
            $s->cliente?->nome ?? '',
            $s->vencimento?->format('d/m/Y') ?? '',
            $s->periodicidade->label(),
            RelatorioPdf::moeda((float) $s->valor),
        ])->all();

        $this->dispatch('toast', title: 'A exportar PDF', body: 'A transferência começa em breve.', tone: 'success');

        return RelatorioPdf::gerar(
            titulo: 'Finanças — A Receber',
            subtitulo: 'Serviços vencidos e a vencer, com o resumo financeiro do mês corrente',
            colunas: ['Serviço', 'Cliente', 'Vencimento', 'Período', 'Valor'],
            linhas: $linhas,
            nomeArquivo: 'financas_'.now()->format('Y-m-d_His').'.pdf',
            colunasNumericas: [4],
            resumo: [
                'Entradas do mês' => RelatorioPdf::moeda($totais['entradas']),
                'A receber' => RelatorioPdf::moeda($totais['aReceber']),
                'Em atraso' => RelatorioPdf::moeda($totais['emAtraso']),
                'Receita mensal recorrente' => RelatorioPdf::moeda($totais['mrr']),
                'Receita anual recorrente' => RelatorioPdf::moeda($totais['arr']),
            ],
        );
    }

    public function render()
    {
        return view('livewire.financas.financas-index');
    }
}
