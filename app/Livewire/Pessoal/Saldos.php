<?php

namespace App\Livewire\Pessoal;

use App\Models\MetaPessoal;
use App\Models\MetodoPagamentoOpcao;
use App\Services\FinancasPessoais;
use App\Services\SaldosEMetas;
use App\Support\Valor;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Saldos e metas')]
class Saldos extends Component
{
    // "Acertar saldo" (inline, one account at a time)
    #[Locked]
    public ?string $acertando = null;

    public string $valorActual = '';

    // Goal form
    public bool $metaAberta = false;

    #[Locked]
    public ?int $metaId = null;

    public string $metaNome = '';

    public string $metaValor = '';

    public string $metaData = '';

    // Contribution form
    public bool $contribuicaoAberta = false;

    #[Locked]
    public ?int $contribuicaoMetaId = null;

    public string $contribuicaoValor = '';

    public string $contribuicaoData = '';

    public string $contribuicaoMeio = '';

    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();
    }

    private function servico(): SaldosEMetas
    {
        return app(SaldosEMetas::class);
    }

    #[Computed]
    public function saldos(): array
    {
        return $this->servico()->saldos();
    }

    #[Computed]
    public function semMeio(): float
    {
        return $this->servico()->semMeio();
    }

    #[Computed]
    public function metas(): array
    {
        return $this->servico()->metas();
    }

    #[Computed]
    public function meios(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
    }

    // ---------- Acertar saldo ----------

    public function acertar(string $meio): void
    {
        $linha = collect($this->saldos)->firstWhere('meio', $meio);
        if (! $linha) {
            return;
        }

        $this->resetErrorBag();
        $this->acertando = $meio;
        $this->valorActual = number_format($linha['saldo'], 2, '.', '');
    }

    public function guardarAcerto(): void
    {
        if ($this->acertando === null) {
            return;
        }

        $this->valorActual = Valor::normalizar($this->valorActual);
        $this->validate(['valorActual' => ['required', 'numeric', 'between:-9999999999,9999999999']], [
            'valorActual.required' => 'Indica quanto tens agora nesta conta.',
            'valorActual.numeric' => 'Usa só números (ex: 12.500 ou -300).',
        ]);

        $this->servico()->acertarSaldo($this->acertando, (float) $this->valorActual);
        $this->dispatch('toast', title: 'Saldo acertado', body: FinancasPessoais::mzn((float) $this->valorActual), tone: 'success');
        $this->reset(['acertando', 'valorActual']);
        $this->actualizar();
    }

    public function cancelarAcerto(): void
    {
        $this->reset(['acertando', 'valorActual']);
    }

    // ---------- Metas ----------

    public function abrirMeta(?int $metaId = null): void
    {
        $this->resetErrorBag();
        $this->reset(['metaId', 'metaNome', 'metaValor', 'metaData']);

        if ($metaId !== null) {
            $m = MetaPessoal::findOrFail($metaId);
            $this->metaId = $m->id;
            $this->metaNome = $m->nome;
            $this->metaValor = (string) (float) $m->valor_alvo;
            $this->metaData = $m->data_alvo?->format('Y-m') ?? '';
        }

        $this->metaAberta = true;
    }

    public function guardarMeta(): void
    {
        $this->metaValor = Valor::normalizar($this->metaValor);
        $dados = $this->validate([
            'metaNome' => ['required', 'string', 'max:120'],
            'metaValor' => ['required', 'numeric', 'min:1', 'max:9999999999'],
            'metaData' => ['nullable', 'date_format:Y-m'],
        ], [
            'metaNome.required' => 'Dá um nome à meta (ex: Fundo de emergência).',
            'metaValor.required' => 'Indica quanto queres juntar.',
            'metaValor.numeric' => 'Usa só números (ex: 100.000).',
        ]);

        $atributos = [
            'nome' => trim($dados['metaNome']),
            'valor_alvo' => $dados['metaValor'],
            'data_alvo' => $dados['metaData'] ? FinancasPessoais::mesDe($dados['metaData'])->endOfMonth()->toDateString() : null,
        ];

        if ($this->metaId) {
            $meta = MetaPessoal::findOrFail($this->metaId);
            $meta->update($atributos);
            // Raising the target re-opens a completed goal.
            if ($meta->concluida_em && (float) $meta->contribuicoes()->sum('valor') < (float) $meta->valor_alvo) {
                $meta->update(['concluida_em' => null]);
            }
        } else {
            MetaPessoal::create($atributos);
        }

        $this->dispatch('toast', title: $this->metaId ? 'Meta actualizada' : 'Meta criada', body: $atributos['nome'], tone: 'success');
        $this->metaAberta = false;
        $this->actualizar();
    }

    /** Contributions stay as savings saídas in the history; only the goal goes away. */
    public function apagarMeta(): void
    {
        if (! $this->metaId) {
            return;
        }

        $meta = MetaPessoal::findOrFail($this->metaId);
        $meta->delete();

        $this->dispatch('toast', title: 'Meta removida', body: $meta->nome, tone: 'success');
        $this->metaAberta = false;
        $this->actualizar();
    }

    // ---------- Contribuições ----------

    public function abrirContribuicao(int $metaId): void
    {
        $meta = MetaPessoal::findOrFail($metaId);
        $linha = collect($this->metas)->first(fn ($l) => $l['meta']->id === $meta->id);

        $this->resetErrorBag();
        $this->contribuicaoMetaId = $meta->id;
        $this->contribuicaoValor = $linha && $linha['porMes'] ? (string) round($linha['porMes']) : '';
        $this->contribuicaoData = Carbon::today()->toDateString();
        $this->contribuicaoMeio = '';
        $this->contribuicaoAberta = true;
    }

    public function guardarContribuicao(): void
    {
        $this->contribuicaoValor = Valor::normalizar($this->contribuicaoValor);
        $dados = $this->validate([
            'contribuicaoValor' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'contribuicaoData' => ['required', 'date_format:Y-m-d'],
            'contribuicaoMeio' => ['nullable', Rule::in($this->meios)],
        ], [
            'contribuicaoValor.required' => 'Indica quanto guardaste.',
            'contribuicaoValor.numeric' => 'Usa só números (ex: 5.000).',
        ]);

        $meta = MetaPessoal::findOrFail($this->contribuicaoMetaId);
        $this->servico()->contribuir($meta, (float) $dados['contribuicaoValor'], Carbon::createFromFormat('!Y-m-d', $dados['contribuicaoData']), $dados['contribuicaoMeio'] ?: null);

        $meta->refresh();
        $this->dispatch('toast',
            title: $meta->concluida_em ? 'Meta atingida!' : 'Guardado na meta',
            body: FinancasPessoais::mzn((float) $dados['contribuicaoValor']).' — '.$meta->nome,
            tone: 'success',
        );
        $this->contribuicaoAberta = false;
        $this->actualizar();
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->saldos, $this->semMeio, $this->metas);
    }

    public function render()
    {
        return view('livewire.pessoal.saldos');
    }
}
