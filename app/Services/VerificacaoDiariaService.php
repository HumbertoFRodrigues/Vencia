<?php

namespace App\Services;

use App\Enums\HistoricoKind;
use App\Enums\IntervaloLembrete;
use App\Enums\Periodicidade;
use App\Enums\ServicoStatus;
use App\Mail\LembreteVencimentoMail;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\LembreteEnviado;
use App\Models\Servico;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The daily job (per HANDOFF_CLAUDE_CODE.md "State Management" — Windows
 * Task Scheduler calls `vencia:verificar-vencimentos` once a day, which
 * delegates here): recomputes every non-terminal servico's status, and
 * sends whichever reminder intervals fall due today, never sending the same
 * one twice for the same vencimento (enforced both by a dedup check here and
 * by lembretes_enviados' composite unique index as a backstop).
 */
class VerificacaoDiariaService
{
    public function __construct(private ServicoStatusService $statusService) {}

    /**
     * @return array{
     *     executado: bool,
     *     nota?: string,
     *     servicos_avaliados: int,
     *     estados_alterados: int,
     *     lembretes_enviados: int,
     *     lembretes_falhados: int,
     * }
     */
    public function executar(): array
    {
        $resumo = [
            'executado' => true,
            'servicos_avaliados' => 0,
            'estados_alterados' => 0,
            'lembretes_enviados' => 0,
            'lembretes_falhados' => 0,
        ];

        $globais = Configuracao::obter('lembretes', []);

        if (! ($globais['verificacao_diaria'] ?? false)) {
            Log::info('Verificação diária de vencimentos ignorada: desligada em Configurações → Lembretes.');

            $resumo['executado'] = false;
            $resumo['nota'] = 'Verificação diária automática está desligada em Configurações → Lembretes.';

            return $resumo;
        }

        // Switches the mailer to the admin's UI-configured SMTP settings for
        // the rest of this run, when one is set — a no-op (keeps .env's
        // MAIL_MAILER) otherwise. Called once here rather than per servico:
        // SMTP config doesn't change mid-run, and every send below goes
        // through the one mailer this selects.
        Configuracao::aplicarSmtpEmTempoDeExecucao();

        Servico::query()
            ->whereNotIn('status', [ServicoStatus::Suspenso, ServicoStatus::Cancelado])
            ->cursor()
            ->each(function (Servico $servico) use (&$resumo): void {
                $resumo['servicos_avaliados']++;

                $this->avaliarEstado($servico, $resumo);
                $this->enviarLembretesDevidos($servico, $resumo);
            });

        return $resumo;
    }

    /** @param array<string, mixed> $resumo */
    private function avaliarEstado(Servico $servico, array &$resumo): void
    {
        $novoStatus = $this->statusService->avaliar($servico);

        if ($novoStatus === $servico->status) {
            return;
        }

        $servico->status = $novoStatus;
        $servico->save();
        $resumo['estados_alterados']++;

        Historico::create([
            'cliente_id' => $servico->cliente_id,
            'servico_id' => $servico->id,
            'occurred_at' => Carbon::now(),
            'title' => 'Estado alterado para '.$this->statusLabel($novoStatus),
            'description' => null,
            'kind' => $novoStatus === ServicoStatus::Vencido ? HistoricoKind::Vencido : HistoricoKind::Alterado,
        ]);
    }

    /** @param array<string, mixed> $resumo */
    private function enviarLembretesDevidos(Servico $servico, array &$resumo): void
    {
        // "unica" has no renewal cycle and no fixed vencimento to compare
        // against; a servico that just moved to suspenso/cancelado above
        // stops getting reminders (matches "reminders stop" on suspension).
        if ($servico->periodicidade === Periodicidade::Unica || $servico->vencimento === null) {
            return;
        }

        if (in_array($servico->status, [ServicoStatus::Suspenso, ServicoStatus::Cancelado], true)) {
            return;
        }

        $dias = $servico->dias;

        foreach (IntervaloLembrete::ordenadas() as $intervalo) {
            $offset = $intervalo->diasOffset();

            // apos_vencimento has no fixed offset: it means "any day strictly
            // after vencimento", sent at most once per vencimento — the
            // dedup check below (keyed by vencimento_referencia) is what
            // actually caps it to one send per cycle, not a day comparison.
            $devidoHoje = $offset !== null ? $dias === $offset : $dias < 0;

            if (! $devidoHoje) {
                continue;
            }

            if (! $this->statusService->intervaloActivo($servico, $intervalo)) {
                continue;
            }

            $jaEnviado = LembreteEnviado::query()
                ->where('servico_id', $servico->id)
                ->where('intervalo', $intervalo->value)
                ->where('vencimento_referencia', $servico->vencimento->toDateString())
                ->exists();

            if ($jaEnviado) {
                continue;
            }

            $this->enviarLembrete($servico, $intervalo, $resumo);
        }
    }

    /** @param array<string, mixed> $resumo */
    private function enviarLembrete(Servico $servico, IntervaloLembrete $intervalo, array &$resumo): void
    {
        try {
            DB::transaction(function () use ($servico, $intervalo): void {
                if ($servico->cliente?->email) {
                    Mail::to($servico->cliente->email)
                        ->bcc(Configuracao::emailBccAdmin($servico->cliente->email))
                        ->send(new LembreteVencimentoMail($servico, $intervalo));
                }

                Historico::create([
                    'cliente_id' => $servico->cliente_id,
                    'servico_id' => $servico->id,
                    'occurred_at' => Carbon::now(),
                    'title' => 'Lembrete de vencimento enviado',
                    'description' => $this->intervaloLabel($intervalo).' — vencimento em '.$servico->vencimento->format('d/m/Y'),
                    'kind' => HistoricoKind::Lembrete,
                ]);

                LembreteEnviado::create([
                    'servico_id' => $servico->id,
                    'intervalo' => $intervalo->value,
                    'vencimento_referencia' => $servico->vencimento->toDateString(),
                    'enviado_em' => Carbon::now(),
                ]);
            });

            $resumo['lembretes_enviados']++;
        } catch (Throwable $e) {
            $resumo['lembretes_falhados']++;

            Log::error(sprintf(
                'Falha ao enviar lembrete "%s" do serviço #%d (%s): %s',
                $intervalo->value,
                $servico->id,
                $servico->nome,
                $e->getMessage(),
            ));
        }
    }

    private function statusLabel(ServicoStatus $status): string
    {
        return match ($status) {
            ServicoStatus::Activo => 'activo',
            ServicoStatus::AVencer => 'a vencer',
            ServicoStatus::Vencido => 'vencido',
            ServicoStatus::Suspenso => 'suspenso',
            ServicoStatus::Cancelado => 'cancelado',
        };
    }

    private function intervaloLabel(IntervaloLembrete $intervalo): string
    {
        return match ($intervalo) {
            IntervaloLembrete::D30 => '30 dias antes',
            IntervaloLembrete::D15 => '15 dias antes',
            IntervaloLembrete::D7 => '7 dias antes',
            IntervaloLembrete::D3 => '3 dias antes',
            IntervaloLembrete::D1 => '1 dia antes',
            IntervaloLembrete::NoDia => 'no dia',
            IntervaloLembrete::AposVencimento => 'após vencimento',
        };
    }

}
