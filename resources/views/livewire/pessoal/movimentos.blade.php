@php
    $t = $this->totais;
    $mesAtual = $this->mesActual()->isSameMonth(now());
@endphp

<div class="pessoal-page">
    <x-ui.page-header eyebrow="Painel pessoal" title="Movimentos">
        <x-slot:actions>
            <x-ui.month-nav :label="$this->rotuloMes()" :atual="$mesAtual" />
            @if($this->movimentos->isNotEmpty())<x-ui.export-menu />@endif
            <x-ui.button icon="arrow-down-to-line" wire:click="$dispatch('movimento:abrir', { tipo: 'entrada' })">Entrada</x-ui.button>
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('movimento:abrir', { tipo: 'saida' })">Saída</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card label="Entradas" :value="$t['entradas']" :currency="\App\Models\Configuracao::moeda()" tone="in" icon="arrow-down-to-line" :footnote="$t['nEntradas'].' '.($t['nEntradas'] === 1 ? 'movimento' : 'movimentos')" />
        <x-ui.stat-card label="Saídas" :value="$t['saidas']" :currency="\App\Models\Configuracao::moeda()" tone="out" icon="arrow-up" :footnote="$t['nSaidas'].' '.($t['nSaidas'] === 1 ? 'movimento' : 'movimentos')" />
        <x-ui.stat-card label="Saldo" :value="$t['saldo']" :currency="\App\Models\Configuracao::moeda()" :tone="$t['saldo'] >= 0 ? 'in' : 'out'" icon="wallet" :footnote="$categoria !== '' || $q !== '' ? 'Só o que está filtrado' : 'Entradas menos saídas'" />
    </div>

    <x-ui.card :padding="0">
        <div class="filter-bar">
            <x-ui.tabs :tabs="[
                ['id' => 'todos', 'label' => 'Todos', 'count' => $t['nTodos']],
                ['id' => 'entrada', 'label' => 'Entradas', 'count' => $t['nEntradas']],
                ['id' => 'saida', 'label' => 'Saídas', 'count' => $t['nSaidas']],
            ]" :active="$tipo" />
            <div class="filter-bar__controls">
                <div style="width:220px"><x-ui.select wire:model.live="categoria" :options="$this->categoriaOptions" aria-label="Filtrar por categoria" /></div>
                <div style="width:220px"><x-ui.input icon="search" wire:model.live.debounce.300ms="q" placeholder="Pesquisar descrição" size="sm" aria-label="Pesquisar" /></div>
            </div>
        </div>

        <x-ui.data-table
            :columns="[
                ['key' => 'data', 'header' => 'Data'],
                ['key' => 'descricao', 'header' => 'Descrição'],
                ['key' => 'categoria', 'header' => 'Categoria'],
                ['key' => 'meio', 'header' => 'Meio'],
                ['key' => 'valor', 'header' => 'Valor', 'align' => 'right'],
            ]"
            :empty="$this->movimentos->isEmpty()"
        >
            @foreach($this->movimentos as $m)
                @php($entrada = $m->tipo === \App\Enums\TipoMovimento::Entrada)
                <tr data-clickable wire:key="m-{{ $m->id }}" wire:click="$dispatch('movimento:abrir', { movimentoId: {{ $m->id }} })" tabindex="0" onkeydown="if (event.key === 'Enter') this.click()">
                    <td><span class="num" style="font-size:var(--text-xs)">{{ $m->data->format('d/m/Y') }}</span></td>
                    <td>
                        <span style="display:flex;align-items:center;gap:8px">
                            <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $m->descricao }}</span>
                            @if($m->fixo_pessoal_id)<x-ui.tag tone="outline">Fixo</x-ui.tag>@endif
                            @if($m->retirada_id)<x-ui.tag tone="accent">Da empresa</x-ui.tag>@endif
                        </span>
                        @if($m->notas)<span style="display:block;font-size:var(--text-xs);color:var(--text-muted);margin-top:2px">{{ $m->notas }}</span>@endif
                    </td>
                    <td><x-ui.cat-chip :categoria="$m->categoria" label size="sm" /></td>
                    <td>
                        @if($m->meio)
                            <x-ui.payment-method :method="$m->meio" size="20" />
                        @else
                            <span style="color:var(--text-faint)">—</span>
                        @endif
                    </td>
                    <td class="u-text-right"><x-ui.money-value :amount="$entrada ? $m->valor : -$m->valor" size="sm" :tone="$entrada ? 'in' : 'out'" /></td>
                </tr>
            @endforeach

            <x-slot:emptyState>
                @if($q !== '' || $categoria !== '' || $tipo !== 'todos')
                    <x-ui.empty-state icon="search-x" title="Nada encontrado" description="Nenhum movimento neste mês corresponde aos filtros." />
                @else
                    <x-ui.empty-state icon="receipt" title="Sem movimentos em {{ $this->rotuloMes() }}" description="Regista aqui os gastos do dia-a-dia e as entradas que não são fixas.">
                        <x-slot:action>
                            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('movimento:abrir', { tipo: 'saida' })">Registar saída</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endif
            </x-slot:emptyState>
        </x-ui.data-table>
    </x-ui.card>

    <livewire:pessoal.movimento-dialog />
</div>
