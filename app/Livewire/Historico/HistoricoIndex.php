<?php

namespace App\Livewire\Historico;

use App\Enums\HistoricoKind;
use App\Models\Historico;
use App\Models\Pagamento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Global, append-only audit log. Type tabs group HistoricoKind's finer enum
 * into the three buckets HistoryScreen.jsx's `tipo` field uses:
 * "pagamentos" is HistoricoKind::Pagamento; "emails" is every actual email
 * send — Lembrete (renewal reminders) and Email (the "Serviço terminado"
 * notice SuspenderDialog logs, see app/Livewire/Servicos/SuspenderDialog.php);
 * everything else (Criado/Activado/Vencido/Suspenso/Cancelado/Alterado) is a
 * state "alteração". This mapping isn't invented — it mirrors the
 * prototype's own EVENTS dataset one-for-one (its "lembrete" rows are
 * tipo: emails, its "criado/activado/suspenso/vencido" rows are
 * tipo: alteracoes), just generalised to the richer real enum.
 */
#[Layout('layouts.app')]
#[Title('Histórico')]
class HistoricoIndex extends Component
{
    use WithPagination;

    /** @var array<string, list<HistoricoKind>> */
    private const GRUPOS = [
        'pagamentos' => [HistoricoKind::Pagamento],
        'emails' => [HistoricoKind::Lembrete, HistoricoKind::Email],
        'alteracoes' => [
            HistoricoKind::Criado, HistoricoKind::Activado, HistoricoKind::Vencido,
            HistoricoKind::Suspenso, HistoricoKind::Cancelado, HistoricoKind::Alterado,
        ],
    ];

    /** This table only grows (append-only) — 25 events/page keeps the timeline scannable. */
    private const POR_PAGINA = 25;

    #[Url]
    public string $tab = 'todos';

    #[Url]
    public string $q = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    /** Search box (wire:model.live.debounce) — every change resets to page 1. */
    public function updatedQ(): void
    {
        $this->resetPage();
    }

    /** @return LengthAwarePaginator<int, Historico> */
    #[Computed]
    public function eventos(): LengthAwarePaginator
    {
        return Historico::query()
            ->with(['cliente', 'servico'])
            ->when($this->tab !== 'todos', fn ($query) => $query->whereIn('kind', self::GRUPOS[$this->tab] ?? []))
            ->when(trim($this->q) !== '', function ($query): void {
                $term = '%'.trim($this->q).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('cliente', fn ($c) => $c->where('nome', 'like', $term))
                        ->orWhereHas('servico', fn ($s) => $s->where('nome', 'like', $term));
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(self::POR_PAGINA);
    }

    /** @return array{todos: int, pagamentos: int, emails: int, alteracoes: int} */
    #[Computed]
    public function counts(): array
    {
        return [
            'todos' => Historico::query()->count(),
            'pagamentos' => Historico::query()->whereIn('kind', self::GRUPOS['pagamentos'])->count(),
            'emails' => Historico::query()->whereIn('kind', self::GRUPOS['emails'])->count(),
            'alteracoes' => Historico::query()->whereIn('kind', self::GRUPOS['alteracoes'])->count(),
        ];
    }

    /**
     * Rail card "Últimos pagamentos" — queries Pagamento directly rather
     * than filtering the Historico feed, since Pagamento already carries
     * método/valor/servico in the exact shape that row needs (less
     * duplicative than re-deriving them from a Historico description string).
     *
     * @return Collection<int, Pagamento>
     */
    #[Computed]
    public function ultimosPagamentos(): Collection
    {
        return Pagamento::query()
            ->with('servico')
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.historico.historico-index');
    }
}
