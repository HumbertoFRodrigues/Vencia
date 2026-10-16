<?php

namespace App\Models;

use App\Enums\Papel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A workspace: one business (clients, services, payments, settings) shared by
 * its members. Each member's personal panel stays private to them. The plan
 * is kept here (plano_id until plano_ate, no end when null); faturas are
 * the history of how it was paid for.
 */
class Conta extends Model
{
    public const ACTIVA = 'activa';

    public const SUSPENSA = 'suspensa';

    protected $table = 'contas';

    protected $fillable = ['nome', 'estado', 'motivo_suspensao', 'plano_id', 'plano_ate', 'em_teste', 'proximo_recibo', 'avisos_plano'];

    protected $attributes = ['estado' => self::ACTIVA, 'proximo_recibo' => 1, 'em_teste' => false];

    protected function casts(): array
    {
        return [
            'plano_ate' => 'date',
            'em_teste' => 'boolean',
            'avisos_plano' => 'array',
        ];
    }

    public function membros(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conta_user')->withPivot('papel')->withTimestamps();
    }

    public function dono(): ?User
    {
        return $this->membros()->wherePivot('papel', Papel::Dono->value)->orderBy('conta_user.created_at')->first();
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function convites(): HasMany
    {
        return $this->hasMany(Convite::class);
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class);
    }

    /** Admin counts across contas: these relations skip the current-conta scope on purpose. */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class)->withoutGlobalScope('conta');
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class)->withoutGlobalScope('conta');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class)->withoutGlobalScope('conta');
    }

    public function suspensa(): bool
    {
        return $this->estado === self::SUSPENSA;
    }

    /** The plan in force today: a paid plan whose date has passed counts as Grátis until the daily job tidies it up. */
    public function planoEfectivo(): Plano
    {
        if ($this->plano && ! $this->planoExpirado()) {
            return $this->plano;
        }

        return Plano::gratis();
    }

    public function planoExpirado(): bool
    {
        return $this->plano_ate !== null && $this->plano_ate->lt(Carbon::today());
    }

    /** The next receipt number, taken under a row lock so two payments never share one. */
    public function reservarNumeroRecibo(): int
    {
        return DB::transaction(function (): int {
            $conta = static::query()->whereKey($this->id)->lockForUpdate()->first();
            $numero = (int) $conta->proximo_recibo;
            $conta->forceFill(['proximo_recibo' => $numero + 1])->save();
            $this->proximo_recibo = $numero + 1;

            return $numero;
        });
    }
}
