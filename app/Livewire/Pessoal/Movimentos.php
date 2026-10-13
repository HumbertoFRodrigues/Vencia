<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Livewire\Pessoal\Concerns\NavegaMeses;
use App\Models\CategoriaPessoal;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use App\Services\RelatorioPdf;
use App\Support\Csv;
use App\View\Components\Ui\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Movimentos pessoais')]
class Movimentos extends Component
{
    use NavegaMeses;

    #[Url(except: 'todos')]
    public string $tipo = 'todos';

    #[Url(except: '')]
    public string $categoria = '';

    #[Url(except: '')]
    public string $q = '';

    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();

        // Filters arrive from the URL: anything unexpected falls back to "everything".
        if (! in_array($this->tipo, ['todos', 'entrada', 'saida'], true)) {
            $this->tipo = 'todos';
        }
        if ($this->categoria !== '' && ! ctype_digit($this->categoria)) {
            $this->categoria = '';
        }
        $this->q = mb_substr($this->q, 0, 100);
    }

    public function setTab(string $tipo): void
    {
        $this->tipo = in_array($tipo, ['todos', 'entrada', 'saida'], true) ? $tipo : 'todos';
        $this->categoria = '';
    }

    private function base(): Builder
    {
        return MovimentoPessoal::query()->contabilizados()->noMes($this->mesActual())
            ->when(trim($this->q) !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->where('descricao', 'like', '%'.trim($this->q).'%')
                ->orWhere('notas', 'like', '%'.trim($this->q).'%')))
            ->when($this->categoria !== '', fn (Builder $b) => $b->where('categoria_pessoal_id', (int) $this->categoria));
    }

    #[Computed]
    public function movimentos()
    {
        return $this->base()
            ->when($this->tipo !== 'todos', fn (Builder $b) => $b->where('tipo', $this->tipo))
            ->with(['categoria', 'fixo'])
            ->orderByDesc('data')->orderByDesc('id')
            ->get();
    }

    /** Counts per tab and totals ignore the tab itself, so the tabs always show the whole picture. */
    #[Computed]
    public function totais(): array
    {
        $linhas = $this->base()->get(['tipo', 'valor']);
        $entradas = (float) $linhas->where('tipo', TipoMovimento::Entrada)->sum('valor');
        $saidas = (float) $linhas->where('tipo', TipoMovimento::Saida)->sum('valor');

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $entradas - $saidas,
            'nTodos' => $linhas->count(),
            'nEntradas' => $linhas->where('tipo', TipoMovimento::Entrada)->count(),
            'nSaidas' => $linhas->where('tipo', TipoMovimento::Saida)->count(),
        ];
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function categoriaOptions(): array
    {
        $opcoes = CategoriaPessoal::query()
            ->when($this->tipo !== 'todos', fn (Builder $b) => $b->where('tipo', $this->tipo))
            ->orderBy('tipo', 'desc')->orderBy('ordem')->orderBy('nome')
            ->get()
            ->map(fn (CategoriaPessoal $c) => [
                'value' => (string) $c->id,
                'label' => ($this->tipo === 'todos' ? ($c->tipo === TipoMovimento::Entrada ? 'Entrada · ' : 'Saída · ') : '').$c->nome.($c->arquivado ? ' (arquivada)' : ''),
            ])
            ->all();

        return array_merge([['value' => '', 'label' => 'Todas as categorias']], $opcoes);
    }

    public function exportarCsv(): StreamedResponse
    {
        $this->dispatch('toast', title: 'A exportar CSV', body: 'A transferência começa em breve.', tone: 'success');

        return Csv::download(
            'movimentos_'.$this->mesActual()->format('Y-m').'.csv',
            ['Data', 'Tipo', 'Descrição', 'Categoria', 'Meio', 'Fixo', 'Valor', 'Nota'],
            $this->movimentos->map(fn (MovimentoPessoal $m) => [
                $m->data->format('d/m/Y'),
                $m->tipo->label(),
                $m->descricao,
                $m->categoria?->nome ?? '',
                $m->meio ? PaymentMethod::labelFor($m->meio) : '',
                $m->fixo_pessoal_id ? 'Sim' : 'Não',
                number_format((float) $m->valor, 2, ',', ''),
                $m->notas ?? '',
            ]),
        );
    }

    public function exportarPdf(): StreamedResponse
    {
        $t = $this->totais;
        $this->dispatch('toast', title: 'A exportar PDF', body: 'A transferência começa em breve.', tone: 'success');

        return RelatorioPdf::gerar(
            titulo: 'Movimentos pessoais',
            subtitulo: $this->rotuloMes().($this->tipo !== 'todos' ? ' · '.TipoMovimento::from($this->tipo)->plural() : ''),
            colunas: ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor'],
            linhas: $this->movimentos->map(fn (MovimentoPessoal $m) => [
                $m->data->format('d/m/Y'),
                $m->descricao,
                $m->categoria?->nome ?? '',
                $m->tipo->label(),
                ($m->tipo === TipoMovimento::Saida ? '-' : '').RelatorioPdf::moeda((float) $m->valor),
            ])->all(),
            nomeArquivo: 'movimentos_'.$this->mesActual()->format('Y-m').'.pdf',
            colunasNumericas: [4],
            resumo: [
                'Entradas' => RelatorioPdf::moeda($t['entradas']),
                'Saídas' => RelatorioPdf::moeda($t['saidas']),
                'Saldo' => RelatorioPdf::moeda($t['saldo']),
            ],
        );
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->movimentos, $this->totais);
    }

    public function render()
    {
        return view('livewire.pessoal.movimentos');
    }
}
