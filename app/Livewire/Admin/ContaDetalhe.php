<?php

namespace App\Livewire\Admin;

use App\Enums\Papel;
use App\Models\ActividadeAdmin;
use App\Models\Conta;
use App\Models\Plano;
use App\Models\User;
use App\Services\DadosDaConta;
use App\Services\Equipa as EquipaService;
use App\Services\Faturacao;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

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

    public string $nome = '';

    public string $novoMembroEmail = '';

    public string $novoMembroPapel = 'gestor';

    public function mount(Conta $conta): void
    {
        $this->contaId = $conta->id;
        $this->nome = $conta->nome;
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

    public function guardarNome(): void
    {
        $this->validate(['nome' => ['required', 'string', 'max:120']], [], ['nome' => 'nome']);
        $conta = $this->conta();
        $antes = $conta->nome;
        $conta->update(['nome' => trim($this->nome)]);
        ActividadeAdmin::registar('conta.nome', 'conta:'.$conta->id, $antes.' → '.$conta->nome);
        $this->dispatch('toast', title: 'Nome actualizado', body: $conta->nome, tone: 'success');
    }

    /**
     * Gives $dias of Pro (an offer, a delayed payment, a longer trial): from
     * today, or from the current end if it is still in the future.
     */
    public function oferecerDias(int $dias): void
    {
        abort_unless(in_array($dias, [7, 14, 30], true), 422);
        $conta = $this->conta();
        $pro = Plano::query()->where('slug', 'pro')->firstOrFail();

        $base = $conta->plano_id === $pro->id && $conta->plano_ate && $conta->plano_ate->gte(Carbon::today())
            ? $conta->plano_ate->copy()
            : Carbon::today();
        $ate = $base->addDays($dias);

        $conta->forceFill([
            'plano_id' => $pro->id,
            'plano_ate' => $ate->toDateString(),
            'em_teste' => $conta->em_teste && $conta->plano_id === $pro->id,
            'avisos_plano' => null,
        ])->save();

        $this->planoId = (string) $pro->id;
        $this->planoAte = $ate->toDateString();
        ActividadeAdmin::registar('conta.dias_oferecidos', 'conta:'.$conta->id, '+'.$dias.' dias de Pro, até '.$ate->format('d/m/Y'));
        $this->dispatch('toast', title: '+'.$dias.' dias de Pro', body: $conta->nome.' até '.$ate->format('d/m/Y'), tone: 'success');
    }

    public function mudarPapelMembro(int $userId, string $papel): void
    {
        $conta = $this->conta();
        $membro = $conta->membros()->findOrFail($userId);

        try {
            app(EquipaService::class)->mudarPapel($conta, $membro, Papel::from($papel));
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não foi possível', body: $e->getMessage(), tone: 'danger');

            return;
        }

        ActividadeAdmin::registar('conta.membro_papel', 'conta:'.$conta->id, $membro->email.' → '.Papel::from($papel)->label());
        $this->dispatch('toast', title: 'Papel actualizado', body: $membro->name.' — '.Papel::from($papel)->label(), tone: 'success');
    }

    public function removerMembro(int $userId): void
    {
        $conta = $this->conta();
        $membro = $conta->membros()->findOrFail($userId);

        try {
            app(EquipaService::class)->remover($conta, $membro);
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não foi possível', body: $e->getMessage(), tone: 'danger');

            return;
        }

        ActividadeAdmin::registar('conta.membro_removido', 'conta:'.$conta->id, $membro->email);
        $this->dispatch('toast', title: 'Removido da conta', body: $membro->name, tone: 'success');
    }

    /** Adds someone who already has a Vencia account (the admin may go past the plan's member limit). */
    public function adicionarMembro(): void
    {
        $this->validate([
            'novoMembroEmail' => ['required', 'email'],
            'novoMembroPapel' => ['required', Rule::enum(Papel::class)],
        ], [], ['novoMembroEmail' => 'email', 'novoMembroPapel' => 'papel']);

        $conta = $this->conta();
        $user = User::query()->where('email', mb_strtolower(trim($this->novoMembroEmail)))->first();

        if (! $user) {
            $this->addError('novoMembroEmail', 'Não existe nenhum utilizador com este email. Para alguém novo, o dono convida-o em Equipa.');

            return;
        }
        if ($conta->membros()->whereKey($user->id)->exists()) {
            $this->addError('novoMembroEmail', 'Esta pessoa já faz parte da conta.');

            return;
        }

        $conta->membros()->attach($user->id, ['papel' => $this->novoMembroPapel]);
        ActividadeAdmin::registar('conta.membro_adicionado', 'conta:'.$conta->id, $user->email.' ('.Papel::from($this->novoMembroPapel)->label().')');
        $this->reset('novoMembroEmail');
        $this->dispatch('toast', title: 'Adicionado à conta', body: $user->name, tone: 'success');
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

        app(DadosDaConta::class)->apagarConta($conta);

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
