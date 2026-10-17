<?php

namespace App\Livewire\Admin;

use App\Models\ActividadeAdmin;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    #[Locked]
    public ?int $editandoId = null;

    public string $editNome = '';

    public string $editEmail = '';

    public function editar(int $id): void
    {
        $user = $this->alvo($id);
        $this->resetErrorBag();
        $this->editandoId = $user->id;
        $this->editNome = $user->name;
        $this->editEmail = $user->email;
    }

    public function fecharEdicao(): void
    {
        $this->editandoId = null;
    }

    /** A typo in a name or email: fixed by the admin, the new email counts as confirmed. */
    public function guardarEdicao(): void
    {
        $user = $this->alvo((int) $this->editandoId);

        $this->validate([
            'editNome' => ['required', 'string', 'max:120'],
            'editEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], ['editEmail.unique' => 'Já existe outro utilizador com este email.'], ['editNome' => 'nome', 'editEmail' => 'email']);

        $antes = $user->email;
        $user->forceFill(['name' => trim($this->editNome), 'email' => mb_strtolower(trim($this->editEmail))]);
        if ($user->isDirty('email')) {
            $user->email_verified_at = Carbon::now();
        }
        $user->save();

        ActividadeAdmin::registar('user.editado', 'user:'.$user->id, $antes === $user->email ? $user->name : $antes.' → '.$user->email);
        $this->editandoId = null;
        $this->dispatch('toast', title: 'Utilizador actualizado', body: $user->email, tone: 'success');
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
