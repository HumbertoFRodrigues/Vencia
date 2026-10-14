<?php

namespace App\Livewire\Configuracoes;

use App\Models\User;
use App\Support\Totp;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;

/** Configurações → Segurança: turn the two-step login on/off and manage recovery codes. */
class Seguranca extends Component
{
    public string $codigo = '';

    public string $password = '';

    /** Shown once, right after they are generated. */
    public array $codigosNovos = [];

    private function utilizador(): User
    {
        return auth()->user()->fresh();
    }

    #[Computed]
    public function estado(): string
    {
        $u = $this->utilizador();

        return match (true) {
            $u->temDoisPassos() => 'activo',
            $u->dois_passos_segredo !== null => 'a_configurar',
            default => 'desligado',
        };
    }

    #[Computed]
    public function qr(): ?string
    {
        $u = $this->utilizador();

        return $this->estado === 'a_configurar' ? Totp::qrSvg(Totp::uri($u->dois_passos_segredo, $u->email)) : null;
    }

    #[Computed]
    public function segredo(): ?string
    {
        return $this->estado === 'a_configurar' ? trim(chunk_split($this->utilizador()->dois_passos_segredo, 4, ' ')) : null;
    }

    #[Computed]
    public function codigosRestantes(): int
    {
        return count($this->utilizador()->dois_passos_recuperacao ?? []);
    }

    public function iniciar(): void
    {
        $this->utilizador()->forceFill([
            'dois_passos_segredo' => Totp::gerarSegredo(),
            'dois_passos_confirmado_em' => null,
            'dois_passos_recuperacao' => null,
            'dois_passos_ultimo_passo' => null,
        ])->save();

        $this->reset(['codigo', 'codigosNovos']);
        $this->limpar();
    }

    public function confirmar(): void
    {
        $this->resetErrorBag();
        $u = $this->utilizador();
        $passo = $u->dois_passos_segredo ? Totp::verificar($u->dois_passos_segredo, $this->codigo) : null;

        if ($passo === null) {
            $this->addError('codigo', 'O código não confere. Confirma que leste o QR desta página e escreve o código que está a aparecer agora.');

            return;
        }

        $this->codigosNovos = User::gerarCodigosRecuperacao();
        $u->forceFill([
            'dois_passos_confirmado_em' => now(),
            'dois_passos_ultimo_passo' => $passo,
            'dois_passos_recuperacao' => $this->codigosNovos,
        ])->save();

        $this->reset('codigo');
        $this->limpar();
        $this->dispatch('toast', title: 'Verificação em dois passos activada', body: 'Guarda os códigos de recuperação num sítio seguro.', tone: 'success');
    }

    public function cancelar(): void
    {
        if ($this->estado === 'a_configurar') {
            $this->utilizador()->forceFill(['dois_passos_segredo' => null])->save();
        }

        $this->limpar();
    }

    public function novosCodigos(): void
    {
        $this->resetErrorBag();
        if (! $this->passwordCerta()) {
            return;
        }

        $this->codigosNovos = User::gerarCodigosRecuperacao();
        $this->utilizador()->forceFill(['dois_passos_recuperacao' => $this->codigosNovos])->save();
        $this->reset('password');
        $this->limpar();
    }

    public function desactivar(): void
    {
        $this->resetErrorBag();
        if (! $this->passwordCerta()) {
            return;
        }

        $this->utilizador()->forceFill([
            'dois_passos_segredo' => null,
            'dois_passos_recuperacao' => null,
            'dois_passos_confirmado_em' => null,
            'dois_passos_ultimo_passo' => null,
        ])->save();

        $this->reset(['password', 'codigosNovos']);
        $this->limpar();
        $this->dispatch('toast', title: 'Verificação em dois passos desligada', body: 'O login volta a pedir só a password.', tone: 'success');
    }

    private function passwordCerta(): bool
    {
        if (Hash::check($this->password, $this->utilizador()->password)) {
            return true;
        }

        $this->addError('password', 'Password incorrecta.');

        return false;
    }

    private function limpar(): void
    {
        unset($this->estado, $this->qr, $this->segredo, $this->codigosRestantes);
    }

    public function render()
    {
        return view('livewire.configuracoes.seguranca');
    }
}
