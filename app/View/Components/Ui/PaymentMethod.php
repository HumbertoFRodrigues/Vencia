<?php

namespace App\View\Components\Ui;

use App\Enums\MetodoPagamento;
use App\Models\MetodoPagamentoOpcao;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Ported from components/data/PaymentMethod.jsx's PAYMENT_METHODS map, then
 * generalised to also resolve any admin-added custom método (see
 * App\Models\MetodoPagamentoOpcao) once "add/edit payment methods" stopped
 * being a fixed 5-case enum. Resolution is a clean 3-step fallback chain, in
 * this order:
 *
 *   1. self::METHODS — the 5 originally well-known codes (mpesa/emola/...),
 *      which keep their real hand-authored logos/tints exactly as before,
 *      regardless of what the admin has since done to their
 *      MetodoPagamentoOpcao row (renamed display, archived, etc.) — this is
 *      the one lookup this whole component existed for originally, so it
 *      stays a static, DB-free fast path.
 *   2. A DB-backed MetodoPagamentoOpcao row matching this método string
 *      (looked up once per request, cached statically) — covers any custom
 *      method the admin has added, including an already-archived one (a past
 *      Pagamento may still reference it, and archived ≠ deleted).
 *   3. A generic fallback (credit-card icon, muted tint, the raw string as
 *      its own label) — covers a método value that matches neither, so this
 *      component never fatals on unexpected data.
 */
class PaymentMethod extends Component
{
    private const METHODS = [
        'mpesa' => ['label' => 'M-Pesa', 'logo' => 'mpesa.png', 'bg' => '#e30613', 'fg' => null, 'icon' => null],
        'emola' => ['label' => 'e-Mola', 'logo' => 'emola.png', 'bg' => '#ee7623', 'fg' => null, 'icon' => null],
        'transferencia' => ['label' => 'Transferência', 'logo' => null, 'bg' => 'var(--surface-sunken)', 'fg' => 'var(--text-body)', 'icon' => 'landmark'],
        'dinheiro' => ['label' => 'Dinheiro', 'logo' => null, 'bg' => 'var(--status-active-bg)', 'fg' => 'var(--status-active-fg)', 'icon' => 'banknote'],
        'outro' => ['label' => 'Outro', 'logo' => null, 'bg' => 'var(--surface-sunken)', 'fg' => 'var(--text-muted)', 'icon' => 'credit-card'],
    ];

    /** @var array<string, MetodoPagamentoOpcao>|null Per-request cache, keyed by nome — populated on first use. */
    private static ?array $opcoes = null;

    public string $method;

    public string $label;

    /** Bare filename under $assetsBase — set only for a self::METHODS match. */
    public ?string $logo;

    /** Fully-qualified URL for a custom uploaded logo — set only for a MetodoPagamentoOpcao match with logo_path. */
    public ?string $logoUrl;

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
        $conhecido = self::METHODS[$this->method] ?? null;

        if ($conhecido !== null) {
            $this->label = $conhecido['label'];
            $this->logo = $conhecido['logo'];
            $this->logoUrl = null;
            $this->bg = $conhecido['bg'];
            $this->fg = $conhecido['fg'];
            $this->icon = $conhecido['icon'];

            return;
        }

        $opcao = self::opcoes()[$this->method] ?? null;

        $this->label = $opcao->nome ?? $this->method;
        $this->logo = null;
        $this->logoUrl = $opcao?->logo_path ? Storage::disk('public')->url($opcao->logo_path) : null;
        $this->bg = 'var(--surface-sunken)';
        $this->fg = 'var(--text-muted)';
        $this->icon = 'credit-card';
    }

    public function render(): View
    {
        return view('components.ui.payment-method');
    }

    /** Human-readable label for a método value (e.g. for CSV/PDF exports), without instantiating the component. */
    public static function labelFor(string $method): string
    {
        if ($conhecido = self::METHODS[$method] ?? null) {
            return $conhecido['label'];
        }

        return self::opcoes()[$method]->nome ?? $method;
    }

    /**
     * All MetodoPagamentoOpcao rows (archived included — an old Pagamento
     * may still reference an archived one), keyed by nome, fetched once per
     * request. Deliberately not filtered to non-archived here: that
     * filtering belongs to the pickers deciding what's *selectable*
     * (MetodoPagamentoOpcao::nomesActivos()), not to this display-only
     * lookup, which must resolve every método a stored record can still hold.
     *
     * @return array<string, MetodoPagamentoOpcao>
     */
    private static function opcoes(): array
    {
        return self::$opcoes ??= MetodoPagamentoOpcao::query()->get()->keyBy('nome')->all();
    }
}
