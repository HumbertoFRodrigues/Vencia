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

    /**
     * The admin (empresa) always gets a copy of every client-facing email —
     * that's the whole point of the reminder engine from the admin's side,
     * the client copy is the secondary/optional part. Returns an empty array
     * (a no-op for Mail::bcc()) when no admin address is configured, or when
     * it's identical to the client's own address (avoids a redundant BCC to
     * the same inbox the mail is already going to).
     *
     * @return list<string>
     */
    public static function emailBccAdmin(?string $emailCliente = null): array
    {
        $admin = self::obter('empresa', [])['email'] ?? null;

        if (! $admin || $admin === $emailCliente) {
            return [];
        }

        return [$admin];
    }
}
