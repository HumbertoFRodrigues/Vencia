<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A request to pay for a plan. Manual flow: pendente (instructions shown) →
 * em_analise (the customer sent the reference/proof) → paga or rejeitada by
 * the super-admin. An automatic gateway later jumps straight to paga.
 */
class Fatura extends Model
{
    public const PENDENTE = 'pendente';

    public const EM_ANALISE = 'em_analise';

    public const PAGA = 'paga';

    public const REJEITADA = 'rejeitada';

    public const CANCELADA = 'cancelada';

    protected $fillable = [
        'conta_id', 'plano_id', 'ciclo', 'valor', 'estado', 'metodo', 'referencia', 'comprovativo_path',
        'gateway', 'gateway_ref', 'nota', 'periodo_inicio', 'periodo_fim', 'pedida_por', 'aprovada_por', 'enviada_em', 'paga_em',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'periodo_inicio' => 'date',
            'periodo_fim' => 'date',
            'enviada_em' => 'datetime',
            'paga_em' => 'datetime',
        ];
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function pedidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pedida_por');
    }

    public function aprovadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovada_por');
    }

    public function referenciaInterna(): string
    {
        return 'VEN-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function metodoLabel(): ?string
    {
        return match ($this->metodo) {
            null => null,
            'mpesa' => 'M-Pesa',
            'emola' => 'e-Mola',
            'transferencia' => 'Transferência',
            'outro' => 'Outro',
            default => $this->metodo,
        };
    }

    public function aberta(): bool
    {
        return in_array($this->estado, [self::PENDENTE, self::EM_ANALISE], true);
    }

    public function estadoLabel(): string
    {
        return match ($this->estado) {
            self::PENDENTE => 'À espera do pagamento',
            self::EM_ANALISE => 'Em análise',
            self::PAGA => 'Paga',
            self::REJEITADA => 'Rejeitada',
            self::CANCELADA => 'Cancelada',
            default => $this->estado,
        };
    }

    public function estadoTom(): string
    {
        return match ($this->estado) {
            self::PAGA => 'success',
            self::EM_ANALISE => 'info',
            self::PENDENTE => 'warning',
            default => 'neutral',
        };
    }
}
