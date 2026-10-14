<?php

namespace App\Services;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\ContaPessoal;
use App\Models\MetaPessoal;
use App\Models\MetodoPagamentoOpcao;
use App\Models\MovimentoPessoal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Per-account balances and savings goals of the personal panel (logged-in user only). */
class SaldosEMetas
{
    /**
     * Balance per account = opening balance + entradas − saídas with that meio.
     * Every active payment method is listed, plus any meio already used.
     *
     * @return list<array{meio: string, inicial: float, entradas: float, saidas: float, saldo: float, definido: bool}>
     */
    public function saldos(): array
    {
        $somas = MovimentoPessoal::query()->contabilizados()->whereNotNull('meio')
            ->selectRaw('meio, tipo, sum(valor) as total')
            ->groupBy('meio', 'tipo')
            ->get();

        $contas = ContaPessoal::query()->get()->keyBy('meio');
        $meios = collect(MetodoPagamentoOpcao::nomesActivos())->merge($somas->pluck('meio'))->merge($contas->keys())->unique()->values();

        return $meios->map(function (string $meio) use ($somas, $contas) {
            $entradas = (float) $somas->first(fn ($s) => $s->meio === $meio && $s->tipo === TipoMovimento::Entrada)?->total;
            $saidas = (float) $somas->first(fn ($s) => $s->meio === $meio && $s->tipo === TipoMovimento::Saida)?->total;
            $inicial = (float) ($contas[$meio]->saldo_inicial ?? 0);

            return [
                'meio' => $meio,
                'inicial' => $inicial,
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $inicial + $entradas - $saidas,
                'definido' => $contas->has($meio),
            ];
        })->all();
    }

    /** Movimentos with no meio can't be placed in any account; shown so the total is honest. */
    public function semMeio(): float
    {
        $linhas = MovimentoPessoal::query()->contabilizados()->whereNull('meio')->get(['tipo', 'valor']);

        return (float) $linhas->where('tipo', TipoMovimento::Entrada)->sum('valor') - (float) $linhas->where('tipo', TipoMovimento::Saida)->sum('valor');
    }

    /** "Acertar saldo": the account now shows exactly $actual, by adjusting its opening balance (no fake movement). */
    public function acertarSaldo(string $meio, float $actual): void
    {
        $conta = collect($this->saldos())->firstWhere('meio', $meio);
        $movimentos = $conta ? $conta['entradas'] - $conta['saidas'] : 0.0;

        ContaPessoal::updateOrCreate(['user_id' => auth()->id(), 'meio' => $meio], ['saldo_inicial' => round($actual - $movimentos, 2)]);
    }

    /**
     * @return list<array{meta: MetaPessoal, guardado: float, pct: float, falta: float, meses: ?int, porMes: ?float, atrasada: bool}>
     */
    public function metas(): array
    {
        $hoje = Carbon::today();

        return MetaPessoal::query()
            ->withSum('contribuicoes as guardado', 'valor')
            ->orderByRaw('concluida_em IS NOT NULL')
            ->orderByRaw('data_alvo IS NULL, data_alvo')
            ->get()
            ->map(function (MetaPessoal $m) use ($hoje) {
                $alvo = (float) $m->valor_alvo;
                $guardado = (float) $m->guardado;
                $falta = max(0, $alvo - $guardado);
                $meses = $m->data_alvo ? max(0, (int) ceil($hoje->floatDiffInMonths($m->data_alvo->copy()->endOfMonth(), false))) : null;

                return [
                    'meta' => $m,
                    'guardado' => $guardado,
                    'pct' => $alvo > 0 ? min(100, $guardado / $alvo * 100) : 0,
                    'falta' => $falta,
                    'meses' => $meses,
                    'porMes' => $meses ? $falta / $meses : null,
                    'atrasada' => $m->data_alvo !== null && $m->data_alvo->lt($hoje) && $falta > 0,
                ];
            })
            ->all();
    }

    /** Adds money to a goal: a saída in "Poupança & Investimento", linked to the goal. */
    public function contribuir(MetaPessoal $meta, float $valor, Carbon $data, ?string $meio): MovimentoPessoal
    {
        return DB::transaction(function () use ($meta, $valor, $data, $meio) {
            app(FinancasPessoais::class)->garantirCategoriasPadrao();

            $movimento = MovimentoPessoal::create([
                'tipo' => TipoMovimento::Saida,
                'descricao' => 'Meta: '.$meta->nome,
                'categoria_pessoal_id' => $this->categoriaPoupanca()->id,
                'valor' => $valor,
                'data' => $data->toDateString(),
                'meio' => $meio,
                'meta_pessoal_id' => $meta->id,
            ]);

            $guardado = (float) $meta->contribuicoes()->sum('valor');
            if ($meta->concluida_em === null && $guardado >= (float) $meta->valor_alvo) {
                $meta->update(['concluida_em' => now()]);
            }

            return $movimento;
        });
    }

    private function categoriaPoupanca(): CategoriaPessoal
    {
        return CategoriaPessoal::query()->doTipo(TipoMovimento::Saida)->where('nome', 'Poupança & Investimento')->first()
            ?? CategoriaPessoal::create([
                'nome' => 'Poupança & Investimento', 'tipo' => TipoMovimento::Saida, 'icone' => 'piggy-bank', 'cor' => 'ciano',
                'ordem' => (int) CategoriaPessoal::query()->max('ordem') + 1,
            ]);
    }
}
