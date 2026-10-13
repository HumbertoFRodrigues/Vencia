<?php

namespace App\Models;

use App\Enums\TipoMovimento;
use App\Models\Concerns\PertenceAoUtilizador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaPessoal extends Model
{
    use PertenceAoUtilizador;

    protected $table = 'categorias_pessoais';

    /** Tint key => [background, foreground]; reuses the theme-aware category tint tokens. */
    public const CORES = [
        'azul' => ['var(--cat-dominio-bg)', 'var(--cat-dominio-fg)'],
        'indigo' => ['var(--cat-hospedagem-bg)', 'var(--cat-hospedagem-fg)'],
        'violeta' => ['var(--cat-email-bg)', 'var(--cat-email-fg)'],
        'verde' => ['var(--cat-ia-bg)', 'var(--cat-ia-fg)'],
        'ambar' => ['var(--cat-manutencao-bg)', 'var(--cat-manutencao-fg)'],
        'vermelho' => ['var(--status-overdue-bg)', 'var(--status-overdue-fg)'],
        'rosa' => ['var(--cat-marketing-bg)', 'var(--cat-marketing-fg)'],
        'teal' => ['var(--cat-design-bg)', 'var(--cat-design-fg)'],
        'ciano' => ['var(--cat-seguranca-bg)', 'var(--cat-seguranca-fg)'],
        'castanho' => ['var(--cat-consultoria-bg)', 'var(--cat-consultoria-fg)'],
        'aco' => ['var(--cat-telefonia-bg)', 'var(--cat-telefonia-fg)'],
        'cinza' => ['var(--cat-outro-bg)', 'var(--cat-outro-fg)'],
    ];

    public const ICONES = [
        'house', 'utensils', 'shopping-cart', 'car', 'fuel', 'bus', 'zap', 'droplet', 'wifi', 'smartphone',
        'heart-pulse', 'graduation-cap', 'users', 'baby', 'shirt', 'gamepad-2', 'plane', 'gift', 'church',
        'credit-card', 'landmark', 'piggy-bank', 'briefcase', 'banknote', 'hand-coins', 'chart-line',
        'sparkles', 'wrench', 'receipt', 'package',
    ];

    protected $fillable = ['nome', 'tipo', 'icone', 'cor', 'limite_mensal', 'arquivado', 'ordem', 'user_id'];

    protected $casts = [
        'tipo' => TipoMovimento::class,
        'limite_mensal' => 'decimal:2',
        'arquivado' => 'boolean',
    ];

    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoPessoal::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('arquivado', false);
    }

    public function scopeDoTipo(Builder $query, TipoMovimento $tipo): Builder
    {
        return $query->where('tipo', $tipo->value);
    }

    /** @return array{0: string, 1: string} */
    public function tinta(): array
    {
        return self::CORES[$this->cor] ?? self::CORES['cinza'];
    }

    /**
     * Active categories of one tipo, plus $incluirId even if archived (so
     * editing an old movimento never silently drops its category).
     *
     * @return list<array{value: string, label: string}>
     */
    public static function opcoes(TipoMovimento $tipo, ?int $incluirId = null): array
    {
        return self::query()
            ->doTipo($tipo)
            ->where(fn (Builder $q) => $q->where('arquivado', false)->when($incluirId, fn ($q2) => $q2->orWhere('id', $incluirId)))
            ->orderBy('ordem')->orderBy('nome')
            ->get()
            ->map(fn (self $c) => ['value' => (string) $c->id, 'label' => $c->nome])
            ->all();
    }
}
