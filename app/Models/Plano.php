<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A plan the super-admin edits in /admin. `limites` holds the counts (null =
 * no limit) and the switches: clientes, servicos, membros, exportar,
 * smtp_proprio.
 */
class Plano extends Model
{
    public const LIMITES = [
        'clientes' => 'Clientes',
        'servicos' => 'Serviços activos',
        'membros' => 'Membros da equipa',
    ];

    public const FUNCOES = [
        'exportar' => 'Exportar CSV e PDF',
        'smtp_proprio' => 'Servidor de email próprio',
    ];

    protected $fillable = ['slug', 'nome', 'descricao', 'preco_mensal', 'preco_anual', 'limites', 'activo', 'ordem'];

    protected function casts(): array
    {
        return [
            'limites' => 'array',
            'activo' => 'boolean',
            'preco_mensal' => 'decimal:2',
            'preco_anual' => 'decimal:2',
        ];
    }

    public function contas(): HasMany
    {
        return $this->hasMany(Conta::class);
    }

    /** The free plan every conta falls back to. Created on the fly if someone deleted it. */
    public static function gratis(): self
    {
        return once(fn () => static::query()->firstOrCreate(['slug' => 'gratis'], [
            'nome' => 'Grátis',
            'limites' => ['clientes' => 10, 'servicos' => 15, 'membros' => 1, 'exportar' => false, 'smtp_proprio' => false],
            'ordem' => 1,
        ]));
    }

    public function gratuito(): bool
    {
        return (float) $this->preco_mensal <= 0 && (float) $this->preco_anual <= 0;
    }

    /** A count limit (null = no limit). */
    public function limite(string $chave): ?int
    {
        $valor = $this->limites[$chave] ?? null;

        return $valor === null || $valor === '' ? null : (int) $valor;
    }

    /** "Clientes sem limite", "Até 10 clientes", "1 membro da equipa". */
    public function textoLimite(string $chave): string
    {
        $limite = $this->limite($chave);
        $nome = self::LIMITES[$chave] ?? $chave;

        return match (true) {
            $limite === null => $nome.' sem limite',
            $limite === 1 => '1 '.mb_strtolower(self::SINGULAR[$chave] ?? $nome),
            default => 'Até '.$limite.' '.mb_strtolower($nome),
        };
    }

    private const SINGULAR = [
        'clientes' => 'Cliente',
        'servicos' => 'Serviço activo',
        'membros' => 'Membro da equipa',
    ];

    public function permite(string $funcao): bool
    {
        return (bool) ($this->limites[$funcao] ?? false);
    }

    public function preco(string $ciclo): float
    {
        return (float) ($ciclo === 'anual' ? $this->preco_anual : $this->preco_mensal);
    }
}
