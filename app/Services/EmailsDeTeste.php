<?php

namespace App\Services;

use App\Enums\IntervaloLembrete;
use App\Mail\LembreteVencimentoMail;
use App\Mail\ReciboPagamentoMail;
use App\Mail\ResetPasswordMail;
use App\Mail\ServicoTerminadoMail;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Models\Suspensao;
use App\Support\AlertaErro;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Sends one sample of each email the app produces to any address, filled with
 * made-up data (nothing is saved, no history is written), so the admin can
 * check SMTP and how every message looks before a client receives it.
 */
class EmailsDeTeste
{
    public const TIPOS = [
        'simples' => 'Teste simples (só confirma o SMTP)',
        'lembrete' => 'Lembrete de vencimento ao cliente',
        'terminado' => 'Serviço suspenso ao cliente',
        'recibo' => 'Recibo de pagamento com PDF',
        'password' => 'Reposição da password',
        'copia' => 'Cópia de segurança (cria uma cópia real)',
        'erro' => 'Alerta de erro',
    ];

    /** What a business can test from its Configurações: the emails its clients receive. */
    public const TIPOS_CONTA = ['simples', 'lembrete', 'terminado', 'recibo'];

    /** The platform's own emails, tested by the super-admin with the platform's SMTP. */
    public const TIPOS_PLATAFORMA = ['simples', 'password', 'copia', 'erro'];

    /** True when mail only goes to laravel.log (no SMTP saved and .env still on "log"). */
    public static function soNoRegisto(bool $plataforma = false): bool
    {
        if ($plataforma) {
            return trim((string) (Configuracao::plataforma('smtp', [])['servidor'] ?? '')) === '' && config('mail.default') === 'log';
        }

        return ! Configuracao::envioDisponivel();
    }

    /** Sends the sample; throws when the mail server refuses it. */
    public function enviar(string $tipo, string $destino, bool $plataforma = false): void
    {
        $plataforma ? Configuracao::aplicarSmtpDaPlataforma() : Configuracao::aplicarSmtpEmTempoDeExecucao();

        match ($tipo) {
            'simples' => Mail::raw(
                "Isto é um email de teste do Vencia.\n\nSe o recebeste, o envio de emails (SMTP) está a funcionar.",
                fn ($m) => $m->to($destino)->subject('Email de teste — Vencia'),
            ),
            'lembrete' => Mail::to($destino)->send(new LembreteVencimentoMail($this->servico(), IntervaloLembrete::D7)),
            'terminado' => Mail::to($destino)->send(new ServicoTerminadoMail(
                $servico = $this->servico(),
                (new Suspensao(['data' => Carbon::today()]))->setRelation('servico', $servico),
            )),
            'recibo' => Mail::to($destino)->send(new ReciboPagamentoMail($pagamento = $this->pagamento(), app(Recibos::class)->pdf($pagamento))),
            'password' => Mail::to($destino)->send(new ResetPasswordMail(
                route('password.reset', ['token' => 'link-de-exemplo', 'email' => $destino]),
                (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            )),
            'copia' => $this->copia($destino),
            'erro' => $this->erro($destino),
        };
    }

    private function cliente(): Cliente
    {
        return new Cliente(['nome' => 'Cliente de Teste', 'email' => 'cliente@exemplo.co.mz']);
    }

    private function servico(): Servico
    {
        return (new Servico([
            'nome' => 'Alojamento web (exemplo)',
            'plano' => 'Plano Básico',
            'valor' => 2500,
            'vencimento' => Carbon::today()->addDays(7),
        ]))->setRelation('cliente', $this->cliente());
    }

    private function pagamento(): Pagamento
    {
        $servico = $this->servico();
        $pagamento = new Pagamento([
            'data' => Carbon::today(),
            'valor' => 2500,
            'metodo' => 'mpesa',
            'periodo' => Carbon::today()->format('m/Y'),
        ]);
        $pagamento->id = 0;

        return $pagamento->setRelation('cliente', $servico->cliente)->setRelation('servico', $servico);
    }

    private function copia(string $destino): void
    {
        $copias = app(CopiaSeguranca::class);
        $ficheiro = $copias->criar();
        $copias->limpar();

        // No SMTP yet: the copy exists; the caller reports "only in the log" like the other types.
        if (self::soNoRegisto(plataforma: true)) {
            return;
        }

        if (! $copias->enviarPorEmail($ficheiro, $destino)) {
            throw new RuntimeException('A cópia foi criada mas não foi enviada (demasiado grande para email ou falha no SMTP — ver o registo).');
        }
    }

    private function erro(string $destino): void
    {
        [$assunto, $corpo] = AlertaErro::mensagem(new RuntimeException('Erro de teste enviado a partir de Configurações — pode ignorar.'));

        Mail::raw($corpo, fn ($m) => $m->to($destino)->subject($assunto));
    }
}
