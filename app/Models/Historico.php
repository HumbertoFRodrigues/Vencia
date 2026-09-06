<?php

namespace App\Models;

use App\Enums\HistoricoKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Append-only audit log. Rows are created and never changed: boot() aborts
 * any update/delete attempt so this holds even if some future code forgets
 * and calls ->update() or ->delete() on a Historico instance.
 */
class Historico extends Model
{
    use HasFactory;

    protected $table = 'historicos';

    protected $fillable = [
        'cliente_id', 'servico_id', 'occurred_at', 'title', 'description', 'kind',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'kind' => HistoricoKind::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (): void {
            throw new RuntimeException('Historico é imutável: registos existentes não podem ser alterados.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Historico é imutável: registos existentes não podem ser apagados.');
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
