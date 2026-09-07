<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The admin-managed, database-backed list of payment method names — named
 * "...Opcao" (option) rather than plain "MetodoPagamento" to avoid colliding
 * with the pre-existing App\Enums\MetodoPagamento backed enum, which is kept
 * around unchanged as a pure icon/logo/label lookup for the 5 originally
 * "well-known" methods (mpesa/emola/transferencia/dinheiro/outro — the ones
 * with real hand-authored artwork, e.g. assets/logos/mpesa.png). This model
 * is the actual source of truth for "which methods exist and are selectable
 * today", including any custom one the admin adds via Configurações; the
 * enum never grows new cases for those.
 *
 * `nome` doubles as both the row's identity and the exact string persisted
 * on pagamentos.metodo / servicos.metodo_habitual (see the migration's
 * docblock) — there is deliberately no separate slug/label column, mirroring
 * biblioteca_servicos' single `nome` column.
 */
class MetodoPagamentoOpcao extends Model
{
    use HasFactory;

    protected $table = 'metodos_pagamento';

    protected $fillable = [
        'nome', 'logo_path', 'arquivado',
    ];

    protected $casts = [
        'arquivado' => 'boolean',
    ];

    /**
     * Every non-archived method's `nome`, ordered by creation (id) so the 5
     * seeded well-known methods keep their original mpesa/emola/.../outro
     * order and any custom addition simply appends after them — used
     * everywhere a método picker/pill-strip needs "all currently selectable
     * methods" instead of a hardcoded 5-item array.
     *
     * @return list<string>
     */
    public static function nomesActivos(): array
    {
        return self::query()->where('arquivado', false)->orderBy('id')->pluck('nome')->all();
    }
}
