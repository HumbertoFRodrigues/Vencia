<?php

namespace App\Models;

use App\Enums\MetodoPagamento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'data', 'cliente_id', 'servico_id', 'valor', 'metodo', 'periodo', 'user_id',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'metodo' => MetodoPagamento::class,
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
