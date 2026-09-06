@php
    $clientes = $this->clientes;
    $counts = $this->counts;
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header :eyebrow="$counts['todos'].' clientes'" title="Clientes">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('cliente-form:abrir')">Novo cliente</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
        <x-ui.tabs
            style="flex:1;min-width:260px"
            :active="$tab"
            :tabs="[
                ['id' => 'todos', 'label' => 'Todos', 'count' => $counts['todos']],
                ['id' => 'activo', 'label' => 'Activos', 'count' => $counts['activo']],
                ['id' => 'inactivo', 'label' => 'Inactivos', 'count' => $counts['inactivo']],
            ]"
        />
        <x-ui.search-input
            width="260"
            wire:model.live.debounce.300ms="q"
            placeholder="Nome, email ou empresa"
            :shortcut="null"
        />
    </div>

    <x-ui.card :padding="0">
        <x-ui.data-table
            :columns="[
                ['key' => 'nome', 'header' => 'Cliente'],
                ['key' => 'empresa', 'header' => 'Empresa'],
                ['key' => 'servicos', 'header' => 'Serviços', 'align' => 'right'],
                ['key' => 'mrr', 'header' => 'Receita / mês', 'align' => 'right'],
                ['key' => 'desde', 'header' => 'Cliente desde'],
                ['key' => 'status', 'header' => 'Estado'],
            ]"
            :empty="$clientes->isEmpty()"
        >
            @foreach($clientes as $cliente)
                @php
                    $palavras = array_values(array_filter(explode(' ', trim($cliente->nome))));
                    $iniciais = mb_strtoupper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($palavras, 0, 2))));
                @endphp
                <tr data-clickable onclick="window.location='{{ route('clientes.show', $cliente) }}'">
                    <td>
                        <span style="display:flex;align-items:center;gap:10px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;background:var(--surface-sunken);color:var(--text-body);font-size:var(--text-xs);font-weight:var(--weight-semibold);flex-shrink:0">{{ $iniciais }}</span>
                            <span style="display:flex;flex-direction:column;min-width:0">
                                <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $cliente->nome }}</span>
                                <span style="font-size:var(--text-xs);color:var(--text-muted)">{{ $cliente->email }}</span>
                            </span>
                        </span>
                    </td>
                    <td>
                        @if($cliente->empresa)
                            <x-ui.tag>{{ $cliente->empresa }}</x-ui.tag>
                        @else
                            <span style="color:var(--text-faint)">—</span>
                        @endif
                    </td>
                    <td class="u-text-right"><span class="num">{{ $cliente->servicos_count }}</span></td>
                    <td class="u-text-right"><x-ui.money-value :amount="$this->mrrDe($cliente)" size="sm" tone="muted" /></td>
                    <td><span class="num" style="font-size:var(--text-xs)">{{ $cliente->desde->format('d/m/Y') }}</span></td>
                    <td>
                        <x-ui.status-badge
                            size="sm"
                            :status="$cliente->status === \App\Enums\ClienteStatus::Activo ? 'activo' : 'cancelado'"
                            :label="$cliente->status === \App\Enums\ClienteStatus::Activo ? 'Activo' : 'Inactivo'"
                        />
                    </td>
                </tr>
            @endforeach

            <x-slot:emptyState>
                @if(trim($q) !== '')
                    <x-ui.empty-state icon="search-x" title="Sem resultados" description="Nenhum cliente corresponde a &ldquo;{{ $q }}&rdquo;." />
                @else
                    <x-ui.empty-state icon="search-x" title="Sem resultados" description="Não há clientes neste estado." />
                @endif
            </x-slot:emptyState>
        </x-ui.data-table>
    </x-ui.card>

    <livewire:clientes.cliente-form />
</div>
