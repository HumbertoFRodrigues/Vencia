<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicoLembreteConfig extends Model
{
    use HasFactory;

    protected $table = 'servico_lembrete_config';

    protected $fillable = [
        'servico_id', 'd30', 'd15', 'd7', 'd3', 'd1', 'no_dia', 'apos_vencimento',
    ];

    protected $casts = [
        'd30' => 'boolean',
        'd15' => 'boolean',
        'd7' => 'boolean',
        'd3' => 'boolean',
        'd1' => 'boolean',
        'no_dia' => 'boolean',
        'apos_vencimento' => 'boolean',
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
