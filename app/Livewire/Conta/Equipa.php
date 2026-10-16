<?php

namespace App\Livewire\Conta;

use App\Enums\Papel;
use App\Models\Convite;
use App\Services\Equipa as EquipaService;
use App\Services\Limites;
use App\Support\ContaActual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;
use Throwable;

/** The dono's team page: members and their roles, pending invites, inviting someone new. */
#[Layout('layouts.app')]
#[Title('Equipa')]
class Equipa extends Component
{
    public string $email = '';

    public string $papel = 'registos';

    public function mount(): void
    {
        $this->authorize('gerir-conta');
    }

    #[Computed]
    public function membros(): Collection
    {
        return ContaActual::get()->membros()->orderBy('name')->get();
    }

    #[Computed]
    public function convites(): Collection
    {
        return Convite::query()->where('conta_id', ContaActual::id())->pendentes()->with('convidadoPor')->latest()->get();
    }

    #[Computed]
    public function restantes(): ?int
    {
        return app(Limites::class)->restante('membros');
    }

    public function convidar(): void
    {
        $this->authorize('gerir-conta');

        // Invites are emails from the platform: a handful per conta per day is plenty for a real team.
        $chave = 'convites:'.ContaActual::id();
        if (RateLimiter::tooManyAttempts($chave, 20)) {
            $this->addError('email', 'Enviaste muitos convites hoje. Tenta de novo amanhã.');

            return;
        }
        RateLimiter::hit($chave, 86400);

        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'papel' => ['required', Rule::enum(Papel::class)],
        ], [], ['email' => 'email', 'papel' => 'papel']);

        try {
            app(EquipaService::class)->convidar(ContaActual::get(), $this->email, Papel::from($this->papel), auth()->user());
        } catch (RuntimeException $e) {
            $this->addError('email', $e->getMessage());

            return;
        } catch (Throwable $e) {
            Log::error('Falha ao enviar convite: '.$e->getMessage());
            $this->dispatch('toast', title: 'Convite criado, mas o email falhou', body: 'Verifica o SMTP da plataforma. Podes reenviar daqui a pouco.', tone: 'warning');
            $this->reset('email');
            unset($this->convites, $this->restantes);

            return;
        }

        $this->dispatch('toast', title: 'Convite enviado', body: $this->email, tone: 'success');
        $this->reset('email');
        unset($this->convites, $this->restantes);
    }

    public function reenviar(int $conviteId): void
    {
        $this->authorize('gerir-conta');
        $convite = Convite::query()->where('conta_id', ContaActual::id())->findOrFail($conviteId);

        $this->email = $convite->email;
        $this->papel = $convite->papel->value;
        $this->convidar();
    }

    public function anularConvite(int $conviteId): void
    {
        $this->authorize('gerir-conta');
        Convite::query()->where('conta_id', ContaActual::id())->whereKey($conviteId)->delete();
        unset($this->convites, $this->restantes);
    }

    public function mudarPapel(int $userId, string $papel): void
    {
        $this->authorize('gerir-conta');
        $membro = ContaActual::get()->membros()->findOrFail($userId);

        try {
            app(EquipaService::class)->mudarPapel(ContaActual::get(), $membro, Papel::from($papel));
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não foi possível', body: $e->getMessage(), tone: 'danger');

            return;
        }

        unset($this->membros);
        $this->dispatch('toast', title: 'Papel actualizado', body: $membro->name.' — '.Papel::from($papel)->label(), tone: 'success');
    }

    public function remover(int $userId): void
    {
        $this->authorize('gerir-conta');
        $membro = ContaActual::get()->membros()->findOrFail($userId);

        try {
            app(EquipaService::class)->remover(ContaActual::get(), $membro);
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não foi possível', body: $e->getMessage(), tone: 'danger');

            return;
        }

        unset($this->membros, $this->restantes);
        $this->dispatch('toast', title: 'Removido da equipa', body: $membro->name, tone: 'success');

        if ($membro->is(auth()->user())) {
            $this->redirectRoute('dashboard');
        }
    }

    public function render()
    {
        return view('livewire.conta.equipa', ['papeis' => Papel::cases(), 'eu' => auth()->user()]);
    }
}
