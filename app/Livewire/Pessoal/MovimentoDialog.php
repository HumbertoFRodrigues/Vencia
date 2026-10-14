<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\FixoPessoal;
use App\Models\MetodoPagamentoOpcao;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use App\Support\Valor;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * One dialog, three jobs, chosen by what 'movimento:abrir' carries:
 * - nothing / a tipo  → new day-to-day entrada or saída
 * - movimentoId       → edit (or delete) an existing movimento
 * - fixoId+competencia → confirm a fixo for that month with an adjusted value/date
 */
class MovimentoDialog extends Component
{
    public bool $show = false;

    /** A withdrawal's entrada is managed from the business side only. */
    #[Locked]
    public bool $daEmpresa = false;

    #[Locked]
    public ?int $movimentoId = null;

    #[Locked]
    public ?int $fixoId = null;

    #[Locked]
    public ?string $competencia = null;

    #[Locked]
    public string $tipo = 'saida';

    public string $descricao = '';

    public string $categoriaId = '';

    public string $valor = '';

    public string $data = '';

    public string $meio = '';

    public string $notas = '';

    #[On('movimento:abrir')]
    public function abrir(?string $tipo = null, ?int $movimentoId = null, ?int $fixoId = null, ?string $competencia = null): void
    {
        $this->resetValidation();
        $this->reset(['daEmpresa', 'movimentoId', 'fixoId', 'competencia', 'descricao', 'categoriaId', 'valor', 'meio', 'notas']);
        $this->data = Carbon::today()->toDateString();
        $this->tipo = $tipo === 'entrada' ? 'entrada' : 'saida';

        if ($movimentoId !== null) {
            $m = MovimentoPessoal::findOrFail($movimentoId);
            $this->movimentoId = $m->id;
            $this->fixoId = $m->fixo_pessoal_id;
            $this->tipo = $m->tipo->value;
            $this->descricao = $m->descricao;
            $this->categoriaId = (string) $m->categoria_pessoal_id;
            $this->valor = (string) $m->valor;
            $this->data = $m->data->toDateString();
            $this->meio = (string) $m->meio;
            $this->notas = (string) $m->notas;
            $this->daEmpresa = $m->retirada_id !== null;
        } elseif ($fixoId !== null) {
            $fixo = FixoPessoal::findOrFail($fixoId);
            $mes = FinancasPessoais::mesDe($competencia);
            $prevista = $fixo->dataPrevista($mes);

            $this->fixoId = $fixo->id;
            $this->competencia = $mes->format('Y-m');
            $this->tipo = $fixo->tipo->value;
            $this->descricao = $fixo->descricao;
            $this->categoriaId = (string) $fixo->categoria_pessoal_id;
            $this->valor = (string) $fixo->valor;
            $this->meio = (string) $fixo->meio;
            $this->data = ($mes->isSameMonth(Carbon::today()) ? Carbon::today() : $prevista)->toDateString();
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
        if ($this->fixoId !== null || $this->tipoEnum()->value === $tipo) {
            return;
        }

        $this->tipo = $tipo === 'entrada' ? 'entrada' : 'saida';
        unset($this->categoriaOptions);
        $this->categoriaId = $this->categoriaOptions[0]['value'] ?? '';
    }

    #[Computed]
    public function categoriaOptions(): array
    {
        return CategoriaPessoal::opcoes($this->tipoEnum(), $this->categoriaId !== '' ? (int) $this->categoriaId : null);
    }

    #[Computed]
    public function meios(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
    }

    #[Computed]
    public function titulo(): string
    {
        return match (true) {
            $this->movimentoId !== null => 'Editar movimento',
            $this->fixoId !== null => 'Confirmar '.($this->tipoEnum() === TipoMovimento::Entrada ? 'recebimento' : 'pagamento'),
            default => 'Nova '.mb_strtolower($this->tipoEnum()->label()),
        };
    }

    private function tipoEnum(): TipoMovimento
    {
        return TipoMovimento::from($this->tipo);
    }

    protected function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['entrada', 'saida'])],
            'descricao' => ['required', 'string', 'max:160'],
            'categoriaId' => ['required', Rule::exists('categorias_pessoais', 'id')->where('user_id', auth()->id())->where('tipo', $this->tipo)],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'data' => ['required', 'date'],
            'meio' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'descricao.required' => 'Escreve uma descrição curta (ex: Mercado, Combustível).',
            'categoriaId.required' => 'Escolhe uma categoria.',
            'valor.required' => 'Indica o valor.',
            'valor.min' => 'O valor tem de ser maior que zero.',
            'valor.numeric' => 'Usa só números (ex: 1500 ou 1500.50).',
        ];
    }

    public function guardar(): void
    {
        if ($this->daEmpresa) {
            return;
        }

        $this->valor = Valor::normalizar($this->valor);
        $dados = $this->validate();

        $atributos = [
            'tipo' => $dados['tipo'],
            'descricao' => trim($dados['descricao']),
            'categoria_pessoal_id' => (int) $dados['categoriaId'],
            'valor' => $dados['valor'],
            'data' => $dados['data'],
            'meio' => $dados['meio'] ?: null,
            'notas' => $dados['notas'] ?: null,
            'ignorado' => false,
        ];

        if ($this->movimentoId !== null) {
            MovimentoPessoal::findOrFail($this->movimentoId)->update($atributos);
            $titulo = 'Movimento actualizado';
        } elseif ($this->fixoId !== null) {
            $fixo = FixoPessoal::findOrFail($this->fixoId);
            // updateOrCreate: confirming a fixo that was "ignorado" this month replaces the marker row.
            MovimentoPessoal::updateOrCreate(
                ['fixo_pessoal_id' => $fixo->id, 'competencia' => FinancasPessoais::mesDe($this->competencia)->toDateString()],
                $atributos,
            );
            $titulo = $this->tipoEnum() === TipoMovimento::Entrada ? 'Recebimento confirmado' : 'Pagamento confirmado';
        } else {
            MovimentoPessoal::create($atributos);
            $titulo = $this->tipoEnum()->label().' registada';
        }

        $this->dispatch('toast', title: $titulo, body: $atributos['descricao'].' — '.FinancasPessoais::mzn((float) $atributos['valor']), tone: 'success');
        $this->dispatch('pessoal-actualizado');
        $this->dispatch('nav-badge', id: 'resumo', n: app(FinancasPessoais::class)->nAtrasoHoje());
        $this->show = false;
    }

    public function apagar(): void
    {
        if ($this->daEmpresa) {
            return;
        }

        if ($this->movimentoId === null) {
            return;
        }

        $m = MovimentoPessoal::findOrFail($this->movimentoId);
        $m->delete();

        $this->dispatch('toast', title: 'Movimento apagado', body: $m->descricao, tone: 'success');
        $this->dispatch('pessoal-actualizado');
        $this->dispatch('nav-badge', id: 'resumo', n: app(FinancasPessoais::class)->nAtrasoHoje());
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.pessoal.movimento-dialog');
    }
}
