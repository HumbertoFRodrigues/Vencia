<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Livewire\Pessoal\Concerns\NavegaMeses;
use App\Models\FixoPessoal;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Resumo pessoal')]
class Resumo extends Component
{
    use NavegaMeses;

    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();
    }

    private function financas(): FinancasPessoais
    {
        return app(FinancasPessoais::class);
    }

    #[Computed]
    public function resumo(): array
    {
        return $this->financas()->resumoMes($this->mesActual());
    }

    #[Computed]
    public function fixos()
    {
        return $this->financas()->fixosDoMes($this->mesActual());
    }

    #[Computed]
    public function orcamentos(): array
    {
        return $this->financas()->orcamentos($this->mesActual());
    }

    #[Computed]
    public function gastosPorCategoria(): array
    {
        return $this->financas()->porCategoria($this->mesActual(), TipoMovimento::Saida);
    }

    #[Computed]
    public function insights(): array
    {
        return $this->financas()->insights($this->mesActual());
    }

    #[Computed]
    public function ultimos()
    {
        return MovimentoPessoal::query()->contabilizados()->noMes($this->mesActual())
            ->with('categoria')
            ->orderByDesc('data')->orderByDesc('id')
            ->limit(8)->get();
    }

    public function confirmarFixo(int $fixoId): void
    {
        $fixo = FixoPessoal::findOrFail($fixoId);
        $this->financas()->confirmarFixo($fixo, $this->mesActual());

        $this->dispatch('toast',
            title: $fixo->tipo === TipoMovimento::Entrada ? 'Recebimento confirmado' : 'Pagamento confirmado',
            body: $fixo->descricao.' — '.FinancasPessoais::mzn((float) $fixo->valor),
            tone: 'success',
        );
        $this->actualizar();
    }

    public function ignorarFixo(int $fixoId): void
    {
        $fixo = FixoPessoal::findOrFail($fixoId);
        $this->financas()->ignorarFixo($fixo, $this->mesActual());

        $this->dispatch('toast', title: 'Ignorado este mês', body: $fixo->descricao.' não conta para '.$this->rotuloMes().'.', tone: 'success');
        $this->actualizar();
    }

    public function reporFixo(int $fixoId): void
    {
        $fixo = FixoPessoal::findOrFail($fixoId);
        $this->financas()->reporFixo($fixo, $this->mesActual());

        $this->dispatch('toast', title: 'Voltou a pendente', body: $fixo->descricao, tone: 'success');
        $this->actualizar();
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->resumo, $this->fixos, $this->orcamentos, $this->gastosPorCategoria, $this->insights, $this->ultimos);
    }

    public function render()
    {
        return view('livewire.pessoal.resumo');
    }
}
