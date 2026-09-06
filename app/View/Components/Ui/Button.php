<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Ported from components/core/Button.jsx. VARIANTS/SIZES become
 * .btn--{variant}/.btn--{size} classes in resources/css/components.css.
 */
class Button extends Component
{
    public int $iconSize;

    public function __construct(
        public string $variant = 'secondary',
        public string $size = 'md',
        public ?string $icon = null,
        public ?string $iconEnd = null,
        public bool $fullWidth = false,
        public bool $disabled = false,
        public string $type = 'button',
    ) {
        $this->iconSize = $size === 'lg' ? 17 : 15;
    }

    public function render(): View
    {
        return view('components.ui.button');
    }
}
