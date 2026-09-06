<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

/** Ported from components/core/IconButton.jsx. */
class IconButton extends Component
{
    public int $iconSize;

    public function __construct(
        public string $icon,
        public string $size = 'md',
        public string $variant = 'ghost',
        public ?string $label = null,
        public bool $disabled = false,
        public string $type = 'button',
    ) {
        $this->iconSize = $size === 'sm' ? 15 : 17;
    }

    public function render(): View
    {
        return view('components.ui.icon-button');
    }
}
