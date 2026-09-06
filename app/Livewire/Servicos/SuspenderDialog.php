<?php

namespace App\Livewire\Servicos;

use App\Enums\HistoricoKind;
use App\Enums\SuspensaoMotivo;
use App\Mail\ServicoTerminadoMail;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\Servico;
use App\Services\ServicoStatusService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class SuspenderDialog extends Component
{
    public bool $show = false;

    public ?int $servicoId = null;

    public string $motivo = 'pagamento_nao_renovado';

    public string $data = '';

    public ?string $observacao = null;

    public bool $enviarEmail = true;

    #[On('suspender:abrir')]
    public function abrir(int $servicoId): void
    {
        $this->resetValidation();
        $this->servicoId = $servicoId;
        $this->motivo = SuspensaoMotivo::PagamentoNaoRenovado->value;
        $this->data = Carbon::today()->toDateString();
        $this->observacao = null;
        $this->enviarEmail = true;
        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    #[Computed]
    public function servico(): ?Servico
    {
        return $this->servicoId ? Servico::with('cliente')->find($this->servicoId) : null;
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function motivoOptions(): array
    {
        return array_map(
            fn (SuspensaoMotivo $m) => ['value' => $m->value, 'label' => $m->label()],
            SuspensaoMotivo::cases(),
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'motivo' => ['required', 'string'],
            'data' => ['required', 'date'],
            'observacao' => ['nullable', 'string'],
            'enviarEmail' => ['boolean'],
        ];
    }

    public function confirmar(): void
    {
        $dados = $this->validate();
        $servico = $this->servico;

        app(ServicoStatusService::class)->suspender($servico, [
            'data' => $dados['data'],
            'motivo' => $dados['motivo'],
            'observacao' => $dados['observacao'],
            'enviou_email' => $dados['enviarEmail'],
        ]);

        if ($dados['enviarEmail']) {
            $this->enviarEmailTerminado($servico);
        }

        $this->dispatch(
            'toast',
            title: 'Serviço suspenso',
            body: $servico->nome.' — '.$servico->cliente->nome,
            tone: 'danger',
        );
        $this->dispatch('servico-actualizado', servicoId: $servico->id);

        $this->show = false;
    }

    /**
     * Sends the "Serviço terminado" email synchronously — this is a direct
     * admin action taken right now, not part of the daily job, so there's
     * no need for the transactional dedup machinery VerificacaoDiariaService
     * uses for its own reminders. A mail-send failure is logged and does not
     * block the suspension itself, which already happened.
     */
    private function enviarEmailTerminado(Servico $servico): void
    {
        $suspensao = $servico->suspensoes()->latest('id')->first();

        if (! $suspensao || ! $servico->cliente?->email) {
            return;
        }

        try {
            Mail::to($servico->cliente->email)
                ->bcc(Configuracao::emailBccAdmin($servico->cliente->email))
                ->send(new ServicoTerminadoMail($servico, $suspensao));

            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Email de serviço terminado enviado',
                'description' => null,
                'kind' => HistoricoKind::Email,
            ]);
        } catch (\Throwable $e) {
            Log::error(sprintf(
                'Falha ao enviar email de serviço terminado do serviço #%d (%s): %s',
                $servico->id,
                $servico->nome,
                $e->getMessage(),
            ));
        }
    }

    public function render()
    {
        return view('livewire.servicos.suspender-dialog');
    }
}
