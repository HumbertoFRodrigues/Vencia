<?php

namespace App\Models;

use App\Models\Concerns\PertenceAConta;
use App\Support\ContaActual;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

/**
 * Key/value settings. Rows with a conta_id belong to that business (empresa,
 * lembretes, templates, its own SMTP); rows without one are the platform's
 * (its SMTP, payment instructions, sign-up options), read with plataforma().
 */
class Configuracao extends Model
{
    use HasFactory, PertenceAConta;

    protected $table = 'configuracoes';

    protected $fillable = [
        'conta_id', 'chave', 'valor',
    ];

    protected $casts = [
        'valor' => 'array',
    ];

    public static function obter(string $chave, mixed $default = null): mixed
    {
        return static::query()->where('chave', $chave)->value('valor') ?? $default;
    }

    public static function guardar(string $chave, mixed $valor): void
    {
        static::query()->updateOrCreate(['chave' => $chave], ['valor' => $valor]);
    }

    public static function plataforma(string $chave, mixed $default = null): mixed
    {
        return static::withoutGlobalScope('conta')->whereNull('conta_id')->where('chave', $chave)->value('valor') ?? $default;
    }

    public static function guardarPlataforma(string $chave, mixed $valor): void
    {
        static::withoutEvents(fn () => static::withoutGlobalScope('conta')
            ->updateOrCreate(['conta_id' => null, 'chave' => $chave], ['valor' => $valor]));
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

    /** The conta's own SMTP when its plan allows one and it is filled in; otherwise the platform's. */
    public static function smtpEfectivo(): array
    {
        $proprio = self::obter('smtp', []);
        $conta = ContaActual::get();

        if (trim((string) ($proprio['servidor'] ?? '')) !== '' && $conta?->planoEfectivo()->permite('smtp_proprio')) {
            return $proprio + ['proprio' => true];
        }

        return self::plataforma('smtp', []) + ['proprio' => false];
    }

    /** False when mail would only be written to laravel.log (no SMTP anywhere and .env still on "log"). */
    public static function envioDisponivel(): bool
    {
        return self::temEnvio(self::smtpEfectivo()) || config('mail.default') !== 'log';
    }

    /** A saved mail setting that actually sends: an SMTP server, or PHP's mail() ("modo" => "php"). */
    public static function temEnvio(array $smtp): bool
    {
        return ($smtp['modo'] ?? 'smtp') === 'php' || trim((string) ($smtp['servidor'] ?? '')) !== '';
    }

    /** Default sender for PHP's mail(): noreply@ the site's own domain. */
    public static function remetentePorOmissao(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return 'noreply@'.preg_replace('/^www\./', '', $host);
    }

    /**
     * Points the mailer at the right SMTP for the rest of this request or
     * command step — call it right before any Mail::...->send(). Emails from
     * a business carry the business name as sender and reply to the business
     * email, even when they leave through the platform's server.
     *
     * Why more than a plain config() call: Laravel's MailManager caches every
     * transport it builds for the lifetime of the container, so a transport
     * built before this call would keep the old settings.
     * app()->forgetInstance('mail.manager') makes the next Mail:: call build
     * a fresh one from the values written here.
     */
    public static function aplicarSmtpEmTempoDeExecucao(): void
    {
        $empresa = ContaActual::get() ? self::obter('empresa', []) : [];

        self::aplicarSmtp(self::smtpEfectivo(), $empresa['nome'] ?? null, $empresa['email'] ?? null);
    }

    /** For the platform's own emails (sign-up, password reset, plan invoices, alerts). */
    public static function aplicarSmtpDaPlataforma(): void
    {
        self::aplicarSmtp(self::plataforma('smtp', []), config('app.name'), null);
    }

    private static function aplicarSmtp(array $smtp, ?string $nome, ?string $responderPara): void
    {
        // A command walking several contas must not carry one conta's server
        // or sender into the next: start each switch from the .env mailer.
        if (! config()->has('mail.original')) {
            config(['mail.original' => ['default' => config('mail.default'), 'from' => config('mail.from.address')]]);
        }
        config(['mail.default' => config('mail.original.default'), 'mail.from.address' => config('mail.original.from')]);

        $servidor = trim((string) ($smtp['servidor'] ?? ''));

        if (($smtp['modo'] ?? 'smtp') === 'php') {
            // The hosting's own mail server through PHP's mail() (App\Mail\TransportePhpMail): no account needed.
            config([
                'mail.default' => 'phpmail',
                'mail.mailers.phpmail' => ['transport' => 'phpmail'],
                'mail.from.address' => trim((string) ($smtp['remetente'] ?? '')) ?: self::remetentePorOmissao(),
            ]);
            app()->forgetInstance('mail.manager');
        } elseif ($servidor !== '') {
            $seguranca = strtoupper(trim((string) ($smtp['seguranca'] ?? 'STARTTLS')));

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $servidor,
                'mail.mailers.smtp.port' => (int) (($smtp['porta'] ?? null) ?: 587),
                'mail.mailers.smtp.encryption' => match ($seguranca) {
                    'SSL' => 'ssl',
                    'NENHUMA', 'NONE', '' => null,
                    default => 'tls', // STARTTLS
                },
                'mail.mailers.smtp.username' => ($smtp['utilizador'] ?? null) ?: null,
                'mail.mailers.smtp.password' => self::passwordSmtp($smtp['password'] ?? null),
            ]);

            $remetente = trim((string) ($smtp['remetente'] ?? ''));
            if ($remetente === '' && filter_var($smtp['utilizador'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $remetente = $smtp['utilizador'];
            }
            if ($remetente !== '') {
                config(['mail.from.address' => $remetente]);
            }

            app()->forgetInstance('mail.manager');
        }

        // Sender name and reply-to go straight onto the mailer in use, so they
        // change per conta without rebuilding the transport each time.
        Mail::alwaysFrom(config('mail.from.address'), $nome ?: config('app.name'));
        Mail::alwaysReplyTo($responderPara, $responderPara ? $nome : null);
    }

    /** Saved passwords are encrypted; one saved before encryption existed is still read as-is. */
    private static function passwordSmtp(?string $guardada): ?string
    {
        if ($guardada === null || $guardada === '') {
            return null;
        }

        try {
            return Crypt::decryptString($guardada);
        } catch (DecryptException) {
            return $guardada;
        }
    }
}
