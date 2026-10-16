<?php

namespace App\Models\Concerns;

use App\Models\Conta;
use App\Support\ContaActual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Business rows belong to one conta: every query is scoped to the current
 * conta (App\Support\ContaActual) and every new row is stamped with it, so
 * one customer's clients, services and payments are never visible to another.
 */
trait PertenceAConta
{
    protected static function bootPertenceAConta(): void
    {
        // Fails closed: outside a conta nothing matches, so a command that
        // forgets ContaActual::usar() can never read everybody's rows.
        static::addGlobalScope('conta', function (Builder $query): void {
            $query->where($query->getModel()->getTable().'.conta_id', ContaActual::id() ?? 0);
        });

        static::creating(function ($model): void {
            if (! $model->conta_id) {
                $model->conta_id = ContaActual::id();
            }
        });
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class);
    }
}
