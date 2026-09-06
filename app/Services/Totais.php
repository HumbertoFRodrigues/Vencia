<?php

namespace App\Services;

use App\Enums\ClienteStatus;
use App\Enums\Periodicidade;
use App\Enums\ServicoStatus;
use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Servico;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for every derived headline figure (dashboard tiles,
 * sidebar badges, Pagamentos/Finanças screens). Nothing else should
 * re-derive these numbers with its own query.
 */
class Totais
{
    /**
     * @return array{
     *     entradas: float, nPagamentos: int,
     *     aReceber: float, nAReceber: int,
     *     emAtraso: float, nEmAtraso: int,
     *     aVencer: int,
     *     clientesActivos: int, servicosActivos: int,
     *     mrr: float, arr: float, esperada: float,
     * }
     */
    public function calcular(): array
    {
        $inicioMes = Carbon::today()->startOfMonth();
        $fimMes = Carbon::today()->endOfMonth();

        $pagamentosMes = Pagamento::whereBetween('data', [$inicioMes->toDateString(), $fimMes->toDateString()])->get();

        $aReceberServicos = Servico::whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])->get();
        $emAtrasoServicos = $aReceberServicos->filter(fn (Servico $s) => $s->status === ServicoStatus::Vencido);
        $aVencerCount = $aReceberServicos->filter(fn (Servico $s) => $s->status === ServicoStatus::AVencer)->count();

        $clientesActivos = Cliente::where('status', ClienteStatus::Activo)->count();

        $servicosActivos = Servico::whereNotIn('status', [ServicoStatus::Cancelado, ServicoStatus::Suspenso])->get();

        $mrr = round($servicosActivos->sum(fn (Servico $s) => match ($s->periodicidade) {
            Periodicidade::Mensal => (float) $s->valor,
            Periodicidade::Trimestral => (float) $s->valor / 3,
            Periodicidade::Semestral => (float) $s->valor / 6,
            Periodicidade::Anual => (float) $s->valor / 12,
            Periodicidade::Unica, Periodicidade::Personalizada => 0.0,
        }));

        $entradas = (float) $pagamentosMes->sum(fn (Pagamento $p) => (float) $p->valor);
        $aReceber = (float) $aReceberServicos->sum(fn (Servico $s) => (float) $s->valor);

        return [
            'entradas' => $entradas,
            'nPagamentos' => $pagamentosMes->count(),
            'aReceber' => $aReceber,
            'nAReceber' => $aReceberServicos->count(),
            'emAtraso' => (float) $emAtrasoServicos->sum(fn (Servico $s) => (float) $s->valor),
            'nEmAtraso' => $emAtrasoServicos->count(),
            'aVencer' => $aVencerCount,
            'clientesActivos' => $clientesActivos,
            'servicosActivos' => $servicosActivos->count(),
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'esperada' => $entradas + $aReceber,
        ];
    }
}
