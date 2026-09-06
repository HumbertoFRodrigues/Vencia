<?php

namespace App\Models;

use App\Enums\SuspensaoMotivo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suspensao extends Model
{
    use HasFactory;

    protected $table = 'suspensoes';

    protected $fillable = [
        'servico_id', 'data', 'motivo', 'observacao', 'enviou_email', 'user_id',
    ];

    protected $casts = [
        'data' => 'date',
        'motivo' => SuspensaoMotivo::class,
        'enviou_email' => 'boolean',
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
