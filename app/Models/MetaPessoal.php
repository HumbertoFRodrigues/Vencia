<?php

namespace App\Models;

use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A savings goal; its progress is the sum of the contributions (saídas) linked to it. */
class MetaPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'metas_pessoais';

    protected $fillable = ['nome', 'valor_alvo', 'data_alvo', 'concluida_em', 'user_id'];

    protected $casts = [
        'valor_alvo' => 'decimal:2',
        'data_alvo' => 'date',
        'concluida_em' => 'datetime',
    ];

    public function contribuicoes(): HasMany
    {
        return $this->hasMany(MovimentoPessoal::class)->where('ignorado', false);
    }
}
