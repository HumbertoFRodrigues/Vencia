<?php

namespace App\Livewire\Financas;

use App\Models\MetodoPagamentoOpcao;
use App\Services\FinancasPessoais;
use App\Services\Retiradas;
use App\Support\Valor;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/** "Retirar para pessoal": the owner chooses how much to take out of the business. */
class RetirarDialog extends Component
{
    public bool $show = false;

    public string $valor = '';

    public string $data = '';

    public string $meio = '';

    public string $nota = '';

    #[On('retirada:abrir')]
    public function abrir(): void
    {
        $this->resetValidation();
        $this->reset(['valor', 'meio', 'nota']);
        $this->data = Carbon::today()->toDateString();
        unset($this->disponivel);
        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    #[Computed]
    public function disponivel(): float
    {
        return app(Retiradas::class)->disponivel();
    }

    #[Computed]
    public function meios(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
    }

    /** The typed amount as a number (0 while incomplete), for the live "acima do disponível" warning. */
    #[Computed]
    public function valorNumerico(): float
    {
        $v = Valor::normalizar($this->valor);

        return is_numeric($v) ? (float) $v : 0.0;
    }

    protected function rules(): array
    {
        return [
            'valor' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'data' => ['required', 'date_format:Y-m-d'],
            'meio' => ['nullable', Rule::in(MetodoPagamentoOpcao::nomesActivos())],
            'nota' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'valor.required' => 'Indica quanto queres retirar.',
            'valor.numeric' => 'Usa só números (ex: 15000 ou 15.000,00).',
            'valor.min' => 'O valor tem de ser maior que zero.',
        ];
    }

    public function confirmar(): void
    {
        $this->authorize('retirar');
        $this->valor = Valor::normalizar($this->valor);
        $dados = $this->validate();

        app(Retiradas::class)->retirar(
            (float) $dados['valor'],
            Carbon::createFromFormat('!Y-m-d', $dados['data']),
            $dados['meio'] ?: null,
            trim((string) $dados['nota']) ?: null,
        );

        $this->dispatch('toast', title: 'Retirada registada', body: FinancasPessoais::mzn((float) $dados['valor']).' — já entrou no teu painel pessoal.', tone: 'success');
        $this->dispatch('retirada-feita');
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.financas.retirar-dialog');
    }
}
