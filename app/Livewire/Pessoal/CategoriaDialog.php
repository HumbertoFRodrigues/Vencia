<?php

namespace App\Livewire\Pessoal;

use App\Models\CategoriaPessoal;
use App\Services\FinancasPessoais;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoriaDialog extends Component
{
    public bool $show = false;

    public ?int $categoriaId = null;

    public string $tipo = 'saida';

    public string $nome = '';

    public string $icone = 'package';

    public string $cor = 'cinza';

    public string $limite = '';

    public bool $arquivado = false;

    #[On('categoria:abrir')]
    public function abrir(?string $tipo = null, ?int $categoriaId = null): void
    {
        $this->resetValidation();
        $this->reset(['categoriaId', 'nome', 'limite', 'arquivado']);
        $this->tipo = $tipo === 'entrada' ? 'entrada' : 'saida';
        $this->icone = 'package';
        $this->cor = 'cinza';

        if ($categoriaId !== null) {
            $c = CategoriaPessoal::findOrFail($categoriaId);
            $this->categoriaId = $c->id;
            $this->tipo = $c->tipo->value;
            $this->nome = $c->nome;
            $this->icone = $c->icone;
            $this->cor = $c->cor;
            $this->limite = $c->limite_mensal !== null ? (string) (float) $c->limite_mensal : '';
            $this->arquivado = $c->arquivado;
        }

        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['entrada', 'saida'])],
            'nome' => [
                'required', 'string', 'max:80',
                Rule::unique('categorias_pessoais', 'nome')
                    ->where('user_id', auth()->id())->where('tipo', $this->tipo)
                    ->ignore($this->categoriaId),
            ],
            'icone' => ['required', Rule::in(CategoriaPessoal::ICONES)],
            'cor' => ['required', Rule::in(array_keys(CategoriaPessoal::CORES))],
            'limite' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'arquivado' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nome.required' => 'Dá um nome à categoria.',
            'nome.unique' => 'Já tens uma categoria com este nome.',
            'limite.numeric' => 'Usa só números (ex: 8000).',
        ];
    }

    public function guardar(): void
    {
        $this->limite = str_replace([' ', ','], ['', '.'], $this->limite);
        $dados = $this->validate();

        $atributos = [
            'tipo' => $dados['tipo'],
            'nome' => trim($dados['nome']),
            'icone' => $dados['icone'],
            'cor' => $dados['cor'],
            'limite_mensal' => $dados['tipo'] === 'saida' && $dados['limite'] !== '' && (float) $dados['limite'] > 0 ? $dados['limite'] : null,
            'arquivado' => $this->categoriaId !== null && $dados['arquivado'],
        ];

        if ($this->categoriaId !== null) {
            CategoriaPessoal::findOrFail($this->categoriaId)->update($atributos);
        } else {
            CategoriaPessoal::create($atributos + ['ordem' => CategoriaPessoal::query()->max('ordem') + 1]);
        }

        $limite = $atributos['limite_mensal'] ? 'limite de '.FinancasPessoais::mzn((float) $atributos['limite_mensal']).' / mês' : 'sem limite mensal';
        $this->dispatch('toast', title: $this->categoriaId ? 'Categoria actualizada' : 'Categoria criada', body: $atributos['nome'].' — '.$limite, tone: 'success');
        $this->dispatch('pessoal-actualizado');
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.pessoal.categoria-dialog', [
            'icones' => CategoriaPessoal::ICONES,
            'cores' => CategoriaPessoal::CORES,
        ]);
    }
}
