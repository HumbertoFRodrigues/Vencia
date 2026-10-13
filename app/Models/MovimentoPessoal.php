<?php

namespace App\Models;

use App\Enums\TipoMovimento;
use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MovimentoPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'movimentos_pessoais';

    protected $fillable = [
        'tipo', 'descricao', 'categoria_pessoal_id', 'valor', 'data', 'meio',
        'fixo_pessoal_id', 'competencia', 'ignorado', 'notas', 'user_id',
    ];

    protected $casts = [
        'tipo' => TipoMovimento::class,
        'valor' => 'decimal:2',
        'data' => 'date',
        'competencia' => 'date',
        'ignorado' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaPessoal::class, 'categoria_pessoal_id');
    }

    public function fixo(): BelongsTo
    {
        return $this->belongsTo(FixoPessoal::class, 'fixo_pessoal_id');
    }

    /** Real money only — a fixo "ignored" for one month is a marker row, not money. */
    public function scopeContabilizados(Builder $query): Builder
    {
        return $query->where('ignorado', false);
    }

    public function scopeNoMes(Builder $query, Carbon $mes): Builder
    {
        return $query->whereBetween('data', [
            $mes->copy()->startOfMonth()->toDateString(),
            $mes->copy()->endOfMonth()->toDateString(),
        ]);
    }
}
