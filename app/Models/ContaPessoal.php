<?php

namespace App\Models;

use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Model;

/** Opening balance of one personal account (identified by its payment method / meio). */
class ContaPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'contas_pessoais';

    protected $fillable = ['meio', 'saldo_inicial', 'user_id'];

    protected $casts = ['saldo_inicial' => 'decimal:2'];
}
