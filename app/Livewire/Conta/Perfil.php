<?php

namespace App\Livewire\Conta;

use App\Enums\Papel;
use App\Services\DadosDaConta;
use App\Services\Equipa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Every member's own page, whatever their role: name and email, password,
 * two-step sign-in, and the businesses they belong to (including leaving one).
 */
#[Layout('layouts.app')]
#[Title('O meu perfil')]
class Perfil extends Component
{
    public string $nome = '';

    public string $email = '';

    public string $passwordActual = '';

    public string $passwordNova = '';

    public string $passwordNova_confirmation = '';

    public string $confirmarApagar = '';

    public string $passwordApagar = '';

    public function mount(): void
    {
        $this->nome = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    #[Computed]
    public function contas(): Collection
    {
        return auth()->user()->contas()->with('plano')->orderBy('nome')->get();
    }

    public function guardarDados(): void
    {
        $user = auth()->user();

        $dados = $this->validate([
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'email.unique' => 'Já existe uma conta com este email.',
        ], ['nome' => 'nome']);

        $mudouEmail = strcasecmp($dados['email'], $user->email) !== 0;

        // A new address has to be confirmed again, and only by someone who knows the password.
        if ($mudouEmail && ! Hash::check($this->passwordActual, $user->password)) {
            $this->addError('passwordActual', 'Para mudar o email, escreve a tua palavra-passe actual.');

            return;
        }

        $user->forceFill(['name' => $dados['nome'], 'email' => $dados['email']]);
        if ($mudouEmail) {
            $user->email_verified_at = null;
        }
        $user->save();

        $this->passwordActual = '';

        if ($mudouEmail) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $e) {
                Log::error('Falha ao enviar a confirmação do novo email: '.$e->getMessage());
            }

            $this->redirectRoute('verification.notice');

            return;
        }

        $this->dispatch('toast', title: 'Perfil actualizado', tone: 'success');
    }

    public function mudarPassword(): void
    {
        $user = auth()->user();

        $this->validate([
            'passwordActual' => ['required', 'string'],
            'passwordNova' => ['required', 'confirmed', Password::min(8)],
        ], [], ['passwordActual' => 'palavra-passe actual', 'passwordNova' => 'nova palavra-passe']);

        if (! Hash::check($this->passwordActual, $user->password)) {
            $this->addError('passwordActual', 'A palavra-passe actual não está certa.');

            return;
        }

        $user->forceFill(['password' => $this->passwordNova])->save();

        // Other browsers/devices signed in with the old password are signed out.
        auth()->logoutOtherDevices($this->passwordNova);
        session()->put('password_hash_web', $user->getAuthPassword());

        $this->reset('passwordActual', 'passwordNova', 'passwordNova_confirmation');
        $this->dispatch('toast', title: 'Palavra-passe alterada', body: 'As outras sessões abertas foram terminadas.', tone: 'success');
    }

    public function exportarPessoal(): BinaryFileResponse
    {
        return response()->download(app(DadosDaConta::class)->zipPessoal(), 'vencia-o-meu-pessoal.zip')->deleteFileAfterSend();
    }

    /** The whole business, for its dono only. */
    public function exportarEmpresa(int $contaId): ?BinaryFileResponse
    {
        $conta = auth()->user()->contas()->wherePivot('papel', Papel::Dono->value)->find($contaId);
        abort_unless($conta, 403);

        return response()->download(app(DadosDaConta::class)->zipEmpresa($conta), 'vencia-'.Str::slug($conta->nome).'.zip')->deleteFileAfterSend();
    }

    /** What would happen if this account were deleted (shown before the button). */
    #[Computed]
    public function planoApagar(): array
    {
        return app(DadosDaConta::class)->planoParaApagar(auth()->user());
    }

    public function apagarConta(): void
    {
        $user = auth()->user();

        $this->validate(['confirmarApagar' => ['required']], [], ['confirmarApagar' => 'confirmação']);

        if (mb_strtoupper(trim($this->confirmarApagar)) !== 'APAGAR' || ! Hash::check($this->passwordApagar, $user->password)) {
            $this->addError('confirmarApagar', 'Escreve APAGAR e a tua palavra-passe actual para confirmar.');

            return;
        }

        $bloqueios = app(DadosDaConta::class)->planoParaApagar($user)['bloqueios'];
        if ($bloqueios !== []) {
            $this->addError('confirmarApagar', $bloqueios[0]);

            return;
        }

        // Sign out first: logging out saves the user (remember token), which after deleting would re-create it.
        auth()->guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        app(DadosDaConta::class)->apagarUtilizador($user);

        session()->flash('status', 'A tua conta foi apagada. Obrigado por teres usado a Vencia.');

        // There is no user left to draw this page for.
        $this->skipRender();
        $this->redirectRoute('login');
    }

    public function sair(int $contaId): void
    {
        $user = auth()->user();
        $conta = $user->contas()->findOrFail($contaId);

        try {
            app(Equipa::class)->remover($conta, $user);
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não é possível sair', body: $e->getMessage().' Em Equipa podes tornar outra pessoa dona.', tone: 'danger');

            return;
        }

        unset($this->contas);
        $this->dispatch('toast', title: 'Saíste da equipa', body: $conta->nome, tone: 'success');
        $this->redirectRoute('dashboard');
    }

    public function render()
    {
        return view('livewire.conta.perfil', ['papeis' => Papel::class, 'actual' => auth()->user()->conta_actual_id]);
    }
}
