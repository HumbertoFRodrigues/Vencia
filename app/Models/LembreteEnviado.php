<?php

namespace App\Models;

use App\Enums\IntervaloLembrete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembreteEnviado extends Model
{
    use HasFactory;

    protected $table = 'lembretes_enviados';

    protected $fillable = [
        'servico_id', 'intervalo', 'vencimento_referencia', 'enviado_em',
    ];

    protected $casts = [
        'intervalo' => IntervaloLembrete::class,
        'vencimento_referencia' => 'date',
        'enviado_em' => 'datetime',
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
