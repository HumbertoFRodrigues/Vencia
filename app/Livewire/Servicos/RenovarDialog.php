<?php

namespace App\Livewire\Servicos;

use App\Enums\Periodicidade;
use App\Models\Configuracao;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Servico;
use App\Services\ServicoStatusService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Nested dialog opened via the browser event 'renovar:abrir' (servicoId
 * payload), same pattern as ClienteForm. Only servicoId is kept as state
 * (not the model itself) — servico() re-fetches fresh each request, same
 * idiom ClienteForm uses for clienteId.
 */
class RenovarDialog extends Component
{
    /** @var list<array{value: string, label: string}> */
    private const PERIODO_PAGAMENTO_OPTIONS = [
        ['value' => '1 mês', 'label' => '1 mês'],
        ['value' => '3 meses', 'label' => '3 meses'],
        ['value' => '6 meses', 'label' => '6 meses'],
        ['value' => '1 ano', 'label' => '1 ano'],
    ];

    public bool $show = false;

    public ?int $servicoId = null;

    public string $valorPago = '';

    public string $dataPagamento = '';

    public string $periodoPagamento = '1 mês';

    public string $metodo = 'mpesa';

    public ?string $observacoes = null;

    #[On('renovar:abrir')]
    public function abrir(int $servicoId): void
    {
        $this->resetValidation();
        $servico = Servico::findOrFail($servicoId);

        $this->servicoId = $servicoId;
        $this->valorPago = (string) $servico->valor;
        $this->dataPagamento = Carbon::today()->toDateString();
        $this->metodo = $servico->metodo_habitual ?? 'mpesa';
        $this->periodoPagamento = $this->periodoPadrao($servico);
        $this->observacoes = null;
        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    private function periodoPadrao(Servico $servico): string
    {
        return match ($servico->periodicidade) {
            Periodicidade::Trimestral => '3 meses',
            Periodicidade::Semestral => '6 meses',
            Periodicidade::Anual => '1 ano',
            default => '1 mês',
        };
    }

    #[Computed]
    public function servico(): ?Servico
    {
        return $this->servicoId ? Servico::with('cliente')->find($this->servicoId) : null;
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function periodoOptions(): array
    {
        return self::PERIODO_PAGAMENTO_OPTIONS;
    }

    /** Every active (non-arquivado) método, well-known plus any custom one the admin has added. @return list<string> */
    #[Computed]
    public function metodos(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
    }

    /**
     * Live preview of the exact renewal math ServicoStatusService::renovar()
     * will apply — driven solely by the serviço's periodicidade, same as
     * the real call below. It intentionally does not vary with the
     * "período" select: that field only records what this payment is
     * declared to cover on the Pagamento ledger (a free-text label), it
     * doesn't change how far vencimento advances — that's fixed by
     * periodicidade per the brief's locked-in renewal rule (mensal
     * 01/09→01/10, anual 01/09/2026→01/09/2027, regardless of what was
     * paid for).
     */
    #[Computed]
    public function novoVencimento(): ?Carbon
    {
        $servico = $this->servico;

        if (! $servico || $servico->periodicidade === Periodicidade::Unica) {
            return null;
        }

        return app(ServicoStatusService::class)->calcularProximoVencimento($servico);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'valorPago' => ['required', 'numeric', 'min:0.01'],
            'dataPagamento' => ['required', 'date'],
            'periodoPagamento' => ['required', 'string'],
            'metodo' => ['required', 'string'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function confirmar(): void
    {
        $data = $this->validate();
        $servico = $this->servico;

        app(ServicoStatusService::class)->renovar($servico, [
            'data' => $data['dataPagamento'],
            'valor' => $data['valorPago'],
            'metodo' => $data['metodo'],
            'periodo' => $data['periodoPagamento'],
            'observacoes' => $data['observacoes'],
        ]);

        $valorFormatado = number_format((float) $data['valorPago'], 0, ',', '.');

        $this->dispatch(
            'toast',
            title: 'Renovação registrada',
            body: $valorFormatado.' '.Configuracao::moeda().' — '.$servico->nome,
            tone: 'success',
        );
        $this->dispatch('servico-actualizado', servicoId: $servico->id);

        $this->show = false;
    }

    public function render()
    {
        return view('livewire.servicos.renovar-dialog');
    }
}
