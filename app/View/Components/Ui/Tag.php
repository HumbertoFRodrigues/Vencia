<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

/** Ported from components/feedback/Tag.jsx. */
class Tag extends Component
{
    public function __construct(
        public string $tone = 'neutral',
        public ?string $icon = null,
    ) {
    }

    public function render(): View
    {
        return view('components.ui.tag');
    }
}
