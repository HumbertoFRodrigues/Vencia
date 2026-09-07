<?php

namespace App\View\Components\Ui;

use App\Enums\ServicoCategoria;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Ported from components/data/ServiceLogo.jsx. LOGO_LIBRARY is empty today
 * (same as the source .jsx — the readme's larger list is stale), so every
 * service falls back to the category-tinted monogram. Drop a file into
 * public/assets/logos/<file> and add one line to LOGO_LIBRARY to switch a
 * named service over to a real logo everywhere it renders.
 */
class ServiceLogo extends Component
{
    /** @var array<string, string> lowercase service name => file name in assetsBase */
    private const LOGO_LIBRARY = [];

    public string $category;

    public ?string $url;

    public string $bgVar;

    public string $fgVar;

    public string $categoryIcon;

    public string $initials;

    public function __construct(
        public string $name = '',
        string|ServicoCategoria $category = 'outro',
        public ?string $src = null,
        public int $size = 40,
        public string $assetsBase = '/assets/logos/',
    ) {
        $this->category = $category instanceof ServicoCategoria ? $category->value : $category;

        $categoriaEnum = ServicoCategoria::tryFrom($this->category) ?? ServicoCategoria::Outro;
        $this->category = $categoriaEnum->value;

        $this->bgVar = "--cat-{$this->category}-bg";
        $this->fgVar = "--cat-{$this->category}-fg";
        $this->categoryIcon = $categoriaEnum->icon();
        $this->url = $src ?: self::resolveLogo($name, $assetsBase);
        $this->initials = self::initialsFor($name);
    }

    public static function resolveLogo(string $name, string $assetsBase = '/assets/logos/'): ?string
    {
        $key = mb_strtolower(trim($name));

        if ($key === '') {
            return null;
        }

        $file = self::LOGO_LIBRARY[$key] ?? null;

        if ($file === null) {
            foreach (self::LOGO_LIBRARY as $prefix => $candidate) {
                if (str_starts_with($key, $prefix)) {
                    $file = $candidate;
                    break;
                }
            }
        }

        if ($file === null) {
            return null;
        }

        return preg_match('#^https?://#', $file) ? $file : $assetsBase.$file;
    }

    private static function initialsFor(string $name): string
    {
        $clean = preg_replace('/[^\p{L}\p{N} ]/u', '', $name) ?? '';
        $words = array_values(array_filter(preg_split('/\s+/u', trim($clean)) ?: []));
        $letters = array_map(fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice($words, 0, 2));

        return implode('', $letters);
    }

    public function render(): View
    {
        return view('components.ui.service-logo');
    }
}
