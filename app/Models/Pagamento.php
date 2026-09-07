<?php

namespace App\Models;

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
        // 'metodo' is intentionally a plain string, not an enum cast: the set
        // of valid métodos is now admin-managed via MetodoPagamentoOpcao
        // (database-backed), not a fixed PHP enum, so any string the admin
        // has configured (or once configured, even if since archived) must be
        // allowed through untouched. See App\View\Components\Ui\PaymentMethod
        // for how a raw método string is resolved to a label/icon/logo.
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
