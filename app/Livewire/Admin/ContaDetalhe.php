<?php

namespace App\Livewire\Admin;

use App\Models\ActividadeAdmin;
use App\Models\Cliente;
use App\Models\Conta;
use App\Models\Historico;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Models\Retirada;
use App\Models\Servico;
use App\Services\Faturacao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * One conta seen by the super-admin: members, usage and invoices, plus the
 * manual levers — set the plan by hand, suspend/reactivate, delete.
 */
#[Layout('layouts.app')]
class ContaDetalhe extends Component
{
    #[Locked]
    public int $contaId;

    public string $planoId = '';

    public string $planoAte = '';

    public string $motivo = '';

    public string $confirmarNome = '';

    public function mount(Conta $conta): void
    {
        $this->contaId = $conta->id;
        $this->planoId = (string) $conta->plano_id;
        $this->planoAte = $conta->plano_ate?->toDateString() ?? '';
    }

    public function conta(): Conta
    {
        return Conta::query()->with('plano')->withCount(['clientes', 'servicos', 'pagamentos'])->findOrFail($this->contaId);
    }

    public function guardarPlano(): void
    {
        $dados = $this->validate([
            'planoId' => ['required', 'exists:planos,id'],
            'planoAte' => ['nullable', 'date'],
        ], [], ['planoId' => 'plano', 'planoAte' => 'data de fim']);

        $conta = $this->conta();
        $plano = Plano::query()->findOrFail($dados['planoId']);
        $ate = $dados['planoAte'] ? Carbon::parse($dados['planoAte']) : null;

        $conta->forceFill([
            'plano_id' => $plano->id,
            'plano_ate' => $plano->gratuito() ? null : $ate?->toDateString(),
            'em_teste' => false,
            'avisos_plano' => null,
        ])->save();

        ActividadeAdmin::registar('conta.plano', 'conta:'.$conta->id, $plano->nome.($ate && ! $plano->gratuito() ? ' até '.$ate->format('d/m/Y') : ' sem data de fim'));
        $this->dispatch('toast', title: 'Plano actualizado', body: $conta->nome.' — '.$plano->nome, tone: 'success');
    }

    public function suspender(): void
    {
        $this->validate(['motivo' => ['required', 'string', 'max:300']], [], ['motivo' => 'motivo']);
        $conta = $this->conta();
        $conta->update(['estado' => Conta::SUSPENSA, 'motivo_suspensao' => $this->motivo]);
        ActividadeAdmin::registar('conta.suspensa', 'conta:'.$conta->id, $this->motivo);
        $this->reset('motivo');
        $this->dispatch('toast', title: 'Conta suspensa', body: $conta->nome, tone: 'success');
    }

    public function reactivar(): void
    {
        $conta = $this->conta();
        $conta->update(['estado' => Conta::ACTIVA, 'motivo_suspensao' => null]);
        ActividadeAdmin::registar('conta.reactivada', 'conta:'.$conta->id);
        $this->dispatch('toast', title: 'Conta reactivada', body: $conta->nome, tone: 'success');
    }

    public function apagar(): void
    {
        $conta = $this->conta();

        if (trim($this->confirmarNome) !== $conta->nome) {
            $this->addError('confirmarNome', 'Escreve o nome exacto da conta para confirmar.');

            return;
        }

        if ($conta->membros()->where('super_admin', true)->exists() && $conta->membros()->count() === 1 && $conta->membros()->first()->is(auth()->user())) {
            $this->addError('confirmarNome', 'Esta é a tua própria conta. Não a apagues por aqui.');

            return;
        }

        // Payments are protected against deletion (restrictOnDelete); remove the business rows in order first.
        DB::transaction(function () use ($conta): void {
            Pagamento::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            Retirada::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            Historico::withoutGlobalScope('conta')->where('conta_id', $conta->id)->toBase()->delete();
            Servico::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            Cliente::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            $conta->delete();
        });

        ActividadeAdmin::registar('conta.apagada', 'conta:'.$conta->id, $conta->nome);
        session()->flash('toast', ['title' => 'Conta apagada', 'body' => $conta->nome]);
        $this->redirectRoute('admin.contas', navigate: true);
    }

    public function render()
    {
        $conta = $this->conta();

        return view('livewire.admin.conta-detalhe', [
            'conta' => $conta,
            'membros' => $conta->membros()->orderBy('name')->get(),
            'faturas' => $conta->faturas()->with('plano')->latest('id')->limit(20)->get(),
            'planos' => Plano::query()->orderBy('ordem')->get(),
            'faturacao' => app(Faturacao::class),
        ])->title($conta->nome);
    }
}
