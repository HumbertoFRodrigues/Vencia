<?php

namespace App\Livewire\Shared;

use App\Enums\ServicoStatus;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Notification bell mounted in the top bar (see top-bar.blade.php),
 * replacing what used to be a static, non-functional <x-ui.icon-button
 * icon="bell">. Same overdue/due-soon list as Dashboard::alertas(), just
 * rendered in a dropdown (via the shared data-dropdown-toggle/-panel
 * mechanism in layouts/app.blade.php) instead of a full card.
 */
class NotificationBell extends Component
{
    /** @return Collection<int, Servico> */
    #[Computed]
    public function alertas(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    /** The real count for the badge — the list above stops at 8. */
    #[Computed]
    public function total(): int
    {
        return Servico::query()->whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])->count();
    }

    public function render()
    {
        return view('livewire.shared.notification-bell');
    }
}
