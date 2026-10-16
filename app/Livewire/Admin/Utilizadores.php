<?php

namespace App\Livewire\Admin;

use App\Models\ActividadeAdmin;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/** Every user: block/unblock, confirm or resend the email, send a reset link, grant super-admin. */
#[Layout('layouts.app')]
#[Title('Utilizadores')]
class Utilizadores extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function utilizadores()
    {
        return User::query()
            ->with('contas')
            ->when($this->q !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$this->q.'%')->orWhere('email', 'like', '%'.$this->q.'%')))
            ->latest('id')
            ->paginate(25);
    }

    private function alvo(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function bloquear(int $id): void
    {
        $user = $this->alvo($id);
        if ($user->is(auth()->user())) {
            return;
        }

        $bloquear = ! $user->bloqueado();
        $user->forceFill(['bloqueado_em' => $bloquear ? Carbon::now() : null])->save();
        ActividadeAdmin::registar($bloquear ? 'user.bloqueado' : 'user.desbloqueado', 'user:'.$user->id, $user->email);
        $this->dispatch('toast', title: $bloquear ? 'Utilizador bloqueado' : 'Utilizador desbloqueado', body: $user->email, tone: 'success');
    }

    public function confirmarEmail(int $id): void
    {
        $user = $this->alvo($id);
        $user->markEmailAsVerified();
        ActividadeAdmin::registar('user.email_confirmado', 'user:'.$user->id, $user->email);
        $this->dispatch('toast', title: 'Email marcado como confirmado', body: $user->email, tone: 'success');
    }

    public function reenviarConfirmacao(int $id): void
    {
        $user = $this->alvo($id);

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            Log::error('Falha ao reenviar confirmação: '.$e->getMessage());
            $this->dispatch('toast', title: 'Falha ao enviar', body: mb_strimwidth($e->getMessage(), 0, 140, '…'), tone: 'danger');

            return;
        }

        $this->dispatch('toast', title: 'Link de confirmação enviado', body: $user->email, tone: 'success');
    }

    public function enviarReposicao(int $id): void
    {
        $user = $this->alvo($id);

        try {
            $estado = Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable $e) {
            Log::error('Falha ao enviar reposição: '.$e->getMessage());
            $estado = null;
        }

        ActividadeAdmin::registar('user.reposicao_enviada', 'user:'.$user->id, $user->email);
        $this->dispatch('toast', title: $estado === Password::RESET_LINK_SENT ? 'Link de reposição enviado' : 'Não foi possível enviar agora', body: $user->email, tone: $estado === Password::RESET_LINK_SENT ? 'success' : 'warning');
    }

    public function alternarSuperAdmin(int $id): void
    {
        $user = $this->alvo($id);
        if ($user->is(auth()->user())) {
            $this->dispatch('toast', title: 'Não podes retirar o teu próprio acesso', tone: 'warning');

            return;
        }

        $user->forceFill(['super_admin' => ! $user->super_admin])->save();
        ActividadeAdmin::registar($user->super_admin ? 'user.super_admin' : 'user.sem_super_admin', 'user:'.$user->id, $user->email);
        $this->dispatch('toast', title: $user->super_admin ? 'Agora é super-admin' : 'Deixou de ser super-admin', body: $user->email, tone: 'success');
    }

    public function render()
    {
        return view('livewire.admin.utilizadores');
    }
}
