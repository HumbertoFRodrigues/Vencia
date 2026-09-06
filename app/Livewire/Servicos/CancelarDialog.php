<?php

namespace App\Livewire\Servicos;

use App\Models\Servico;
use App\Services\ServicoStatusService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * "Cancelar serviço" dialog — same nested-dialog family as SuspenderDialog
 * (only servicoId kept as state, servico re-fetched on abrir()), opened via
 * the browser event 'servico-cancelar:abrir' carrying servicoId. Unlike
 * Suspender, there is no "enviar email" checkbox: app/Mail only has
 * ServicoTerminadoMail (reused by Suspender) and LembreteVencimentoMail —
 * there is no cancellation template, and the brief is explicit that one
 * should not be invented here.
 *
 * This is presented as a terminal action (see the view's dialog description
 * and confirm button copy): the status machine has no arrow back from
 * cancelado, so unlike suspender() there is nothing analogous to a
 * "Renovar" that could undo it.
 */
class CancelarDialog extends Component
{
    public bool $show = false;

    public ?int $servicoId = null;

    public string $motivo = '';

    #[On('servico-cancelar:abrir')]
    public function abrir(int $servicoId): void
    {
        $this->resetValidation();
        $this->servicoId = $servicoId;
        $this->motivo = '';
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

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'max:500'],
        ];
    }

    public function confirmar(): void
    {
        $dados = $this->validate();
        $servico = $this->servico;

        app(ServicoStatusService::class)->cancelar($servico, [
            'motivo' => $dados['motivo'],
        ]);

        $this->dispatch(
            'toast',
            title: 'Serviço cancelado',
            body: $servico->nome.' — '.$servico->cliente->nome,
            tone: 'danger',
        );
        $this->dispatch('servico-actualizado', servicoId: $servico->id);

        $this->show = false;
    }

    public function render()
    {
        return view('livewire.servicos.cancelar-dialog');
    }
}
