<?php

namespace App\Livewire\Conta;

use App\Contracts\CobrancaImediata;
use App\Models\Fatura;
use App\Models\Plano as PlanoModel;
use App\Services\Faturacao;
use App\Services\Limites;
use App\Support\ContaActual;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * The dono's plan page: current plan and usage, choosing Pro (creates a
 * fatura with the payment instructions) and sending the payment reference
 * or proof for the super-admin to confirm.
 */
#[Layout('layouts.app')]
#[Title('Plano')]
class Plano extends Component
{
    use WithFileUploads;

    public string $ciclo = 'mensal';

    public string $metodo = 'mpesa';

    public string $referencia = '';

    /** @var TemporaryUploadedFile|null */
    public $comprovativo = null;

    public function mount(): void
    {
        $this->authorize('gerir-conta');
    }

    #[Computed]
    public function conta()
    {
        return ContaActual::get()->load('plano');
    }

    #[Computed]
    public function planos(): Collection
    {
        return PlanoModel::query()->where('activo', true)->orderBy('ordem')->get();
    }

    #[Computed]
    public function uso(): array
    {
        $limites = app(Limites::class);

        return collect(PlanoModel::LIMITES)->map(fn (string $nome, string $chave) => [
            'nome' => $nome,
            'usado' => $limites->uso($chave),
            'limite' => $limites->plano()->limite($chave),
        ])->all();
    }

    #[Computed]
    public function faturaAberta(): ?Fatura
    {
        return $this->conta->faturas()->whereIn('estado', [Fatura::PENDENTE, Fatura::EM_ANALISE])->latest('id')->with('plano')->first();
    }

    #[Computed]
    public function faturas(): Collection
    {
        return $this->conta->faturas()->with('plano')->latest('id')->limit(12)->get();
    }

    public function setCiclo(string $ciclo): void
    {
        $this->ciclo = array_key_exists($ciclo, Faturacao::CICLOS) ? $ciclo : 'mensal';
    }

    public function escolher(int $planoId): void
    {
        $this->authorize('gerir-conta');
        $plano = PlanoModel::query()->where('activo', true)->findOrFail($planoId);

        try {
            app(Faturacao::class)->pedir($this->conta, $plano, $this->ciclo, auth()->user());
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não foi possível', body: $e->getMessage(), tone: 'danger');

            return;
        }

        unset($this->faturaAberta, $this->faturas);
        $this->dispatch('toast', title: 'Pedido criado', body: 'Faz o pagamento e envia a referência abaixo.', tone: 'success');
    }

    public function enviar(): void
    {
        $this->authorize('gerir-conta');
        $fatura = $this->faturaAberta;

        if (! $fatura || $fatura->estado !== Fatura::PENDENTE) {
            return;
        }

        $this->validate([
            'metodo' => ['required', 'in:mpesa,emola,transferencia,outro'],
            'referencia' => ['nullable', 'string', 'max:120'],
            'comprovativo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ], [], ['referencia' => 'referência', 'comprovativo' => 'comprovativo']);

        try {
            app(Faturacao::class)->enviarComprovativo($fatura, $this->metodo, $this->referencia, $this->comprovativo);
        } catch (RuntimeException $e) {
            $this->addError('referencia', $e->getMessage());

            return;
        }

        $this->reset('referencia', 'comprovativo');
        unset($this->faturaAberta, $this->faturas);
        $this->dispatch('toast', title: 'Enviado para confirmação', body: 'Recebes um email quando o pagamento for confirmado (normalmente no mesmo dia útil).', tone: 'success');
    }

    public string $telefone = '';

    /** "Pagar já com M-Pesa": the PIN prompt goes to the customer's phone; the plan turns on when confirmed. */
    public function pagarMpesa(): void
    {
        $this->authorize('gerir-conta');
        $gateway = app(Faturacao::class)->gateway();
        $fatura = $this->faturaAberta;

        if (! $gateway instanceof CobrancaImediata || ! $fatura) {
            return;
        }

        $this->validate(['telefone' => ['required', 'string', 'max:20']], [], ['telefone' => 'número M-Pesa']);

        $resultado = $gateway->cobrar($fatura, $this->telefone);

        if (! $resultado['ok']) {
            $this->addError('telefone', $resultado['mensagem']);

            return;
        }

        $this->reset('telefone');
        unset($this->faturaAberta, $this->faturas, $this->conta);
        $this->dispatch('toast', title: 'Pagamento confirmado', body: 'O plano já está activo. Recebes o recibo por email.', tone: 'success');
    }

    public function cancelar(): void
    {
        $this->authorize('gerir-conta');
        $fatura = $this->faturaAberta;

        if ($fatura && $fatura->estado === Fatura::PENDENTE) {
            $fatura->update(['estado' => Fatura::CANCELADA]);
        }

        unset($this->faturaAberta, $this->faturas);
    }

    public function render()
    {
        return view('livewire.conta.plano', [
            'faturacao' => app(Faturacao::class),
        ]);
    }
}
