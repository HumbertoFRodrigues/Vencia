<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personal-finance rows are private to whoever is logged in: every query is
 * scoped to auth()->id() and every new row is stamped with it, so a second
 * admin account (the business side is shared) never sees someone else's money.
 */
trait PertenceAoUtilizador
{
    protected static function bootPertenceAoUtilizador(): void
    {
        static::addGlobalScope('utilizador', function (Builder $query): void {
            if (auth()->check()) {
                $query->where($query->getModel()->getTable().'.user_id', auth()->id());
            }
        });

        static::creating(function ($model): void {
            if (! $model->user_id && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
