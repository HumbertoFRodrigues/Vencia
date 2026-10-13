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
    /** Global reminder settings, read once per instance instead of per servico × interval. */
    private ?array $globais = null;

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

        // A one-off purchase has no cycle to fall behind on.
        if ($servico->periodicidade === Periodicidade::Unica) {
            return ServicoStatus::Activo;
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
        return $this->proximoIntervaloAtivo($servico);
    }

    /**
     * Public generalisation of the above: largest enabled offset that is at
     * or below $antesDeDias (or the overall largest enabled offset when
     * $antesDeDias is null, which is what avaliar()'s a_vencer threshold
     * needs). Given the current "dias" countdown, this is the offset of the
     * *next* reminder still ahead — used by Serviço detalhe's a_vencer
     * banner ("próximo aviso automático: N dias antes") so that screen
     * doesn't reimplement the two-tier config resolution itself.
     */
    public function proximoIntervaloAtivo(Servico $servico, ?int $antesDeDias = null): ?int
    {
        $maior = null;

        foreach (IntervaloLembrete::ordenadas() as $intervalo) {
            $offset = $intervalo->diasOffset();

            if ($offset === null) {
                continue; // apos_vencimento has no fixed offset, irrelevant here
            }

            if ($antesDeDias !== null && $offset > $antesDeDias) {
                continue;
            }

            if ($this->intervaloActivo($servico, $intervalo) && ($maior === null || $offset > $maior)) {
                $maior = $offset;
            }
        }

        return $maior;
    }

    /**
     * Whether a given reminder interval is effectively enabled for this
     * servico: per-servico override (servico_lembrete_config) if it's set,
     * otherwise the global default from Configuracao's "lembretes" key. This
     * is the single place the two-tier reminder config is resolved — reused
     * by proximoIntervaloAtivo() above (for the a_vencer threshold) and by
     * VerificacaoDiariaService (for deciding whether to actually send a
     * given interval's reminder today).
     */
    public function intervaloActivo(Servico $servico, IntervaloLembrete $intervalo): bool
    {
        $config = $servico->lembreteConfig;
        $globais = $this->globais ??= Configuracao::obter('lembretes', []);

        return $config?->{$intervalo->value} ?? (bool) ($globais[$intervalo->value] ?? false);
    }

    /**
     * Records a payment, advances vencimento by one periodicidade interval
     * (from the current vencimento, not from today), returns the servico to
     * activo and logs a Historico entry. Not applicable to periodicidade
     * "unica", which has no renewal cycle.
     *
     * @param  array{data?: string, valor?: float|string, metodo: string, periodo?: string, observacoes?: string|null, user_id?: int|null}  $dadosPagamento
     */
    public function renovar(Servico $servico, array $dadosPagamento): void
    {
        if ($servico->periodicidade === Periodicidade::Unica) {
            throw new RuntimeException('Serviços com periodicidade "única" não têm ciclo de renovação.');
        }

        if ($servico->status === ServicoStatus::Cancelado) {
            throw new RuntimeException('Um serviço cancelado não pode ser renovado.');
        }

        DB::transaction(function () use ($servico, $dadosPagamento): void {
            $novoVencimento = $this->calcularProximoVencimento($servico);

            // Renewing reactivates a suspended servico, but the new status
            // still follows the new vencimento: a long-overdue servico renewed
            // by one period can still be vencido or a_vencer.
            $servico->vencimento = $novoVencimento;
            $servico->status = ServicoStatus::Activo;
            $servico->status = $this->avaliar($servico);
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

            $observacoes = trim((string) ($dadosPagamento['observacoes'] ?? ''));
            $descricao = sprintf('%s — %s', $valor, $servico->nome);

            if ($observacoes !== '') {
                $descricao .= ' — '.$observacoes;
            }

            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Pagamento recebido',
                'description' => $descricao,
                'kind' => HistoricoKind::Pagamento,
            ]);
        });
    }

    /**
     * Public preview of the exact date math renovar() applies, so
     * RenovarDialog's "novo vencimento calculado" line and the real
     * confirmation never drift apart. Not applicable to "unica" (no
     * renewal cycle — callers should hide the Renovar action entirely for
     * those, per the brief).
     */
    public function calcularProximoVencimento(Servico $servico): Carbon
    {
        if ($servico->periodicidade === Periodicidade::Unica) {
            throw new RuntimeException('Serviços com periodicidade "única" não têm ciclo de renovação.');
        }

        /** @var Carbon $base */
        $base = $servico->vencimento ?? Carbon::today();

        // Keep the billing day: a vencimento pushed back to the end of a
        // short month (31 Jan → 28 Feb) returns to the original day
        // (→ 31 Mar) instead of drifting to the 28th for good.
        $dia = $base->day;
        if ($servico->inicio && $base->day === $base->daysInMonth && $servico->inicio->day > $base->day) {
            $dia = $servico->inicio->day;
        }

        return $this->somarPeriodicidade($base, $servico->periodicidade, $servico->duracao_dias, $dia);
    }

    /**
     * Same "add one periodicidade interval" math as calcularProximoVencimento(),
     * applied to an arbitrary início instead of an existing vencimento — used
     * by NewSubscriptionDialog to preview/derive the initial vencimento for a
     * brand-new servico before it exists as a persisted model. Returns null
     * for "unica" (no vencimento at all, per the brief).
     */
    public function calcularVencimentoInicial(Carbon $inicio, Periodicidade $periodicidade, ?int $duracaoDias = null): ?Carbon
    {
        if ($periodicidade === Periodicidade::Unica) {
            return null;
        }

        return $this->somarPeriodicidade($inicio, $periodicidade, $duracaoDias, $inicio->day);
    }

    private function somarPeriodicidade(Carbon $base, Periodicidade $periodicidade, ?int $duracaoDias, int $dia): Carbon
    {
        $meses = match ($periodicidade) {
            Periodicidade::Mensal => 1,
            Periodicidade::Trimestral => 3,
            Periodicidade::Semestral => 6,
            Periodicidade::Anual => 12,
            Periodicidade::Personalizada => null,
            Periodicidade::Unica => throw new RuntimeException('Serviços com periodicidade "única" não têm ciclo de renovação.'),
        };

        if ($meses === null) {
            return $base->copy()->addDays((int) ($duracaoDias ?? 0));
        }

        $alvo = $base->copy()->startOfMonth()->addMonthsNoOverflow($meses);

        return $alvo->day(min($dia, $alvo->daysInMonth));
    }

    /**
     * Brings one servico's stored status in line with today, logging the
     * change in Historico. Returns whether anything changed. Shared by the
     * daily job and the once-a-day refresh on first page load
     * (AppServiceProvider), so status is right even on a day the job didn't run.
     */
    public function sincronizarEstado(Servico $servico): bool
    {
        $novo = $this->avaliar($servico);

        if ($novo === $servico->status) {
            return false;
        }

        $servico->status = $novo;
        $servico->save();

        Historico::create([
            'cliente_id' => $servico->cliente_id,
            'servico_id' => $servico->id,
            'occurred_at' => Carbon::now(),
            'title' => 'Estado alterado para '.$novo->label(),
            'description' => null,
            'kind' => $novo === ServicoStatus::Vencido ? HistoricoKind::Vencido : HistoricoKind::Alterado,
        ]);

        return true;
    }

    /** @return int how many servicos changed status */
    public function sincronizarTodos(): int
    {
        $alterados = 0;

        Servico::query()
            ->with('lembreteConfig')
            ->whereNotIn('status', [ServicoStatus::Suspenso, ServicoStatus::Cancelado])
            ->each(function (Servico $servico) use (&$alterados): void {
                $alterados += (int) $this->sincronizarEstado($servico);
            });

        return $alterados;
    }

    /**
     * Creates a suspension record, moves the servico to suspenso and logs a
     * Historico entry. Never deletes the servico. Dispatching the "Serviço
     * terminado" email is out of scope here (Phase 6's mail engine) — this
     * only records whether the caller asked for one to be sent.
     *
     * @param  array{data?: string, motivo: string, observacao?: string|null, enviou_email?: bool, user_id?: int|null}  $dados
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

    /**
     * Cancels a servico: the *other* terminal branch of the status machine
     * (activo → a_vencer → vencido → suspenso | cancelado). Unlike
     * suspender(), this is deliberately not reversible from within the app —
     * there is no "Renovar"/"Reactivar" arrow back to cancelado in the
     * brief's own diagram, so callers (CancelarDialog) must present it as
     * permanent. Sets status = Cancelado and logs a Historico entry; never
     * deletes the servico, its pagamentos or its historicos.
     *
     * Deliberately does NOT touch ServicoLembreteConfig. A cancelado servico
     * already cannot receive an automatic reminder without that: avaliar()
     * (above) treats suspenso/cancelado as terminal and never recomputes a
     * status for them, and VerificacaoDiariaService's daily pass excludes
     * both statuses from its Servico query (`whereNotIn('status', [...])`)
     * and, redundantly, from enviarLembretesDevidos()'s own guard. So there's
     * no per-servico reminder override left to clear — clearing one would be
     * a no-op that only adds a schema-touching illusion of "extra safety".
     *
     * @param  array{motivo: string}  $dados
     */
    public function cancelar(Servico $servico, array $dados): void
    {
        DB::transaction(function () use ($servico, $dados): void {
            $servico->status = ServicoStatus::Cancelado;
            $servico->save();

            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Serviço cancelado',
                'description' => trim($dados['motivo']) !== '' ? trim($dados['motivo']) : null,
                'kind' => HistoricoKind::Cancelado,
            ]);
        });
    }
}
