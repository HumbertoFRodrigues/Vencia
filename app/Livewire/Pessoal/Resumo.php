<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Livewire\Pessoal\Concerns\NavegaMeses;
use App\Models\FixoPessoal;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use Illuminate\Support\Carbon;
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
    public function anteriores()
    {
        return $this->financas()->pendentesAnteriores($this->mesActual());
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

    /** $competencia (YYYY-MM) is given for an earlier month's fixo; otherwise the month on screen. */
    private function competencia(?string $competencia): Carbon
    {
        return $competencia ? FinancasPessoais::mesDe($competencia) : $this->mesActual();
    }

    public function confirmarFixo(int $fixoId, ?string $competencia = null): void
    {
        $fixo = FixoPessoal::findOrFail($fixoId);
        $this->financas()->confirmarFixo($fixo, $this->competencia($competencia));

        $this->dispatch('toast',
            title: $fixo->tipo === TipoMovimento::Entrada ? 'Recebimento confirmado' : 'Pagamento confirmado',
            body: $fixo->descricao.' — '.FinancasPessoais::mzn((float) $fixo->valor),
            tone: 'success',
        );
        $this->actualizar();
    }

    public function ignorarFixo(int $fixoId, ?string $competencia = null): void
    {
        $fixo = FixoPessoal::findOrFail($fixoId);
        $mes = $this->competencia($competencia);
        $this->financas()->ignorarFixo($fixo, $mes);

        $this->dispatch('toast', title: 'Ignorado nesse mês', body: $fixo->descricao.' não conta para '.ucfirst($mes->locale('pt_PT')->translatedFormat('F \d\e Y')).'.', tone: 'success');
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
        unset($this->resumo, $this->fixos, $this->anteriores, $this->orcamentos, $this->gastosPorCategoria, $this->insights, $this->ultimos);
        $this->dispatch('nav-badge', id: 'resumo', n: $this->financas()->nAtrasoHoje());
    }

    public function render()
    {
        return view('livewire.pessoal.resumo');
    }
}
