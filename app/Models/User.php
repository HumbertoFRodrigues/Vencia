<?php

namespace App\Models;

use App\Enums\Papel;
use App\Mail\PlataformaMail;
use App\Mail\ResetPasswordMail;
use App\Support\Totp;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'dois_passos_segredo', 'dois_passos_recuperacao'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dois_passos_segredo' => 'encrypted',
            'dois_passos_recuperacao' => 'encrypted:array',
            'dois_passos_confirmado_em' => 'datetime',
            'dois_passos_ultimo_passo' => 'integer',
            'super_admin' => 'boolean',
            'bloqueado_em' => 'datetime',
        ];
    }

    /** @var array<int, Papel|null> */
    private array $papeis = [];

    public function contas(): BelongsToMany
    {
        return $this->belongsToMany(Conta::class, 'conta_user')->withPivot('papel')->withTimestamps();
    }

    /** The conta last chosen in the switcher, or the first one the user belongs to. */
    public function contaActual(): ?Conta
    {
        $conta = $this->conta_actual_id ? $this->contas()->whereKey($this->conta_actual_id)->first() : null;

        return $conta ?? $this->contas()->orderBy('conta_user.created_at')->orderBy('conta_user.id')->first();
    }

    public function papelNa(Conta $conta): ?Papel
    {
        if (! array_key_exists($conta->id, $this->papeis)) {
            $papel = $this->contas()->whereKey($conta->id)->first()?->pivot?->papel;
            $this->papeis[$conta->id] = $papel ? Papel::from($papel) : null;
        }

        return $this->papeis[$conta->id];
    }

    public function esquecerPapeis(): void
    {
        $this->papeis = [];
    }

    public function bloqueado(): bool
    {
        return $this->bloqueado_em !== null;
    }

    /** The account confirmation email, in pt-PT and sent through the platform's SMTP. */
    public function sendEmailVerificationNotification(): void
    {
        $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addHours(48), [
            'id' => $this->getKey(),
            'hash' => sha1($this->getEmailForVerification()),
        ]);

        Configuracao::aplicarSmtpDaPlataforma();

        Mail::to($this->getEmailForVerification())->send(new PlataformaMail('Confirma o teu email', [
            'Olá, '.$this->name.'.',
            'Para activar a tua conta Vencia, abre esta ligação (é válida durante 48 horas):',
        ], $url));
    }

    public function temDoisPassos(): bool
    {
        return $this->dois_passos_confirmado_em !== null && $this->dois_passos_segredo !== null;
    }

    /**
     * Accepts an authenticator code (never the same one twice) or an unused
     * recovery code (consumed on use).
     */
    public function validarSegundoPasso(string $codigo): bool
    {
        if (! $this->temDoisPassos()) {
            return false;
        }

        $passo = Totp::verificar($this->dois_passos_segredo, $codigo, $this->dois_passos_ultimo_passo);
        if ($passo !== null) {
            $this->forceFill(['dois_passos_ultimo_passo' => $passo])->save();

            return true;
        }

        $normalizado = strtolower(preg_replace('/[^a-z0-9]/i', '', $codigo));
        $restantes = collect($this->dois_passos_recuperacao ?? []);
        $usado = $restantes->first(fn (string $c) => hash_equals(strtolower(str_replace('-', '', $c)), $normalizado));

        if ($normalizado === '' || $usado === null) {
            return false;
        }

        $this->forceFill(['dois_passos_recuperacao' => $restantes->reject(fn ($c) => $c === $usado)->values()->all()])->save();

        return true;
    }

    /** @return list<string> 8 fresh single-use recovery codes like "k3f9q-2mxp7" */
    public static function gerarCodigosRecuperacao(): array
    {
        return collect(range(1, 8))
            ->map(fn () => strtolower(Str::random(5)).'-'.strtolower(Str::random(5)))
            ->all();
    }

    /**
     * Overrides Laravel's default (English-language) ResetPassword
     * notification with the app's own Mailable, so the reset email stays
     * pt-PT and matches this app's plain, factual voice — see
     * App\Mail\ResetPasswordMail.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        $expiraEmMinutos = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        // The platform's own mailer (an account email, not one from a business),
        // never the log driver that would write the reset link to disk.
        Configuracao::aplicarSmtpDaPlataforma();

        Mail::to($this->getEmailForPasswordReset())->send(new ResetPasswordMail($url, $expiraEmMinutos));
    }
}
