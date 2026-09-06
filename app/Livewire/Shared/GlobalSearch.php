<?php

namespace App\Livewire\Shared;

use App\Models\Cliente;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

/**
 * Debounced dropdown search mounted in the top bar (see
 * resources/views/components/ui/top-bar.blade.php), replacing what used to
 * be a static, non-functional <x-ui.search-input>. Queries cliente
 * nome/email and servico nome/plano, grouped by type, each result linking
 * straight to its detail page. No full results page — a lightweight
 * dropdown is enough per the brief.
 *
 * The dropdown's visibility is driven entirely by server state (rendered
 * only while $q is non-empty), not by CSS focus tricks or Alpine — same
 * "no JS framework beyond what's already here" convention the rest of the
 * shell uses (e.g. the plain-script theme toggle). That sidesteps the
 * classic "blur hides the panel before the result's click can fire" bug
 * entirely, since nothing here reacts to focus/blur at all.
 */
class GlobalSearch extends Component
{
    public string $q = '';

    /** @return Collection<int, Cliente> */
    private function clientes(): Collection
    {
        $term = trim($this->q);
        $like = '%'.$term.'%';

        return Cliente::query()
            ->when($term === '', fn ($query) => $query->whereRaw('1 = 0'))
            ->when($term !== '', fn ($query) => $query->where(
                fn ($inner) => $inner->where('nome', 'like', $like)->orWhere('email', 'like', $like)
            ))
            ->orderBy('nome')
            ->limit(5)
            ->get();
    }

    /** @return Collection<int, Servico> */
    private function servicos(): Collection
    {
        $term = trim($this->q);
        $like = '%'.$term.'%';

        return Servico::query()
            ->with('cliente')
            ->when($term === '', fn ($query) => $query->whereRaw('1 = 0'))
            ->when($term !== '', fn ($query) => $query->where(
                fn ($inner) => $inner->where('nome', 'like', $like)->orWhere('plano', 'like', $like)
            ))
            ->orderBy('nome')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.shared.global-search', [
            'clientesResultado' => $this->clientes(),
            'servicosResultado' => $this->servicos(),
        ]);
    }
}
