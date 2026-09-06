<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Ported from components/core/Icon.jsx — masks a Lucide SVG over `currentColor`
 * via CSS mask-image. Prefers the vendored copy in public/icons/<name>.svg
 * (see public/icons/) and falls back to the live unpkg CDN so a typo or a
 * name that wasn't vendored degrades instead of breaking the mask entirely.
 */
class Icon extends Component
{
    private const CDN_BASE = 'https://unpkg.com/lucide-static@0.454.0/icons/';

    public string $url;

    public function __construct(
        public string $name,
        public int|string $size = 16,
        public string $color = 'currentColor',
    ) {
        $relative = 'icons/'.$name.'.svg';

        $this->url = is_file(public_path($relative))
            ? asset($relative)
            : self::CDN_BASE.$name.'.svg';
    }

    public function render(): View
    {
        return view('components.ui.icon');
    }
}
