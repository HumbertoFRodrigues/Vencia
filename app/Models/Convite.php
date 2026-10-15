<?php

namespace App\Models;

use App\Enums\Papel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** An emailed invitation to join a conta's team. Only a hash of the link's token is stored. */
class Convite extends Model
{
    protected $fillable = ['conta_id', 'email', 'papel', 'token', 'convidado_por', 'expira_em', 'aceite_em'];

    protected function casts(): array
    {
        return [
            'papel' => Papel::class,
            'expira_em' => 'datetime',
            'aceite_em' => 'datetime',
        ];
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class);
    }

    public function convidadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'convidado_por');
    }

    public static function porToken(string $token): ?self
    {
        return static::query()->where('token', hash('sha256', $token))->first();
    }

    public function valido(): bool
    {
        return $this->aceite_em === null && $this->expira_em->isFuture();
    }

    public function scopePendentes($query)
    {
        return $query->whereNull('aceite_em')->where('expira_em', '>', Carbon::now());
    }
}
