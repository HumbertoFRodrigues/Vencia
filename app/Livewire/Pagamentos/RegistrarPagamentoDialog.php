<?php

namespace App\Livewire\Pagamentos;

use App\Enums\HistoricoKind;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Pagamento;
use App\Models\Servico;
use App\View\Components\Ui\PaymentMethod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Nested dialog for recording — or correcting — a plain ledger payment.
 * Opened via the browser event 'pagamento:abrir' (no payload for "create",
 * an int pagamentoId payload for "edit" — same "one component, optional id
 * switches it into edit mode" idiom ClienteForm/EditarServicoDialog use), on
 * two different pages, each holding its own instance of this component:
 *
 * - ServicoDetail mounts it with `:servico-id="$s->id"` fixed at mount time,
 *   so the cliente/serviço pickers are hidden and every field is scoped to
 *   that one servico. It only ever opens this in "create" mode (there is no
 *   payments table on that page to edit from).
 * - PagamentosIndex mounts it bare (no servicoId), so the pickers are shown
 *   for "create" and any cliente/servico combination can be recorded; its
 *   ledger table's "Editar" row action opens this in "edit" mode instead.
 *
 * Unlike RenovarDialog, this intentionally never touches Servico.status or
 * Servico.vencimento and never calls ServicoStatusService — it is a plain
 * ledger entry (e.g. a partial/advance/off-cycle payment or a correction to
 * one), not a renewal. Editing deliberately never reassigns cliente/serviço
 * (same "out of scope" boundary as reassigning a servico to another
 * cliente elsewhere in this app) — those fields stay hidden in edit mode.
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

    /** Field => human label, used both for change-detection and the Histórico description (edit mode only). */
    private const ROTULOS = [
        'data' => 'Data',
        'valor' => 'Valor',
        'metodo' => 'Método',
        'periodo' => 'Período',
    ];

    public bool $show = false;

    /** Fixed at mount time when this instance is scoped to one servico (ServicoDetail's usage). */
    public ?int $servicoId = null;

    /** The servicoId this instance was mounted with — restored at the top of every abrir(), so a
     *  previous edit's temporary override of $servicoId (below) never leaks into the next "create" open. */
    public ?int $servicoIdFixo = null;

    /** Non-null while editing an existing pagamento instead of creating a new one. */
    public ?int $pagamentoId = null;

    public string $clienteSelecionado = '';

    public string $servicoSelecionado = '';

    public string $valor = '';

    public string $data = '';

    public string $periodo = '1 mês';

    public string $metodo = 'mpesa';

    public function mount(?int $servicoId = null): void
    {
        $this->servicoId = $servicoId;
        $this->servicoIdFixo = $servicoId;
    }

    #[On('pagamento:abrir')]
    public function abrir(?int $pagamentoId = null): void
    {
        $this->resetValidation();
        $this->pagamentoId = $pagamentoId;
        $this->servicoId = $this->servicoIdFixo;
        $this->data = Carbon::today()->toDateString();
        $this->periodo = '1 mês';

        if ($pagamentoId !== null) {
            $pagamento = Pagamento::findOrFail($pagamentoId);
            // Fixes the servico the same way ServicoDetail's mount-time scoping
            // does, which is exactly what edit mode needs: cliente/serviço
            // pickers hidden, every other field pre-filled from the record.
            $this->servicoId = $pagamento->servico_id;
            $this->clienteSelecionado = (string) $pagamento->cliente_id;
            $this->servicoSelecionado = (string) $pagamento->servico_id;
            $this->valor = (string) $pagamento->valor;
            $this->data = $pagamento->data->toDateString();
            $this->periodo = $pagamento->periodo;
            $this->metodo = $pagamento->metodo;
        } elseif ($this->servicoId !== null) {
            $servico = Servico::find($this->servicoId);
            $this->valor = $servico ? (string) $servico->valor : '';
            $this->metodo = $servico?->metodo_habitual ?? 'mpesa';
        } else {
            $primeiroServico = Servico::query()->orderBy('nome')->first();
            $this->servicoSelecionado = $primeiroServico ? (string) $primeiroServico->id : '';
            $this->clienteSelecionado = $primeiroServico ? (string) $primeiroServico->cliente_id : '';
            $this->valor = $primeiroServico ? (string) $primeiroServico->valor : '';
            $this->metodo = $primeiroServico?->metodo_habitual ?? 'mpesa';
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

    /** True when the servico is fixed (mount-time scoping or edit mode) and the pickers must stay hidden. */
    #[Computed]
    public function bloqueado(): bool
    {
        return $this->servicoId !== null;
    }

    /** True while editing an existing pagamento rather than creating a new one — drives the dialog's copy. */
    #[Computed]
    public function editando(): bool
    {
        return $this->pagamentoId !== null;
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

    /** Every active (non-arquivado) método, well-known plus any custom one the admin has added. @return list<string> */
    #[Computed]
    public function metodos(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
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

        if ($this->pagamentoId !== null) {
            $this->guardarEdicao($servico, $data);
        } else {
            $this->criar($servico, $data);
        }

        $this->show = false;
    }

    /** @param array<string, mixed> $data */
    private function criar(Servico $servico, array $data): void
    {
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

        $this->dispatch('toast', title: 'Pagamento registado', body: $valorFormatado.' '.Configuracao::moeda().' — '.$servico->nome, tone: 'success');
        // Reuses the same refresh signal RenovarDialog dispatches, so both
        // ServicoDetail's and PagamentosIndex's own listeners for it pick up
        // the new ledger row without a dedicated event just for this dialog.
        $this->dispatch('servico-actualizado', servicoId: $servico->id);
    }

    /**
     * Corrects an existing pagamento in place: data/valor/método/período only
     * — cliente_id/servico_id are never touched (see class docblock). Logs a
     * Historico "Campo: antigo → novo" line per field that actually changed,
     * same pattern as EditarServicoDialog::descreverAlteracoes() — never one
     * entry per field, only the ones that moved. Deliberately does not call
     * ServicoStatusService or touch Servico.vencimento/status: if this
     * payment happened to originate from a Renovar action, that already
     * advanced vencimento independently at the time, and correcting the
     * ledger row afterwards must not retroactively re-run that.
     *
     * @param array<string, mixed> $data
     */
    private function guardarEdicao(Servico $servico, array $data): void
    {
        $pagamento = Pagamento::findOrFail($this->pagamentoId);

        $novosValores = [
            'data' => $data['data'],
            'valor' => $data['valor'],
            'metodo' => $data['metodo'],
            'periodo' => $data['periodo'],
        ];

        DB::transaction(function () use ($pagamento, $novosValores, $servico): void {
            $alteracoes = $this->descreverAlteracoes($pagamento, $novosValores);

            $pagamento->update($novosValores);

            if ($alteracoes !== []) {
                Historico::create([
                    'cliente_id' => $servico->cliente_id,
                    'servico_id' => $servico->id,
                    'occurred_at' => Carbon::now(),
                    'title' => 'Pagamento corrigido',
                    'description' => implode('; ', $alteracoes),
                    'kind' => HistoricoKind::Pagamento,
                ]);
            }
        });

        $this->dispatch('toast', title: 'Pagamento actualizado', body: $servico->nome, tone: 'success');
        $this->dispatch('servico-actualizado', servicoId: $servico->id);
    }

    /**
     * @param  array<string, mixed>  $novosValores
     * @return list<string>
     */
    private function descreverAlteracoes(Pagamento $pagamento, array $novosValores): array
    {
        $alteracoes = [];

        foreach (self::ROTULOS as $campo => $rotulo) {
            $antigo = $this->valorExibicao($campo, $pagamento->getAttribute($campo));
            $novo = $this->valorExibicao($campo, $novosValores[$campo] ?? null);

            if ($antigo === $novo) {
                continue;
            }

            $alteracoes[] = sprintf('%s: %s → %s', $rotulo, $antigo, $novo);
        }

        return $alteracoes;
    }

    private function valorExibicao(string $campo, mixed $valor): string
    {
        if ($campo === 'metodo') {
            return PaymentMethod::labelFor((string) $valor);
        }

        return match ($campo) {
            'valor' => number_format((float) $valor, 2, ',', '.').' '.Configuracao::moeda(),
            'data' => $this->paraData($valor)?->format('d/m/Y') ?? '—',
            default => (string) $valor,
        };
    }

    private function paraData(mixed $valor): ?Carbon
    {
        if ($valor instanceof Carbon) {
            return $valor;
        }

        if ($valor === null || $valor === '') {
            return null;
        }

        return Carbon::parse($valor);
    }

    public function render()
    {
        return view('livewire.pagamentos.registrar-pagamento-dialog');
    }
}
