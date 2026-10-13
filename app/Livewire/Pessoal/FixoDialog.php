<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\FixoPessoal;
use App\Models\MetodoPagamentoOpcao;
use App\Services\FinancasPessoais;
use App\Support\Valor;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class FixoDialog extends Component
{
    public bool $show = false;

    #[Locked]
    public ?int $fixoId = null;

    #[Locked]
    public string $tipo = 'saida';

    public string $descricao = '';

    public string $categoriaId = '';

    public string $valor = '';

    public string $diaMes = '1';

    public string $meio = '';

    public string $inicio = '';

    public string $fim = '';

    public string $notas = '';

    #[On('fixo:abrir')]
    public function abrir(?string $tipo = null, ?int $fixoId = null): void
    {
        $this->resetValidation();
        $this->reset(['fixoId', 'descricao', 'categoriaId', 'valor', 'meio', 'fim', 'notas']);
        $this->tipo = $tipo === 'entrada' ? 'entrada' : 'saida';
        $this->diaMes = (string) Carbon::today()->day;
        $this->inicio = Carbon::today()->format('Y-m');

        if ($fixoId !== null) {
            $f = FixoPessoal::findOrFail($fixoId);
            $this->fixoId = $f->id;
            $this->tipo = $f->tipo->value;
            $this->descricao = $f->descricao;
            $this->categoriaId = (string) $f->categoria_pessoal_id;
            $this->valor = (string) $f->valor;
            $this->diaMes = (string) $f->dia_mes;
            $this->meio = (string) $f->meio;
            $this->inicio = $f->inicio->format('Y-m');
            $this->fim = $f->fim?->format('Y-m') ?? '';
            $this->notas = (string) $f->notas;
        } else {
            $this->categoriaId = $this->categoriaOptions[0]['value'] ?? '';
        }

        unset($this->categoriaOptions);
        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    public function setTipo(string $tipo): void
    {
        if ($this->fixoId !== null || $this->tipo === $tipo) {
            return;
        }

        $this->tipo = $tipo === 'entrada' ? 'entrada' : 'saida';
        unset($this->categoriaOptions);
        $this->categoriaId = $this->categoriaOptions[0]['value'] ?? '';
    }

    /** Shortcut: this month is the last one (stops it from next month on, keeps everything before). */
    public function terminarEsteMes(): void
    {
        $this->fim = Carbon::today()->format('Y-m');
    }

    #[Computed]
    public function categoriaOptions(): array
    {
        return CategoriaPessoal::opcoes(TipoMovimento::from($this->tipo), $this->categoriaId !== '' ? (int) $this->categoriaId : null);
    }

    #[Computed]
    public function meios(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function diasOptions(): array
    {
        return collect(range(1, 31))->map(fn ($d) => ['value' => (string) $d, 'label' => 'Dia '.$d])->all();
    }

    #[Computed]
    public function temHistorico(): bool
    {
        return $this->fixoId !== null && FixoPessoal::findOrFail($this->fixoId)->movimentos()->where('ignorado', false)->exists();
    }

    protected function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['entrada', 'saida'])],
            'descricao' => ['required', 'string', 'max:160'],
            'categoriaId' => ['required', Rule::exists('categorias_pessoais', 'id')->where('user_id', auth()->id())->where('tipo', $this->tipo)],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'diaMes' => ['required', 'integer', 'between:1,31'],
            'meio' => ['nullable', 'string', 'max:255'],
            'inicio' => ['required', 'date_format:Y-m'],
            'fim' => ['nullable', 'date_format:Y-m', 'after_or_equal:inicio'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'descricao.required' => 'Dá um nome a este fixo (ex: Renda de casa, Salário).',
            'categoriaId.required' => 'Escolhe uma categoria.',
            'valor.required' => 'Indica o valor mensal.',
            'valor.min' => 'O valor tem de ser maior que zero.',
            'valor.numeric' => 'Usa só números (ex: 15000).',
            'inicio.required' => 'Indica a partir de que mês conta.',
            'fim.after_or_equal' => 'O último mês não pode ser antes do primeiro.',
        ];
    }

    public function guardar(): void
    {
        $this->valor = Valor::normalizar($this->valor);
        $dados = $this->validate();

        $atributos = [
            'tipo' => $dados['tipo'],
            'descricao' => trim($dados['descricao']),
            'categoria_pessoal_id' => (int) $dados['categoriaId'],
            'valor' => $dados['valor'],
            'dia_mes' => (int) $dados['diaMes'],
            'meio' => $dados['meio'] ?: null,
            'inicio' => FinancasPessoais::mesDe($dados['inicio'])->toDateString(),
            'fim' => $dados['fim'] ? FinancasPessoais::mesDe($dados['fim'])->toDateString() : null,
            'notas' => $dados['notas'] ?: null,
        ];

        if ($this->fixoId !== null) {
            FixoPessoal::findOrFail($this->fixoId)->update($atributos);
            $titulo = 'Fixo actualizado';
        } else {
            FixoPessoal::create($atributos);
            $titulo = ($dados['tipo'] === 'entrada' ? 'Entrada' : 'Débito').' fixo criado';
        }

        $this->dispatch('toast', title: $titulo, body: $atributos['descricao'].' — '.FinancasPessoais::mzn((float) $atributos['valor']).' / mês', tone: 'success');
        $this->dispatch('pessoal-actualizado');
        $this->dispatch('nav-badge', id: 'resumo', n: app(FinancasPessoais::class)->nAtrasoHoje());
        $this->show = false;
    }

    /**
     * Only a fixo with no confirmed month can be removed — otherwise its
     * history would turn into "dia-a-dia" spending. Those get an end month.
     */
    public function apagar(): void
    {
        if ($this->fixoId === null) {
            return;
        }

        $f = FixoPessoal::findOrFail($this->fixoId);

        if ($f->movimentos()->where('ignorado', false)->exists()) {
            $this->addError('fim', 'Este fixo já tem meses confirmados, por isso não pode ser apagado. Para deixar de o usar, define o último mês.');

            return;
        }

        $f->delete();

        $this->dispatch('toast', title: 'Fixo removido', body: $f->descricao, tone: 'success');
        $this->dispatch('pessoal-actualizado');
        $this->dispatch('nav-badge', id: 'resumo', n: app(FinancasPessoais::class)->nAtrasoHoje());
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.pessoal.fixo-dialog');
    }
}
