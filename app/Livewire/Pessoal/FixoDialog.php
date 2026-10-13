<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\FixoPessoal;
use App\Models\MetodoPagamentoOpcao;
use App\Services\FinancasPessoais;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FixoDialog extends Component
{
    public bool $show = false;

    public ?int $fixoId = null;

    public string $tipo = 'saida';

    public string $descricao = '';

    public string $categoriaId = '';

    public string $valor = '';

    public string $diaMes = '1';

    public string $meio = '';

    public string $inicio = '';

    public bool $activo = true;

    public string $notas = '';

    #[On('fixo:abrir')]
    public function abrir(?string $tipo = null, ?int $fixoId = null): void
    {
        $this->resetValidation();
        $this->reset(['fixoId', 'descricao', 'categoriaId', 'valor', 'meio', 'notas']);
        $this->tipo = $tipo === 'entrada' ? 'entrada' : 'saida';
        $this->diaMes = (string) Carbon::today()->day;
        $this->inicio = Carbon::today()->format('Y-m');
        $this->activo = true;

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
            $this->activo = $f->activo;
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

    protected function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['entrada', 'saida'])],
            'descricao' => ['required', 'string', 'max:160'],
            'categoriaId' => ['required', Rule::exists('categorias_pessoais', 'id')->where('user_id', auth()->id())->where('tipo', $this->tipo)],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'diaMes' => ['required', 'integer', 'between:1,31'],
            'meio' => ['nullable', 'string', 'max:60'],
            'inicio' => ['required', 'date_format:Y-m'],
            'activo' => ['boolean'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'descricao.required' => 'Dá um nome a este fixo (ex: Renda de casa, Salário).',
            'valor.required' => 'Indica o valor mensal.',
            'valor.min' => 'O valor tem de ser maior que zero.',
            'valor.numeric' => 'Usa só números (ex: 15000).',
            'inicio.required' => 'Indica a partir de que mês conta.',
        ];
    }

    public function guardar(): void
    {
        $this->valor = str_replace([' ', ','], ['', '.'], $this->valor);
        $dados = $this->validate();

        $atributos = [
            'tipo' => $dados['tipo'],
            'descricao' => trim($dados['descricao']),
            'categoria_pessoal_id' => (int) $dados['categoriaId'],
            'valor' => $dados['valor'],
            'dia_mes' => (int) $dados['diaMes'],
            'meio' => $dados['meio'] ?: null,
            'inicio' => FinancasPessoais::mesDe($dados['inicio'])->toDateString(),
            'activo' => $this->fixoId === null ? true : (bool) $dados['activo'],
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
        $this->show = false;
    }

    /** Confirmed months stay in the history as normal movimentos; only the recurrence goes away. */
    public function apagar(): void
    {
        if ($this->fixoId === null) {
            return;
        }

        $f = FixoPessoal::findOrFail($this->fixoId);
        $f->delete();

        $this->dispatch('toast', title: 'Fixo removido', body: $f->descricao.' — os meses já confirmados continuam no histórico.', tone: 'success');
        $this->dispatch('pessoal-actualizado');
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.pessoal.fixo-dialog');
    }
}
