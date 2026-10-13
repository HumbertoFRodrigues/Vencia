<?php

namespace App\Models;

use App\Enums\TipoMovimento;
use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class FixoPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'fixos_pessoais';

    protected $fillable = [
        'tipo', 'descricao', 'categoria_pessoal_id', 'valor', 'dia_mes', 'meio', 'inicio', 'activo', 'notas', 'user_id',
    ];

    protected $casts = [
        'tipo' => TipoMovimento::class,
        'valor' => 'decimal:2',
        'dia_mes' => 'integer',
        'inicio' => 'date',
        'activo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaPessoal::class, 'categoria_pessoal_id');
    }

    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoPessoal::class);
    }

    /** Due date inside the given month, clamped to its last day (dia 31 in February → 28/29). */
    public function dataPrevista(Carbon $mes): Carbon
    {
        $inicio = $mes->copy()->startOfMonth();

        return $inicio->copy()->day(min($this->dia_mes, $inicio->daysInMonth));
    }

    public function aplicaAoMes(Carbon $mes): bool
    {
        return $this->activo && $this->inicio->copy()->startOfMonth()->lte($mes->copy()->startOfMonth());
    }
}
