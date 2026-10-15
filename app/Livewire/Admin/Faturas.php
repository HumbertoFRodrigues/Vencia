<?php

namespace App\Livewire\Admin;

use App\Models\Fatura;
use App\Services\Faturacao;
use App\Support\Csv;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Plan invoices: the queue of payments to confirm (approve / reject) and the full history. */
#[Layout('layouts.app')]
#[Title('Faturação')]
class Faturas extends Component
{
    use WithPagination;

    #[Url]
    public string $estado = Fatura::EM_ANALISE;

    #[Locked]
    public ?int $rejeitarId = null;

    public string $motivo = '';

    public function setTab(string $estado): void
    {
        $this->estado = $estado;
        $this->resetPage();
    }

    #[Computed]
    public function contagens(): array
    {
        return Fatura::query()->selectRaw('estado, count(*) as n')->groupBy('estado')->pluck('n', 'estado')->all();
    }

    #[Computed]
    public function faturas()
    {
        return $this->consulta()->with('conta', 'plano', 'pedidaPor', 'aprovadaPor')->latest('id')->paginate(20);
    }

    private function consulta()
    {
        return Fatura::query()->when($this->estado !== 'todas', fn ($q) => $q->where('estado', $this->estado));
    }

    public function aprovar(int $id): void
    {
        $fatura = Fatura::query()->findOrFail($id);

        try {
            app(Faturacao::class)->aprovar($fatura, auth()->user());
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Não foi possível', body: $e->getMessage(), tone: 'danger');

            return;
        }

        unset($this->faturas, $this->contagens);
        $this->dispatch('toast', title: 'Pagamento confirmado', body: $fatura->conta->nome.' — '.$fatura->fresh()->plano->nome.' até '.$fatura->fresh()->periodo_fim->format('d/m/Y'), tone: 'success');
    }

    public function abrirRejeitar(int $id): void
    {
        $this->rejeitarId = $id;
        $this->motivo = '';
        $this->resetErrorBag();
    }

    public function fecharRejeitar(): void
    {
        $this->rejeitarId = null;
    }

    public function rejeitar(): void
    {
        $this->validate(['motivo' => ['required', 'string', 'max:300']], [], ['motivo' => 'motivo']);
        $fatura = Fatura::query()->findOrFail($this->rejeitarId);

        app(Faturacao::class)->rejeitar($fatura, auth()->user(), $this->motivo);

        $this->rejeitarId = null;
        unset($this->faturas, $this->contagens);
        $this->dispatch('toast', title: 'Pagamento rejeitado', body: 'O dono da conta foi avisado por email.', tone: 'success');
    }

    public function exportarCsv(): StreamedResponse
    {
        $faturacao = app(Faturacao::class);
        $linhas = $this->consulta()->with('conta', 'plano')->latest('id')->get()->map(fn (Fatura $f) => [
            $f->referenciaInterna(), $f->conta?->nome, $f->plano?->nome, $f->ciclo, number_format((float) $f->valor, 2, ',', ''),
            $faturacao->moeda(), $f->estadoLabel(), $f->metodo, $f->referencia, $f->created_at?->format('d/m/Y'), $f->paga_em?->format('d/m/Y'),
            $f->periodo_inicio?->format('d/m/Y'), $f->periodo_fim?->format('d/m/Y'),
        ]);

        return Csv::download('faturas_'.now()->format('Y-m-d_His').'.csv', [
            'Fatura', 'Conta', 'Plano', 'Ciclo', 'Valor', 'Moeda', 'Estado', 'Método', 'Referência', 'Pedida', 'Paga', 'Início', 'Fim',
        ], $linhas);
    }

    public function render()
    {
        return view('livewire.admin.faturas', ['faturacao' => app(Faturacao::class)]);
    }
}
