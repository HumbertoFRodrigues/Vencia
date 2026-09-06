@php
    $eventos = $this->eventos;
    $counts = $this->counts;
    $pagamentos = $this->ultimosPagamentos;
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header eyebrow="Registo imutável" title="Histórico" />

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <x-ui.tabs
            style="flex:1;min-width:280px"
            :active="$tab"
            :tabs="[
                ['id' => 'todos', 'label' => 'Todos', 'count' => $counts['todos']],
                ['id' => 'pagamentos', 'label' => 'Pagamentos', 'count' => $counts['pagamentos']],
                ['id' => 'emails', 'label' => 'Emails', 'count' => $counts['emails']],
                ['id' => 'alteracoes', 'label' => 'Alterações', 'count' => $counts['alteracoes']],
            ]"
        />
        <x-ui.search-input width="250" wire:model.live.debounce.300ms="q" placeholder="Cliente, serviço ou evento" :shortcut="null" />
    </div>

    <div class="dashboard-grid">
        <x-ui.card title="Eventos" subtitle="Nada é apagado — apenas acrescentado">
            @if($eventos->isEmpty())
                <x-ui.empty-state icon="search-x" title="Sem eventos" description="Nenhum evento corresponde ao filtro aplicado." />
            @else
                <x-ui.timeline :items="$eventos->map(fn ($h) => [
                    'kind' => $h->kind,
                    'date' => $h->occurred_at->format('d/m/Y'),
                    'title' => $h->title,
                    'description' => $h->description,
                ])" />
            @endif
        </x-ui.card>

        <div class="dashboard-rail">
            <x-ui.card title="Últimos pagamentos">
                @if($pagamentos->isEmpty())
                    <x-ui.empty-state icon="banknote" title="Sem pagamentos" />
                @else
                    <div style="display:flex;flex-direction:column;gap:12px">
                        @foreach($pagamentos as $p)
                            <div style="display:flex;align-items:center;gap:10px">
                                <x-ui.service-logo :name="$p->servico?->nome ?? ''" :category="$p->servico?->categoria ?? 'outro'" size="28" />
                                <div style="flex:1;min-width:0;display:flex;flex-direction:column">
                                    <span style="font-size:var(--text-sm);color:var(--text-strong);font-weight:var(--weight-medium);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->servico?->nome ?? '—' }}</span>
                                    <x-ui.payment-method :method="$p->metodo" :size="16" />
                                </div>
                                <x-ui.money-value :amount="$p->valor" size="sm" tone="in" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
