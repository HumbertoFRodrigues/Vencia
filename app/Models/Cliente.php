<?php

namespace App\Models;

use App\Enums\ClienteStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'email', 'tel', 'empresa', 'status', 'desde',
    ];

    protected $casts = [
        'status' => ClienteStatus::class,
        'desde' => 'date',
    ];

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(Historico::class);
    }
}
