<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    use HasFactory;

    protected $table = 'configuracoes';

    protected $fillable = [
        'chave', 'valor',
    ];

    protected $casts = [
        'valor' => 'array',
    ];

    public static function obter(string $chave, mixed $default = null): mixed
    {
        return static::query()->where('chave', $chave)->value('valor') ?? $default;
    }
}
