<?php

namespace App\Livewire\Pagamentos;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Services\Totais;
use App\View\Components\Ui\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
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
    /** @var list<string> */
    public array $metodos = [];

    #[Url]
    public string $mes = 'todos';

    #[Url]
    public string $cliente = 'todos';

    #[Url]
    public string $q = '';

    public function alternarMetodo(string $metodo): void
    {
        if (in_array($metodo, $this->metodos, true)) {
            $this->metodos = array_values(array_diff($this->metodos, [$metodo]));
        } else {
            $this->metodos[] = $metodo;
        }
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

    /** @return Collection<int, Pagamento> */
    #[Computed]
    public function pagamentos(): Collection
    {
        return Pagamento::query()
            ->with(['cliente', 'servico'])
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
            })
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function totalFiltrado(): float
    {
        return (float) $this->pagamentos->sum(fn (Pagamento $p) => (float) $p->valor);
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

    #[On('servico-actualizado')]
    public function actualizar(): void
    {
        unset($this->pagamentos, $this->totalFiltrado, $this->totais, $this->mesesDisponiveis);
    }

    /** Streams the pagamentos currently visible under the applied filters as a pt-PT-formatted CSV. */
    public function exportarCsv(): StreamedResponse
    {
        $pagamentos = $this->pagamentos;

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
                    PaymentMethod::labelFor($p->metodo->value),
                    number_format((float) $p->valor, 0, ',', '.').' MZN',
                ], ';');
            }

            fclose($handle);
        }, 'pagamentos_'.now()->format('Y-m-d_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('livewire.pagamentos.pagamentos-index');
    }
}
