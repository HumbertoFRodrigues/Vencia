@php
    $t = $this->totais;
    $mesAtual = $this->mesActual()->isSameMonth(now());
    $estadoRotulo = ['ok' => 'Dentro', 'alerta' => 'Atenção', 'excedido' => 'Passou'];
    $estadoPill = ['ok' => 'confirmado', 'alerta' => 'proximo', 'excedido' => 'atraso'];
@endphp

<div class="pessoal-page">
    <x-ui.page-header eyebrow="Painel pessoal" title="Orçamentos">
        <x-slot:actions>
            <x-ui.month-nav :label="$this->rotuloMes()" :atual="$mesAtual" />
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('categoria:abrir', { tipo: 'saida' })">Nova categoria</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card label="Orçamento do mês" :value="$t['limite']" :currency="\App\Models\Configuracao::moeda()" icon="target" :footnote="$t['nComLimite'].' '.($t['nComLimite'] === 1 ? 'categoria com limite' : 'categorias com limite')" />
        <x-ui.stat-card label="Gasto nessas categorias" :value="$t['gasto']" :currency="\App\Models\Configuracao::moeda()" tone="out" icon="arrow-up" :footnote="$t['limite'] > 0 ? round($t['gasto'] / $t['limite'] * 100).'% do orçamento' : 'Define limites abaixo'" />
        <x-ui.stat-card label="Ainda disponível" :value="$t['disponivel']" :currency="\App\Models\Configuracao::moeda()" :tone="$t['disponivel'] >= 0 ? 'in' : 'out'" icon="wallet" footnote="Já descontados os fixos por pagar" />
        <x-ui.stat-card label="Em alerta" :value="(string) $t['nAlerta']" :tone="$t['nAlerta'] > 0 ? 'out' : 'neutral'" icon="triangle-alert" footnote="A partir de 80% do limite" />
    </div>

    <x-ui.card title="Categorias de saída" subtitle="Clica numa categoria para mudar o limite, o ícone ou a cor" :padding="0">
        <x-slot:action>
            <x-ui.button size="sm" variant="ghost" icon="plus" wire:click="$dispatch('categoria:abrir', { tipo: 'saida' })">Categoria</x-ui.button>
        </x-slot:action>

        <x-ui.data-table :columns="[
            ['key' => 'nome', 'header' => 'Categoria'],
            ['key' => 'gasto', 'header' => 'Gasto em '.$this->rotuloMes(), 'align' => 'right'],
            ['key' => 'limite', 'header' => 'Limite mensal', 'align' => 'right'],
            ['key' => 'progresso', 'header' => 'Progresso'],
        ]">
            @foreach($this->saidas as $l)
                @php($c = $l['categoria'])
                @php($o = $l['orcamento'])
                <tr data-clickable wire:key="cs-{{ $c->id }}" wire:click="$dispatch('categoria:abrir', { categoriaId: {{ $c->id }} })" tabindex="0" onkeydown="if (event.key === 'Enter') this.click()" @class(['row--muted' => $c->arquivado])>
                    <td>
                        <span style="display:flex;align-items:center;gap:8px">
                            <x-ui.cat-chip :categoria="$c" label />
                            @if($c->arquivado)<x-ui.tag tone="outline">Arquivada</x-ui.tag>@endif
                        </span>
                    </td>
                    <td class="u-text-right"><x-ui.money-value :amount="$l['total']" size="sm" :tone="$l['total'] > 0 ? 'out' : 'muted'" /></td>
                    <td class="u-text-right">
                        @if($c->limite_mensal)
                            <x-ui.money-value :amount="$c->limite_mensal" size="sm" tone="muted" />
                        @else
                            <span class="link-like">Definir limite</span>
                        @endif
                    </td>
                    <td style="min-width:220px">
                        @if($o)
                            <span class="budget-row">
                                <span class="progress" role="img" aria-label="{{ round($o['pct']) }}% do limite gasto">
                                    <span class="progress__fill progress__fill--{{ $o['estado'] }}" style="width:{{ min(100, $o['pct']) }}%"></span>
                                    @if($o['pctPendente'] > 0)<span class="progress__pending" style="width:{{ $o['pctPendente'] }}%"></span>@endif
                                </span>
                                <span class="estado-pill estado-pill--{{ $estadoPill[$o['estado']] }}">{{ round($o['pct']) }}% · {{ $estadoRotulo[$o['estado']] }}</span>
                            </span>
                        @else
                            <span style="color:var(--text-faint);font-size:var(--text-xs)">Sem limite</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>

    <x-ui.card title="Categorias de entrada" subtitle="De onde vem o teu dinheiro" :padding="0">
        <x-slot:action>
            <x-ui.button size="sm" variant="ghost" icon="plus" wire:click="$dispatch('categoria:abrir', { tipo: 'entrada' })">Categoria</x-ui.button>
        </x-slot:action>

        <x-ui.data-table :columns="[
            ['key' => 'nome', 'header' => 'Categoria'],
            ['key' => 'total', 'header' => 'Recebido em '.$this->rotuloMes(), 'align' => 'right'],
        ]">
            @foreach($this->entradas as $l)
                @php($c = $l['categoria'])
                <tr data-clickable wire:key="ce-{{ $c->id }}" wire:click="$dispatch('categoria:abrir', { categoriaId: {{ $c->id }} })" tabindex="0" onkeydown="if (event.key === 'Enter') this.click()" @class(['row--muted' => $c->arquivado])>
                    <td>
                        <span style="display:flex;align-items:center;gap:8px">
                            <x-ui.cat-chip :categoria="$c" label />
                            @if($c->arquivado)<x-ui.tag tone="outline">Arquivada</x-ui.tag>@endif
                        </span>
                    </td>
                    <td class="u-text-right"><x-ui.money-value :amount="$l['total']" size="sm" :tone="$l['total'] > 0 ? 'in' : 'muted'" /></td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>

    <livewire:pessoal.categoria-dialog />
</div>
