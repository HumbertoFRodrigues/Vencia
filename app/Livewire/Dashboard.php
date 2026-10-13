<?php

namespace App\Livewire;

use App\Enums\ServicoStatus;
use App\Models\Configuracao;
use App\Models\Servico;
use App\Services\RelatorioPdf;
use App\Services\Totais;
use App\Support\Csv;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
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
     * Próximos vencimentos — every non-suspended, non-cancelled service,
     * nearest due date first.
     *
     * @return Collection<int, Servico>
     */
    #[Computed]
    public function proximosVencimentos(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereNotIn('status', [ServicoStatus::Cancelado, ServicoStatus::Suspenso])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    /**
     * Alerts: services that are overdue or due soon.
     *
     * @return Collection<int, Servico>
     */
    #[Computed]
    public function alertas(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    /**
     * Exports the same "próximos vencimentos" list shown on this screen —
     * the only single dataset the Dashboard itself owns (the metric tiles
     * are aggregates, not a row list, so there's nothing else to export
     * here). Mirrors PagamentosIndex::exportarCsv()'s exact CSV conventions
     * (UTF-8 BOM, semicolon delimiter, dd/mm/aaaa dates, dot-thousands MZN).
     */
    public function exportarCsv(): StreamedResponse
    {
        $servicos = $this->proximosVencimentos;

        $this->dispatch('toast', title: 'A exportar CSV', body: 'A transferência começa em breve.', tone: 'success');

        return Csv::download(
            'proximos_vencimentos_'.now()->format('Y-m-d_His').'.csv',
            ['Serviço', 'Cliente', 'Vencimento', 'Valor', 'Estado'],
            $servicos->map(fn ($s) => [
                $s->nome,
                $s->cliente?->nome ?? '',
                $s->vencimento?->format('d/m/Y') ?? '',
                number_format((float) $s->valor, 0, ',', '.').' '.Configuracao::moeda(),
                $this->statusLabel($s->status),
            ]),
        );
    }

    /** Same "próximos vencimentos" rows as exportarCsv(), through the shared relatório template. */
    public function exportarPdf(): StreamedResponse
    {
        $servicos = $this->proximosVencimentos;

        $linhas = $servicos->map(fn (Servico $s) => [
            $s->nome,
            $s->cliente?->nome ?? '',
            $s->vencimento?->format('d/m/Y') ?? '',
            RelatorioPdf::moeda((float) $s->valor),
            $this->statusLabel($s->status),
        ])->all();

        $this->dispatch('toast', title: 'A exportar PDF', body: 'A transferência começa em breve.', tone: 'success');

        return RelatorioPdf::gerar(
            titulo: 'Próximos Vencimentos',
            subtitulo: $servicos->count() === 1
                ? 'O serviço mais próximo do vencimento'
                : 'Os '.$servicos->count().' serviços mais próximos do vencimento',
            colunas: ['Serviço', 'Cliente', 'Vencimento', 'Valor', 'Estado'],
            linhas: $linhas,
            nomeArquivo: 'proximos_vencimentos_'.now()->format('Y-m-d_His').'.pdf',
            colunasNumericas: [3],
        );
    }

    /** Mirrors StatusBadge's label map (that one's LABELS const is private to the component). */
    private function statusLabel(ServicoStatus $status): string
    {
        return match ($status) {
            ServicoStatus::Activo => 'Activo',
            ServicoStatus::AVencer => 'A vencer',
            ServicoStatus::Vencido => 'Vencido',
            ServicoStatus::Suspenso => 'Suspenso',
            ServicoStatus::Cancelado => 'Cancelado',
        };
    }

    #[On('servico-criado')]
    #[On('servico-actualizado')]
    public function actualizar(): void
    {
        unset($this->totais, $this->proximosVencimentos, $this->alertas);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
