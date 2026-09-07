<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    use HasFactory;

    protected $table = 'configuracoes';

    protected $fillable = [
        'chave', 'valor',
    ];

    protected $casts = [
        'valor' => 'array',
    ];

    public static function obter(string $chave, mixed $default = null): mixed
    {
        return static::query()->where('chave', $chave)->value('valor') ?? $default;
    }

    /**
     * Cosmetic display currency only (admin-confirmed scope: relabels amounts,
     * never converts them — every valor in the DB stays a MZN amount). Single
     * source of truth for every "MZN"/"USD"/"ZAR" suffix shown across the UI,
     * PDFs, and emails, so changing it in Configurações updates them all.
     */
    public static function moeda(): string
    {
        return self::obter('empresa', [])['moeda'] ?? 'MZN';
    }

    /**
     * The admin (empresa) always gets a copy of every client-facing email —
     * that's the whole point of the reminder engine from the admin's side,
     * the client copy is the secondary/optional part. Returns an empty array
     * (a no-op for Mail::bcc()) when no admin address is configured, or when
     * it's identical to the client's own address (avoids a redundant BCC to
     * the same inbox the mail is already going to).
     *
     * @return list<string>
     */
    public static function emailBccAdmin(?string $emailCliente = null): array
    {
        $admin = self::obter('empresa', [])['email'] ?? null;

        if (! $admin || $admin === $emailCliente) {
            return [];
        }

        return [$admin];
    }

    /**
     * Applies the admin's UI-configured SMTP settings (Configurações →
     * Empresa → "Envio de email") to the mailer actually used for the rest
     * of this request/console invocation — call this once, right before any
     * Mail::... ->send() (or ->raw()), from every place that sends mail:
     * VerificacaoDiariaService, SuspenderDialog, ConfiguracoesIndex::testarEmail().
     * Centralised here so none of those three call sites duplicates the
     * config-juggling logic.
     *
     * A no-op — leaving .env's MAIL_MAILER (today "log") exactly as it is —
     * when the admin hasn't filled in a servidor yet, so nothing here breaks
     * the working default for an install that hasn't configured SMTP.
     *
     * Why more than a plain config(['mail.mailers.smtp....' => ...]) call:
     * Laravel's Mail facade resolves the 'mail.manager' singleton once per
     * container and MailManager caches every mailer/transport it builds
     * inside itself (Illuminate\Mail\MailManager::$mailers) for the
     * lifetime of that container instance. If anything in this same
     * request/console run already touched Mail:: before this call, the
     * "smtp" transport may already be built and cached from the *old*
     * config, and a later config() change alone would silently do nothing.
     * app()->forgetInstance('mail.manager') discards that cached singleton,
     * so the next time the Mail facade resolves 'mail.manager' the
     * container rebuilds a fresh MailManager — and therefore a fresh "smtp"
     * transport — reading the values just written to config() here. This
     * was verified against Laravel's actual runtime-mailer-config pattern,
     * not assumed.
     */
    public static function aplicarSmtpEmTempoDeExecucao(): void
    {
        $smtp = self::obter('smtp', []);
        $servidor = trim((string) ($smtp['servidor'] ?? ''));

        if ($servidor === '') {
            return;
        }

        $seguranca = strtoupper(trim((string) ($smtp['seguranca'] ?? 'STARTTLS')));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $servidor,
            'mail.mailers.smtp.port' => (int) ($smtp['porta'] ?: 587),
            'mail.mailers.smtp.encryption' => match ($seguranca) {
                'SSL' => 'ssl',
                'NENHUMA', 'NONE', '' => null,
                default => 'tls', // STARTTLS
            },
            'mail.mailers.smtp.username' => $smtp['utilizador'] ?: null,
            'mail.mailers.smtp.password' => $smtp['password'] ?? null,
        ]);

        app()->forgetInstance('mail.manager');
    }
}
