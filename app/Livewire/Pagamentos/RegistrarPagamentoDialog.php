<?php

namespace App\Livewire\Pagamentos;

use App\Models\Cliente;
use App\Models\Historico;
use App\Models\Pagamento;
use App\Models\Servico;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Nested dialog for recording a plain ledger payment — opened via the
 * browser event 'pagamento:abrir' (no payload), same trigger used from two
 * different pages, each holding its own instance of this component:
 *
 * - ServicoDetail mounts it with `:servico-id="$s->id"` fixed at mount time,
 *   so the cliente/serviço pickers are hidden and every field is scoped to
 *   that one servico.
 * - PagamentosIndex mounts it bare (no servicoId), so the pickers are shown
 *   and any cliente/servico combination can be recorded.
 *
 * Unlike RenovarDialog, this intentionally never touches Servico.status or
 * Servico.vencimento and never calls ServicoStatusService — it is a plain
 * ledger entry (e.g. a partial/advance/off-cycle payment), not a renewal.
 */
class RegistrarPagamentoDialog extends Component
{
    /** @var list<array{value: string, label: string}> */
    private const PERIODO_OPTIONS = [
        ['value' => '1 mês', 'label' => '1 mês'],
        ['value' => '3 meses', 'label' => '3 meses'],
        ['value' => '6 meses', 'label' => '6 meses'],
        ['value' => '1 ano', 'label' => '1 ano'],
        ['value' => 'avulso', 'label' => 'Avulso'],
    ];

    public bool $show = false;

    /** Fixed at mount time when this instance is scoped to one servico. */
    public ?int $servicoId = null;

    public string $clienteSelecionado = '';

    public string $servicoSelecionado = '';

    public string $valor = '';

    public string $data = '';

    public string $periodo = '1 mês';

    public string $metodo = 'mpesa';

    public function mount(?int $servicoId = null): void
    {
        $this->servicoId = $servicoId;
    }

    #[On('pagamento:abrir')]
    public function abrir(): void
    {
        $this->resetValidation();
        $this->data = Carbon::today()->toDateString();
        $this->periodo = '1 mês';

        if ($this->servicoId !== null) {
            $servico = Servico::find($this->servicoId);
            $this->valor = $servico ? (string) $servico->valor : '';
            $this->metodo = $servico?->metodo_habitual?->value ?? 'mpesa';
        } else {
            $primeiroServico = Servico::query()->orderBy('nome')->first();
            $this->servicoSelecionado = $primeiroServico ? (string) $primeiroServico->id : '';
            $this->clienteSelecionado = $primeiroServico ? (string) $primeiroServico->cliente_id : '';
            $this->valor = $primeiroServico ? (string) $primeiroServico->valor : '';
            $this->metodo = $primeiroServico?->metodo_habitual?->value ?? 'mpesa';
            unset($this->servicosOptions);
        }

        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    /**
     * Changing the cliente re-scopes the serviço options to that cliente and
     * jumps to its first serviço, same "pick A, B narrows" idiom
     * NewSubscriptionDialog uses for biblioteca → descrição.
     */
    public function updatedClienteSelecionado(): void
    {
        unset($this->servicosOptions);

        $primeiro = Servico::query()->where('cliente_id', (int) $this->clienteSelecionado)->orderBy('nome')->first();
        $this->servicoSelecionado = $primeiro ? (string) $primeiro->id : '';
        $this->valor = $primeiro ? (string) $primeiro->valor : '';
    }

    public function updatedServicoSelecionado(): void
    {
        $servico = $this->servicoSelecionado !== '' ? Servico::find((int) $this->servicoSelecionado) : null;

        if ($servico) {
            $this->valor = (string) $servico->valor;
        }
    }

    /** True when the servico is fixed by the caller (ServicoDetail) and the pickers must stay hidden. */
    #[Computed]
    public function bloqueado(): bool
    {
        return $this->servicoId !== null;
    }

    #[Computed]
    public function servico(): ?Servico
    {
        $id = $this->servicoId ?? ($this->servicoSelecionado !== '' ? (int) $this->servicoSelecionado : null);

        return $id ? Servico::with('cliente')->find($id) : null;
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function clientesOptions(): array
    {
        return Cliente::query()->orderBy('nome')->get()
            ->map(fn (Cliente $c) => ['value' => (string) $c->id, 'label' => $c->nome])
            ->all();
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function servicosOptions(): array
    {
        return Servico::query()
            ->when($this->clienteSelecionado !== '', fn ($q) => $q->where('cliente_id', (int) $this->clienteSelecionado))
            ->orderBy('nome')
            ->get()
            ->map(fn (Servico $s) => ['value' => (string) $s->id, 'label' => $s->nome])
            ->all();
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function periodoOptions(): array
    {
        return self::PERIODO_OPTIONS;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = [
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data' => ['required', 'date'],
            'periodo' => ['required', 'string'],
            'metodo' => ['required', 'string'],
        ];

        if ($this->servicoId === null) {
            $rules['clienteSelecionado'] = ['required', 'string', Rule::exists('clientes', 'id')];
            $rules['servicoSelecionado'] = ['required', 'string', Rule::exists('servicos', 'id')];
        }

        return $rules;
    }

    public function confirmar(): void
    {
        $data = $this->validate();
        $servico = $this->servico;

        if (! $servico) {
            $this->addError('servicoSelecionado', 'Selecione um serviço.');

            return;
        }

        DB::transaction(function () use ($servico, $data): void {
            Pagamento::create([
                'data' => $data['data'],
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'valor' => $data['valor'],
                'metodo' => $data['metodo'],
                'periodo' => $data['periodo'],
            ]);

            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Pagamento registado',
                'description' => sprintf('%s — %s', $data['valor'], $servico->nome),
                'kind' => 'pagamento',
            ]);
        });

        $valorFormatado = number_format((float) $data['valor'], 0, ',', '.');

        $this->dispatch('toast', title: 'Pagamento registado', body: $valorFormatado.' MZN — '.$servico->nome, tone: 'success');
        // Reuses the same refresh signal RenovarDialog dispatches, so both
        // ServicoDetail's and PagamentosIndex's own listeners for it pick up
        // the new ledger row without a dedicated event just for this dialog.
        $this->dispatch('servico-actualizado', servicoId: $servico->id);

        $this->show = false;
    }

    public function render()
    {
        return view('livewire.pagamentos.registrar-pagamento-dialog');
    }
}
