@php
    $servicos = $this->servicos;
    $counts = $this->counts;
    $mesLabel = ucfirst(now()->locale('pt_PT')->translatedFormat('F')).' de '.now()->year;
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header :eyebrow="$mesLabel" title="Vencimentos">
        <x-slot:actions>
            <x-ui.button :variant="$view === 'lista' ? 'primary' : 'secondary'" icon="list" wire:click="setView('lista')">Lista</x-ui.button>
            <x-ui.button :variant="$view === 'calendario' ? 'primary' : 'secondary'" icon="calendar" wire:click="setView('calendario')">Calendário</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if($view === 'lista')
        <x-ui.tabs
            :active="$tab"
            :tabs="[
                ['id' => 'todos', 'label' => 'Todos', 'count' => $counts['todos']],
                ['id' => 'activo', 'label' => 'Activos', 'count' => $counts['activo']],
                ['id' => 'a_vencer', 'label' => 'A vencer', 'count' => $counts['a_vencer']],
                ['id' => 'vencido', 'label' => 'Vencidos', 'count' => $counts['vencido']],
                ['id' => 'suspenso', 'label' => 'Suspensos', 'count' => $counts['suspenso']],
            ]"
        />

        <x-ui.card :padding="0">
            <x-ui.data-table
                :columns="[
                    ['key' => 'nome', 'header' => 'Serviço'],
                    ['key' => 'cliente', 'header' => 'Cliente'],
                    ['key' => 'periodo', 'header' => 'Período'],
                    ['key' => 'vencimento', 'header' => 'Vencimento'],
                    ['key' => 'valor', 'header' => 'Valor', 'align' => 'right'],
                    ['key' => 'status', 'header' => 'Estado'],
                ]"
                :empty="$servicos->isEmpty()"
            >
                @foreach($servicos as $s)
                    @php
                        $dias = $s->dias;
                        $label = match (true) {
                            $s->status->value === 'a_vencer' && $dias !== null => 'Vence em '.$dias.' '.($dias === 1 ? 'dia' : 'dias'),
                            $s->status->value === 'vencido' && $dias !== null => 'Vencido há '.abs($dias).' '.(abs($dias) === 1 ? 'dia' : 'dias'),
                            default => null,
                        };
                    @endphp
                    <tr data-clickable onclick="window.location='{{ route('servicos.show', $s) }}'">
                        <td>
                            <span style="display:flex;align-items:center;gap:9px">
                                <x-ui.service-logo :name="$s->nome" :category="$s->categoria" size="26" />
                                <span style="display:flex;flex-direction:column">
                                    <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->nome }}</span>
                                    <span style="font-size:var(--text-xs);color:var(--text-muted)">{{ $s->plano }}</span>
                                </span>
                            </span>
                        </td>
                        <td>{{ $s->cliente?->nome }}</td>
                        <td>{{ $s->periodicidade->label() }}</td>
                        <td><span class="num" style="font-size:var(--text-xs)">{{ $s->vencimento?->format('d/m/Y') ?? '—' }}</span></td>
                        <td class="u-text-right"><x-ui.money-value :amount="$s->valor" size="sm" /></td>
                        <td><x-ui.status-badge :status="$s->status" size="sm" :label="$label" /></td>
                    </tr>
                @endforeach

                <x-slot:emptyState>
                    <x-ui.empty-state icon="calendar-check" title="Sem vencimentos" description="Nenhum serviço corresponde ao filtro aplicado." />
                </x-slot:emptyState>
            </x-ui.data-table>
        </x-ui.card>
    @else
        <x-ui.card :title="$mesLabel" subtitle="Clique num evento para abrir o serviço">
            <livewire:vencimentos.calendario-mes />
        </x-ui.card>
    @endif
</div>
