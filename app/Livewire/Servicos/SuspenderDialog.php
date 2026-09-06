<?php

namespace App\Livewire\Servicos;

use App\Enums\SuspensaoMotivo;
use App\Models\Servico;
use App\Services\ServicoStatusService;
use Illuminate\Support\Carbon;
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

        $this->dispatch(
            'toast',
            title: 'Serviço suspenso',
            body: $servico->nome.' — '.$servico->cliente->nome,
            tone: 'danger',
        );
        $this->dispatch('servico-actualizado', servicoId: $servico->id);

        $this->show = false;
    }

    public function render()
    {
        return view('livewire.servicos.suspender-dialog');
    }
}
