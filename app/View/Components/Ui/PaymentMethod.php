<?php

namespace App\View\Components\Ui;

use App\Enums\MetodoPagamento;
use Illuminate\View\Component;
use Illuminate\View\View;

/** Ported from components/data/PaymentMethod.jsx's PAYMENT_METHODS map. */
class PaymentMethod extends Component
{
    private const METHODS = [
        'mpesa' => ['label' => 'M-Pesa', 'logo' => 'mpesa.png', 'bg' => '#e30613', 'fg' => null, 'icon' => null],
        'emola' => ['label' => 'e-Mola', 'logo' => 'emola.png', 'bg' => '#ee7623', 'fg' => null, 'icon' => null],
        'transferencia' => ['label' => 'Transferência', 'logo' => null, 'bg' => 'var(--surface-sunken)', 'fg' => 'var(--text-body)', 'icon' => 'landmark'],
        'dinheiro' => ['label' => 'Dinheiro', 'logo' => null, 'bg' => 'var(--status-active-bg)', 'fg' => 'var(--status-active-fg)', 'icon' => 'banknote'],
        'outro' => ['label' => 'Outro', 'logo' => null, 'bg' => 'var(--surface-sunken)', 'fg' => 'var(--text-muted)', 'icon' => 'credit-card'],
    ];

    public string $method;

    public string $label;

    public ?string $logo;

    public string $bg;

    public ?string $fg;

    public ?string $icon;

    public function __construct(
        string|MetodoPagamento $method = 'outro',
        public int $size = 22,
        public bool $showLabel = true,
        public string $assetsBase = '/assets/logos/',
    ) {
        $this->method = $method instanceof MetodoPagamento ? $method->value : $method;
        $m = self::METHODS[$this->method] ?? self::METHODS['outro'];

        $this->label = $m['label'];
        $this->logo = $m['logo'];
        $this->bg = $m['bg'];
        $this->fg = $m['fg'];
        $this->icon = $m['icon'];
    }

    public function render(): View
    {
        return view('components.ui.payment-method');
    }

    /** Human-readable label for a método value (e.g. for CSV exports), without instantiating the component. */
    public static function labelFor(string $method): string
    {
        return self::METHODS[$method]['label'] ?? self::METHODS['outro']['label'];
    }
}
