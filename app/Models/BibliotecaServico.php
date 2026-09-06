<?php

namespace App\Models;

use App\Enums\ServicoCategoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibliotecaServico extends Model
{
    use HasFactory;

    protected $table = 'biblioteca_servicos';

    protected $fillable = [
        'nome', 'categoria', 'logo_path', 'descricao_padrao', 'arquivado',
    ];

    protected $casts = [
        'categoria' => ServicoCategoria::class,
        'arquivado' => 'boolean',
    ];

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class);
    }
}
