<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** What super-admins (and the billing engine) did to contas, users, plans and invoices. Append-only. */
class ActividadeAdmin extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'actividade_admin';

    protected $fillable = ['user_id', 'accao', 'alvo', 'detalhe'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function registar(string $accao, ?string $alvo = null, ?string $detalhe = null): void
    {
        static::create([
            'user_id' => session('admin_original_id') ?? auth()->id(),
            'accao' => $accao,
            'alvo' => $alvo,
            'detalhe' => $detalhe ? mb_strimwidth($detalhe, 0, 1000, '…') : null,
            'created_at' => Carbon::now(),
        ]);
    }
}
