<?php

namespace App\Models;

use App\Enums\TipoMovimento;
use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class FixoPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'fixos_pessoais';

    protected $fillable = [
        'tipo', 'descricao', 'categoria_pessoal_id', 'valor', 'dia_mes', 'meio', 'inicio', 'fim', 'notas', 'user_id',
    ];

    protected $casts = [
        'tipo' => TipoMovimento::class,
        'valor' => 'decimal:2',
        'dia_mes' => 'integer',
        'inicio' => 'date',
        'fim' => 'date',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaPessoal::class, 'categoria_pessoal_id');
    }

    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoPessoal::class);
    }

    /** Fixos that apply to the given month: started on or before it and not ended before it. */
    public function scopeVigentesEm(Builder $query, Carbon $mes): Builder
    {
        $inicio = $mes->copy()->startOfMonth()->toDateString();

        return $query->whereDate('inicio', '<=', $inicio)
            ->where(fn (Builder $q) => $q->whereNull('fim')->orWhereDate('fim', '>=', $inicio));
    }

    /** Due date inside the given month, clamped to its last day (dia 31 in February → 28/29). */
    public function dataPrevista(Carbon $mes): Carbon
    {
        $inicio = $mes->copy()->startOfMonth();

        return $inicio->copy()->day(min($this->dia_mes, $inicio->daysInMonth));
    }

    public function aplicaAoMes(Carbon $mes): bool
    {
        $m = $mes->copy()->startOfMonth();

        return $this->inicio->copy()->startOfMonth()->lte($m) && ($this->fim === null || $this->fim->gte($m));
    }

    /** Still running this month (or later). */
    public function emCurso(): bool
    {
        return $this->fim === null || $this->fim->gte(Carbon::today()->startOfMonth());
    }

    /** Starts in a future month. */
    public function futuro(): bool
    {
        return $this->inicio->gt(Carbon::today()->endOfMonth());
    }
}
