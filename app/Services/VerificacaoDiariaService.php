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
        $enviarEmails = (bool) ($globais['verificacao_diaria'] ?? false);

        // The switch in Configurações only stops the emails: statuses must
        // stay right regardless, or every total in the app goes stale.
        if (! $enviarEmails) {
            Log::info('Verificação diária: lembretes por email desligados em Configurações → Lembretes; só os estados foram actualizados.');
            $resumo['nota'] = 'Os lembretes por email estão desligados em Configurações → Lembretes — só os estados foram actualizados.';
        } else {
            // Switches the mailer to the admin's UI-configured SMTP settings
            // for the rest of this run (a no-op when none is set).
            Configuracao::aplicarSmtpEmTempoDeExecucao();
        }

        Servico::query()
            ->with(['cliente', 'lembreteConfig'])
            ->whereNotIn('status', [ServicoStatus::Suspenso, ServicoStatus::Cancelado])
            ->each(function (Servico $servico) use (&$resumo, $enviarEmails): void {
                $resumo['servicos_avaliados']++;

                if ($this->statusService->sincronizarEstado($servico)) {
                    $resumo['estados_alterados']++;
                }

                if ($enviarEmails) {
                    $this->enviarLembretesDevidos($servico, $resumo);
                }
            });

        return $resumo;
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

        $intervalo = $this->intervaloEmVigor($servico, $servico->dias);

        if ($intervalo === null) {
            return;
        }

        $jaEnviado = LembreteEnviado::query()
            ->where('servico_id', $servico->id)
            ->where('intervalo', $intervalo->value)
            ->whereDate('vencimento_referencia', $servico->vencimento->toDateString())
            ->exists();

        if (! $jaEnviado) {
            $this->enviarLembrete($servico, $intervalo, $resumo);
        }
    }

    /**
     * The reminder that applies today: the closest enabled interval the
     * countdown has already reached — not only on its exact day. If the job
     * didn't run on day 7 (server or PC off), day 6 still sends the 7-day
     * reminder; once day 3 is reached it is the 3-day one that applies, so
     * missed reminders are never sent in a burst. After the due date only
     * "após vencimento" applies. Each one is still sent once per vencimento.
     */
    private function intervaloEmVigor(Servico $servico, int $dias): ?IntervaloLembrete
    {
        if ($dias < 0) {
            return $this->statusService->intervaloActivo($servico, IntervaloLembrete::AposVencimento)
                ? IntervaloLembrete::AposVencimento
                : null;
        }

        $emVigor = null;

        foreach (IntervaloLembrete::ordenadas() as $intervalo) {
            $offset = $intervalo->diasOffset();

            if ($offset === null || $dias > $offset || ! $this->statusService->intervaloActivo($servico, $intervalo)) {
                continue;
            }

            if ($emVigor === null || $offset < $emVigor->diasOffset()) {
                $emVigor = $intervalo;
            }
        }

        return $emVigor;
    }

    /** @param array<string, mixed> $resumo */
    private function enviarLembrete(Servico $servico, IntervaloLembrete $intervalo, array &$resumo): void
    {
        $emailCliente = $servico->cliente?->email;
        $admin = Configuracao::emailBccAdmin($emailCliente);

        // No client email: the empresa still gets the reminder so someone
        // follows up by phone; with nobody to send to, nothing is recorded
        // and the reminder is retried on the next run.
        if (! $emailCliente && $admin === []) {
            Log::warning(sprintf('Lembrete "%s" do serviço #%d não enviado: o cliente não tem email e a empresa também não.', $intervalo->value, $servico->id));
            $resumo['lembretes_falhados']++;

            return;
        }

        try {
            DB::transaction(function () use ($servico, $intervalo, $emailCliente, $admin): void {
                $mail = $emailCliente ? Mail::to($emailCliente)->bcc($admin) : Mail::to($admin);
                $mail->send(new LembreteVencimentoMail($servico, $intervalo));

                Historico::create([
                    'cliente_id' => $servico->cliente_id,
                    'servico_id' => $servico->id,
                    'occurred_at' => Carbon::now(),
                    'title' => $emailCliente ? 'Lembrete de vencimento enviado' : 'Lembrete enviado só à empresa',
                    'description' => $this->intervaloLabel($intervalo).' — vencimento em '.$servico->vencimento->format('d/m/Y')
                        .($emailCliente ? '' : ' — o cliente não tem email registado'),
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
