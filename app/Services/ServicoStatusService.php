<?php

namespace App\Services;

use App\Enums\HistoricoKind;
use App\Enums\IntervaloLembrete;
use App\Enums\Periodicidade;
use App\Enums\ServicoStatus;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\Servico;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServicoStatusService
{
    /**
     * Recalculates what a servico's status *should* be today, comparing
     * vencimento with the earliest enabled reminder interval (a_vencer) and
     * with today itself (vencido). Does not persist — callers decide when to
     * save the result. suspenso/cancelado are terminal/manual states and are
     * never overridden here; a servico with no vencimento (e.g. "unica") can't
     * be evaluated and keeps its current status.
     */
    public function avaliar(Servico $servico): ServicoStatus
    {
        if (in_array($servico->status, [ServicoStatus::Suspenso, ServicoStatus::Cancelado], true)) {
            return $servico->status;
        }

        if ($servico->vencimento === null) {
            return $servico->status;
        }

        $dias = $servico->dias;

        if ($dias < 0) {
            return ServicoStatus::Vencido;
        }

        $limiar = $this->maiorIntervaloActivo($servico);

        if ($limiar !== null && $dias <= $limiar) {
            return ServicoStatus::AVencer;
        }

        return ServicoStatus::Activo;
    }

    /**
     * Largest "dias antes" offset among the reminder intervals enabled for
     * this servico (per-servico override falling back to the global default
     * per flag), or null if none are enabled. That's the day count at which
     * the servico enters the a_vencer window.
     */
    private function maiorIntervaloActivo(Servico $servico): ?int
    {
        $config = $servico->lembreteConfig;
        $globais = Configuracao::obter('lembretes', []);

        $maior = null;

        foreach (IntervaloLembrete::ordenadas() as $intervalo) {
            $offset = $intervalo->diasOffset();

            if ($offset === null) {
                continue; // apos_vencimento has no fixed offset, irrelevant to the a_vencer threshold
            }

            $ligado = $config?->{$intervalo->value} ?? (bool) ($globais[$intervalo->value] ?? false);

            if ($ligado && ($maior === null || $offset > $maior)) {
                $maior = $offset;
            }
        }

        return $maior;
    }

    /**
     * Records a payment, advances vencimento by one periodicidade interval
     * (from the current vencimento, not from today), returns the servico to
     * activo and logs a Historico entry. Not applicable to periodicidade
     * "unica", which has no renewal cycle.
     *
     * @param array{data?: string, valor?: float|string, metodo: string, periodo?: string, user_id?: int|null} $dadosPagamento
     */
    public function renovar(Servico $servico, array $dadosPagamento): void
    {
        if ($servico->periodicidade === Periodicidade::Unica) {
            throw new RuntimeException('Serviços com periodicidade "única" não têm ciclo de renovação.');
        }

        DB::transaction(function () use ($servico, $dadosPagamento): void {
            $novoVencimento = $this->calcularNovoVencimento($servico);

            $servico->vencimento = $novoVencimento;
            $servico->status = ServicoStatus::Activo;
            $servico->save();

            $valor = $dadosPagamento['valor'] ?? $servico->valor;

            $servico->pagamentos()->create([
                'data' => $dadosPagamento['data'] ?? Carbon::today()->toDateString(),
                'cliente_id' => $servico->cliente_id,
                'valor' => $valor,
                'metodo' => $dadosPagamento['metodo'],
                'periodo' => $dadosPagamento['periodo'] ?? $servico->periodicidade->label(),
                'user_id' => $dadosPagamento['user_id'] ?? null,
            ]);

            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Pagamento recebido',
                'description' => sprintf('%s — %s', $valor, $servico->nome),
                'kind' => HistoricoKind::Pagamento,
            ]);
        });
    }

    private function calcularNovoVencimento(Servico $servico): Carbon
    {
        /** @var Carbon $base */
        $base = $servico->vencimento ?? Carbon::today();

        return match ($servico->periodicidade) {
            Periodicidade::Mensal => $base->copy()->addMonthNoOverflow(),
            Periodicidade::Trimestral => $base->copy()->addMonthsNoOverflow(3),
            Periodicidade::Semestral => $base->copy()->addMonthsNoOverflow(6),
            Periodicidade::Anual => $base->copy()->addYearNoOverflow(),
            Periodicidade::Personalizada => $base->copy()->addDays((int) ($servico->duracao_dias ?? 0)),
            Periodicidade::Unica => throw new RuntimeException('Serviços com periodicidade "única" não têm ciclo de renovação.'),
        };
    }

    /**
     * Creates a suspension record, moves the servico to suspenso and logs a
     * Historico entry. Never deletes the servico. Dispatching the "Serviço
     * terminado" email is out of scope here (Phase 6's mail engine) — this
     * only records whether the caller asked for one to be sent.
     *
     * @param array{data?: string, motivo: string, observacao?: string|null, enviou_email?: bool, user_id?: int|null} $dados
     */
    public function suspender(Servico $servico, array $dados): void
    {
        DB::transaction(function () use ($servico, $dados): void {
            $servico->suspensoes()->create([
                'data' => $dados['data'] ?? Carbon::today()->toDateString(),
                'motivo' => $dados['motivo'],
                'observacao' => $dados['observacao'] ?? null,
                'enviou_email' => $dados['enviou_email'] ?? false,
                'user_id' => $dados['user_id'] ?? null,
            ]);

            $servico->status = ServicoStatus::Suspenso;
            $servico->save();

            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Serviço suspenso',
                'description' => $dados['observacao'] ?? null,
                'kind' => HistoricoKind::Suspenso,
            ]);
        });
    }
}
