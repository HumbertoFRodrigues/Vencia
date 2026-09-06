<?php

namespace App\View\Components\Ui;

use App\Enums\ServicoStatus;
use Illuminate\View\Component;
use Illuminate\View\View;

/** Ported from components/feedback/StatusBadge.jsx's STATUS map. */
class StatusBadge extends Component
{
    private const LABELS = [
        'activo' => 'Activo',
        'a_vencer' => 'A vencer',
        'vencido' => 'Vencido',
        'suspenso' => 'Suspenso',
        'cancelado' => 'Cancelado',
    ];

    public string $status;

    public string $resolvedLabel;

    public function __construct(
        string|ServicoStatus $status = 'activo',
        public ?string $label = null,
        public string $size = 'md',
    ) {
        $this->status = $status instanceof ServicoStatus ? $status->value : $status;
        $this->resolvedLabel = $label ?? self::LABELS[$this->status] ?? self::LABELS['activo'];
    }

    public function render(): View
    {
        return view('components.ui.status-badge');
    }
}
