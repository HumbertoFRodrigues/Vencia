<?php

namespace App\Livewire\Pessoal;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\MovimentoPessoal;
use App\Services\FinancasPessoais;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Análise pessoal')]
class Analise extends Component
{
    #[Url(except: 0)]
    public int $ano = 0;

    public string $simCategoria = '';

    public int $simCorte = 10;

    public int $simAumento = 0;

    public function mount(FinancasPessoais $financas): void
    {
        $financas->garantirCategoriasPadrao();

        $maior = collect($financas->mediasRecentes()['categorias'])->sortDesc()->keys()->first();
        $this->simCategoria = $maior ? (string) $maior : '';
    }

    public function anoActual(): int
    {
        return $this->ano > 2000 ? $this->ano : (int) Carbon::today()->year;
    }

    public function mudarAno(int $delta): void
    {
        $alvo = $this->anoActual() + $delta;
        $this->ano = $alvo === (int) Carbon::today()->year ? 0 : $alvo;
    }

    public function irParaEsteAno(): void
    {
        $this->ano = 0;
    }

    private function financas(): FinancasPessoais
    {
        return app(FinancasPessoais::class);
    }

    #[Computed]
    public function serie(): array
    {
        return $this->financas()->serieAnual($this->anoActual());
    }

    #[Computed]
    public function totais(): array
    {
        $ano = $this->anoActual();
        $serie = collect($this->serie);
        $meses = $this->financas()->mesesActivos($ano);
        $entradas = (float) $serie->sum('entradas');
        $saidas = (float) $serie->sum('saidas');

        $saidasFixas = (float) MovimentoPessoal::query()->contabilizados()
            ->where('tipo', TipoMovimento::Saida->value)->whereNotNull('fixo_pessoal_id')
            ->whereBetween('data', [Carbon::create($ano, 1, 1)->toDateString(), Carbon::create($ano, 12, 31)->toDateString()])
            ->sum('valor');

        $comDados = $serie->filter(fn ($m) => $m['entradas'] > 0 || $m['saidas'] > 0);

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $entradas - $saidas,
            'taxa' => $entradas > 0 ? (($entradas - $saidas) / $entradas) * 100 : null,
            'mediaEntradas' => $entradas / $meses,
            'mediaSaidas' => $saidas / $meses,
            'pctFixo' => $saidas > 0 ? ($saidasFixas / $saidas) * 100 : null,
            'melhor' => $comDados->sortByDesc('saldo')->first(),
            'pior' => $comDados->sortBy('saldo')->first(),
            'max' => max(1.0, (float) $serie->max('entradas'), (float) $serie->max('saidas')),
        ];
    }

    #[Computed]
    public function categoriasSaida(): array
    {
        return $this->financas()->categoriasDoAno($this->anoActual(), TipoMovimento::Saida);
    }

    #[Computed]
    public function categoriasEntrada(): array
    {
        return $this->financas()->categoriasDoAno($this->anoActual(), TipoMovimento::Entrada);
    }

    #[Computed]
    public function medias(): array
    {
        return $this->financas()->mediasRecentes();
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function simOpcoes(): array
    {
        $medias = $this->medias['categorias'];
        $categorias = CategoriaPessoal::query()->whereIn('id', array_keys($medias))->get()->keyBy('id');

        return collect($medias)->sortDesc()
            ->map(fn ($valor, $id) => ['value' => (string) $id, 'label' => $categorias[$id]->nome.' — '.FinancasPessoais::mzn($valor).'/mês'])
            ->values()->all();
    }

    /** What-if: cut one category by X% and/or earn Y% more, on top of the recent monthly averages. */
    #[Computed]
    public function simulacao(): ?array
    {
        $m = $this->medias;
        if ($m['meses'] === 0 || $m['entradas'] <= 0) {
            return null;
        }

        $corte = max(0, min(100, $this->simCorte)) / 100;
        $aumento = max(0, min(200, $this->simAumento)) / 100;
        $categoriaMedia = (float) ($m['categorias'][(int) $this->simCategoria] ?? 0);

        $poupancaHoje = $m['entradas'] - $m['saidas'];
        $novasEntradas = $m['entradas'] * (1 + $aumento);
        $novasSaidas = $m['saidas'] - ($categoriaMedia * $corte);
        $poupancaNova = $novasEntradas - $novasSaidas;

        return [
            'meses' => $m['meses'],
            'entradas' => $m['entradas'],
            'saidas' => $m['saidas'],
            'poupancaHoje' => $poupancaHoje,
            'taxaHoje' => ($poupancaHoje / $m['entradas']) * 100,
            'poupancaNova' => $poupancaNova,
            'taxaNova' => ($poupancaNova / $novasEntradas) * 100,
            'ganhoMes' => $poupancaNova - $poupancaHoje,
            'poupancaCorte' => $categoriaMedia * $corte,
            'ganhoAumento' => $novasEntradas - $m['entradas'],
        ];
    }

    #[On('pessoal-actualizado')]
    public function actualizar(): void
    {
        unset($this->serie, $this->totais, $this->categoriasSaida, $this->categoriasEntrada, $this->medias, $this->simOpcoes, $this->simulacao);
    }

    public function render()
    {
        return view('livewire.pessoal.analise');
    }
}
