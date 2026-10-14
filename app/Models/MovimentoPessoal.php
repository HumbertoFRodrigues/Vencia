<?php

namespace App\Models;

use App\Enums\TipoMovimento;
use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MovimentoPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'movimentos_pessoais';

    protected $fillable = [
        'tipo', 'descricao', 'categoria_pessoal_id', 'valor', 'data', 'meio',
        'fixo_pessoal_id', 'retirada_id', 'competencia', 'ignorado', 'notas', 'user_id',
    ];

    protected $casts = [
        'tipo' => TipoMovimento::class,
        'valor' => 'decimal:2',
        'data' => 'date',
        'ignorado' => 'boolean',
    ];

    /**
     * Stored as a bare Y-m-d (not the default "Y-m-d H:i:s" date-cast format),
     * so the equality/range lookups on it behave the same on MySQL and SQLite.
     */
    protected function competencia(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor === null ? null : Carbon::parse($valor)->startOfDay(),
            set: fn ($valor) => $valor === null ? null : Carbon::parse($valor)->startOfMonth()->toDateString(),
        );
    }

    protected static function booted(): void
    {
        // A fixo's occurrence keeps the month it was confirmed for; any other
        // movimento always belongs to the month of its date.
        static::saving(function (self $m): void {
            if ($m->fixo_pessoal_id === null || $m->competencia === null) {
                $m->competencia = $m->data->copy()->startOfMonth();
            }
        });
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaPessoal::class, 'categoria_pessoal_id');
    }

    public function fixo(): BelongsTo
    {
        return $this->belongsTo(FixoPessoal::class, 'fixo_pessoal_id');
    }

    /** Set when this entrada was created by a withdrawal from the business (managed there, not here). */
    public function retirada(): BelongsTo
    {
        return $this->belongsTo(Retirada::class);
    }

    /** Real money only — a fixo "ignored" for one month is a marker row, not money. */
    public function scopeContabilizados(Builder $query): Builder
    {
        return $query->where('ignorado', false);
    }

    public function scopeNoMes(Builder $query, Carbon $mes): Builder
    {
        return $query->whereDate('competencia', $mes->copy()->startOfMonth()->toDateString());
    }

    /** Months $de..$ate inclusive. */
    public function scopeEntreMeses(Builder $query, Carbon $de, Carbon $ate): Builder
    {
        return $query->whereBetween('competencia', [
            $de->copy()->startOfMonth()->toDateString(),
            $ate->copy()->startOfMonth()->toDateString(),
        ]);
    }
}
