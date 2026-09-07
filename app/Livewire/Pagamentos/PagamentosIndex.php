<?php

namespace App\Livewire\Pagamentos;

use App\Enums\HistoricoKind;
use App\Models\Cliente;
use App\Models\Historico;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Pagamento;
use App\Services\RelatorioPdf;
use App\Services\Totais;
use App\View\Components\Ui\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Full payments ledger — 4 headline tiles (from Totais::calcular(), same
 * numbers as Dashboard/Finanças, never re-derived), method-logo pills as
 * multi-select toggle filters, plus mês/cliente selects and a live search.
 *
 * Deviation from the brief's suggestion of an *additional* método <select>
 * alongside the pills: the pills already are the método filter (multi-select,
 * not a single dropdown value), so a second select offering the same choice
 * would just be a redundant, less expressive control. Only mês/cliente/search
 * get selects here.
 */
#[Layout('layouts.app')]
#[Title('Pagamentos')]
class PagamentosIndex extends Component
{
    use WithPagination;

    /** Dense ledger rows — 25/page. */
    private const POR_PAGINA = 25;

    /** @var list<string> */
    public array $metodos = [];

    #[Url]
    public string $mes = 'todos';

    #[Url]
    public string $cliente = 'todos';

    #[Url]
    public string $q = '';

    /** Non-null while a row's "Apagar" action is showing its inline "tens a certeza?" confirm step. */
    public ?int $confirmandoRemocaoId = null;

    public function alternarMetodo(string $metodo): void
    {
        if (in_array($metodo, $this->metodos, true)) {
            $this->metodos = array_values(array_diff($this->metodos, [$metodo]));
        } else {
            $this->metodos[] = $metodo;
        }

        $this->resetPage();
    }

    /** Mês <select> (wire:model.live) — changing it must land back on page 1. */
    public function updatedMes(): void
    {
        $this->resetPage();
    }

    /** Cliente <select> (wire:model.live) — same. */
    public function updatedCliente(): void
    {
        $this->resetPage();
    }

    /** Search box (wire:model.live.debounce) — same. */
    public function updatedQ(): void
    {
        $this->resetPage();
    }

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
     * Every método/mês/cliente/search `->when()` clause, shared by the
     * paginated list, the period total, and the CSV export — those last two
     * must reflect *every* filtered row, not just whichever page is on
     * screen, so they build straight off this instead of off `pagamentos()`.
     */
    private function pagamentosQuery(): Builder
    {
        return Pagamento::query()
            ->when($this->metodos !== [], fn ($query) => $query->whereIn('metodo', $this->metodos))
            ->when($this->mes !== 'todos', function ($query): void {
                [$ano, $mesNumero] = explode('-', $this->mes);
                $query->whereYear('data', (int) $ano)->whereMonth('data', (int) $mesNumero);
            })
            ->when($this->cliente !== 'todos', fn ($query) => $query->where('cliente_id', (int) $this->cliente))
            ->when(trim($this->q) !== '', function ($query): void {
                $term = '%'.trim($this->q).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->whereHas('cliente', fn ($c) => $c->where('nome', 'like', $term))
                        ->orWhereHas('servico', fn ($s) => $s->where('nome', 'like', $term));
                });
            });
    }

    /** @return LengthAwarePaginator<int, Pagamento> */
    #[Computed]
    public function pagamentos(): LengthAwarePaginator
    {
        return $this->pagamentosQuery()
            ->with(['cliente', 'servico'])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->paginate(self::POR_PAGINA);
    }

    /** Sum across every filtered pagamento, not just the current page. */
    #[Computed]
    public function totalFiltrado(): float
    {
        return (float) $this->pagamentosQuery()->sum('valor');
    }

    /**
     * Every calendar month that has at least one pagamento, newest first,
     * plus "Todos os meses" — not just the current month, so the filter is
     * actually useful once the ledger spans more than one period.
     *
     * @return list<array{value: string, label: string}>
     */
    #[Computed]
    public function mesesDisponiveis(): array
    {
        $opcoes = [['value' => 'todos', 'label' => 'Todos os meses']];
        $vistos = [];

        foreach (Pagamento::query()->orderByDesc('data')->pluck('data') as $data) {
            $chave = $data->format('Y-m');

            if (isset($vistos[$chave])) {
                continue;
            }

            $vistos[$chave] = true;
            $opcoes[] = [
                'value' => $chave,
                'label' => ucfirst($data->locale('pt_PT')->translatedFormat('F')).' de '.$data->year,
            ];
        }

        return $opcoes;
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function clientesOptions(): array
    {
        $opcoes = [['value' => 'todos', 'label' => 'Todos os clientes']];

        foreach (Cliente::query()->orderBy('nome')->get() as $c) {
            $opcoes[] = ['value' => (string) $c->id, 'label' => $c->nome];
        }

        return $opcoes;
    }

    /**
     * Every active (non-arquivado) método, for the filter pill-strip — the
     * pills also double as the applied filter's own value list (see
     * $metodos above), so an archived método the ledger still holds old rows
     * for stays filterable-out-of-the-box (it simply won't show as a pill to
     * toggle, matching "archived is no longer offered as a choice").
     *
     * @return list<string>
     */
    #[Computed]
    public function metodosDisponiveis(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
    }

    #[On('servico-actualizado')]
    public function actualizar(): void
    {
        unset($this->pagamentos, $this->totalFiltrado, $this->totais, $this->mesesDisponiveis);
    }

    /** Shows the inline "tens a certeza?" confirm step for one row instead of deleting on a single click. */
    public function pedirConfirmacaoRemocao(int $id): void
    {
        $this->confirmandoRemocaoId = $id;
    }

    public function cancelarRemocao(): void
    {
        $this->confirmandoRemocaoId = null;
    }

    /**
     * Hard-deletes a pagamento (it has no deleted_at column — this is a
     * plain correctable ledger row, not the append-only Historico) after the
     * inline confirm step above. Logs a Historico entry recording what was
     * deleted — amount/date/método, since the row itself won't exist any
     * more to look those up from — *before* deleting, in the same
     * transaction. Deliberately never touches the associated Servico:
     * vencimento/status are governed by ServicoStatusService from whichever
     * action (e.g. Renovar) produced this payment, not by the ledger, and
     * removing the ledger row later must not retroactively undo that.
     */
    public function apagar(int $id): void
    {
        $pagamento = Pagamento::findOrFail($id);

        DB::transaction(function () use ($pagamento): void {
            Historico::create([
                'cliente_id' => $pagamento->cliente_id,
                'servico_id' => $pagamento->servico_id,
                'occurred_at' => Carbon::now(),
                'title' => 'Pagamento apagado',
                'description' => sprintf(
                    '%s — %s — %s',
                    number_format((float) $pagamento->valor, 2, ',', '.').' '.\App\Models\Configuracao::moeda(),
                    $pagamento->data->format('d/m/Y'),
                    PaymentMethod::labelFor($pagamento->metodo),
                ),
                'kind' => HistoricoKind::Pagamento,
            ]);

            $pagamento->delete();
        });

        $this->confirmandoRemocaoId = null;
        unset($this->pagamentos, $this->totalFiltrado, $this->totais, $this->mesesDisponiveis);

        $this->dispatch('toast', title: 'Pagamento apagado', body: 'O registo foi removido do livro de pagamentos.', tone: 'danger');
    }

    /** Streams every pagamento matching the applied filters (not just the visible page) as a pt-PT-formatted CSV. */
    public function exportarCsv(): StreamedResponse
    {
        $pagamentos = $this->pagamentosQuery()
            ->with(['cliente', 'servico'])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($pagamentos): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Data', 'Cliente', 'Serviço', 'Período', 'Método', 'Valor'], ';');

            foreach ($pagamentos as $p) {
                fputcsv($handle, [
                    $p->data->format('d/m/Y'),
                    $p->cliente?->nome ?? '',
                    $p->servico?->nome ?? '',
                    $p->periodo,
                    PaymentMethod::labelFor($p->metodo),
                    number_format((float) $p->valor, 0, ',', '.').' '.\App\Models\Configuracao::moeda(),
                ], ';');
            }

            fclose($handle);
        }, 'pagamentos_'.now()->format('Y-m-d_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Every filter that's currently narrowing the ledger, in the same
     * mês/métodos/cliente/pesquisa order the filter bar shows them — used as
     * the PDF export's subtitle so the report says exactly what it covers.
     */
    private function descricaoFiltros(): string
    {
        $partes = [];

        if ($this->mes === 'todos') {
            $partes[] = 'Todos os meses';
        } else {
            $opcao = collect($this->mesesDisponiveis)->firstWhere('value', $this->mes);
            $partes[] = $opcao['label'] ?? $this->mes;
        }

        if ($this->metodos !== []) {
            $partes[] = 'Métodos: '.implode(', ', array_map(
                fn (string $m) => PaymentMethod::labelFor($m),
                $this->metodos,
            ));
        }

        if ($this->cliente !== 'todos') {
            $opcao = collect($this->clientesOptions)->firstWhere('value', $this->cliente);
            $partes[] = 'Cliente: '.($opcao['label'] ?? $this->cliente);
        }

        if (trim($this->q) !== '') {
            $partes[] = 'Pesquisa: "'.trim($this->q).'"';
        }

        return implode(' · ', $partes);
    }

    /** Same filtered ledger as exportarCsv() (reuses pagamentosQuery()), through the shared relatório template. */
    public function exportarPdf(): StreamedResponse
    {
        $pagamentos = $this->pagamentosQuery()
            ->with(['cliente', 'servico'])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->get();

        $linhas = $pagamentos->map(fn (Pagamento $p) => [
            $p->data->format('d/m/Y'),
            $p->cliente?->nome ?? '',
            $p->servico?->nome ?? '',
            $p->periodo,
            PaymentMethod::labelFor($p->metodo),
            RelatorioPdf::moeda((float) $p->valor),
        ])->all();

        return RelatorioPdf::gerar(
            titulo: 'Pagamentos',
            subtitulo: $this->descricaoFiltros(),
            colunas: ['Data', 'Cliente', 'Serviço', 'Período', 'Método', 'Valor'],
            linhas: $linhas,
            nomeArquivo: 'pagamentos_'.now()->format('Y-m-d_His').'.pdf',
            colunasNumericas: [5],
        );
    }

    public function render()
    {
        return view('livewire.pagamentos.pagamentos-index');
    }
}
