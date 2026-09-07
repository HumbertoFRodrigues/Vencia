@php
    use Illuminate\Support\Facades\Route;

    $totais = $this->totais;
    $proximos = $this->proximosVencimentos;
    $alertas = $this->alertas;
    $mesLabel = ucfirst(now()->locale('pt_PT')->translatedFormat('F')).' de '.now()->year;
@endphp

<div style="display:flex;flex-direction:column;gap:18px">
<x-ui.page-header :eyebrow="$mesLabel" title="Dashboard">
    <x-slot:actions>
        <x-ui.export-menu />
        <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('assinatura:abrir')">Nova assinatura</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="tile-grid">
    <x-ui.stat-card
        label="Receita deste mês"
        :value="$totais['entradas']"
        currency="MZN"
        icon="wallet"
        tone="in"
        :footnote="$totais['nPagamentos'].' pagamentos'"
    />
    <x-ui.stat-card
        label="Receita esperada"
        :value="$totais['esperada']"
        currency="MZN"
        icon="target"
        :footnote="'faltam '.number_format($totais['aReceber'], 0, ',', '.').' MZN'"
    />
    <x-ui.stat-card
        label="Em atraso"
        :value="$totais['emAtraso']"
        currency="MZN"
        tone="out"
        icon="circle-alert"
        :footnote="$totais['nEmAtraso'].' '.($totais['nEmAtraso'] === 1 ? 'serviço' : 'serviços')"
    />
    <x-ui.stat-card label="Clientes activos" :value="$totais['clientesActivos']" icon="users" />
    <x-ui.stat-card label="Serviços activos" :value="$totais['servicosActivos']" icon="package" />
    <x-ui.stat-card
        label="Vencendo em breve"
        :value="$totais['aVencer']"
        tone="due"
        icon="calendar-clock"
        footnote="próximos 10 dias"
    />
</div>

<div class="dashboard-grid">
    <x-ui.card title="Próximos vencimentos" subtitle="Ordenado pela data mais próxima" :padding="0">
        <x-slot:action>
            <x-ui.button size="sm" iconEnd="arrow-right">Ver todos</x-ui.button>
        </x-slot:action>

        <x-ui.data-table
            :columns="[
                ['key' => 'nome', 'header' => 'Serviço'],
                ['key' => 'cliente', 'header' => 'Cliente'],
                ['key' => 'vencimento', 'header' => 'Vencimento'],
                ['key' => 'valor', 'header' => 'Valor', 'align' => 'right'],
                ['key' => 'status', 'header' => 'Estado'],
            ]"
            :empty="$proximos->isEmpty()"
        >
            @foreach($proximos as $s)
                @php
                    $href = Route::has('servicos.show') ? route('servicos.show', $s) : null;
                    $dias = $s->dias;
                @endphp
                <tr @if($href) data-clickable onclick="Livewire.navigate('{{ $href }}')" @endif>
                    <td>
                        <span style="display:flex;align-items:center;gap:9px">
                            <x-ui.service-logo :name="$s->nome" :category="$s->categoria" size="26" />
                            <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->nome }}</span>
                        </span>
                    </td>
                    <td>{{ $s->cliente?->nome }}</td>
                    <td><span class="num" style="font-size:var(--text-xs)">{{ $s->vencimento?->format('d/m/Y') ?? '—' }}</span></td>
                    <td class="u-text-right"><x-ui.money-value :amount="$s->valor" size="sm" /></td>
                    <td>
                        <x-ui.status-badge
                            :status="$s->status"
                            size="sm"
                            :label="$dias !== null && $dias < 0 ? 'Vencido' : ($dias !== null && $dias <= 7 ? 'Vence em '.$dias.' dias' : null)"
                        />
                    </td>
                </tr>
            @endforeach

            <x-slot:emptyState>
                <x-ui.empty-state icon="calendar-check" title="Sem vencimentos próximos" description="Ainda não há serviços a vencer." />
            </x-slot:emptyState>
        </x-ui.data-table>
    </x-ui.card>

    <div class="dashboard-rail">
        @if($totais['nEmAtraso'] > 0)
            <x-ui.alert-banner tone="danger" title="Requer atenção">
                {{ $totais['nEmAtraso'] }} {{ $totais['nEmAtraso'] === 1 ? 'serviço está vencido' : 'serviços estão vencidos' }}.
            </x-ui.alert-banner>
        @elseif($totais['aVencer'] > 0)
            <x-ui.alert-banner tone="warning" title="Requer atenção">
                {{ $totais['aVencer'] }} {{ $totais['aVencer'] === 1 ? 'serviço vence' : 'serviços vencem' }} em breve.
            </x-ui.alert-banner>
        @endif

        <x-ui.card title="Alertas" :padding="0">
            @if($alertas->isEmpty())
                <x-ui.empty-state icon="circle-check" title="Tudo em dia" description="Não há serviços vencidos ou a vencer." />
            @else
                <div style="display:flex;flex-direction:column">
                    @foreach($alertas as $i => $s)
                        @php
                            $href = Route::has('servicos.show') ? route('servicos.show', $s) : '#';
                            $dias = $s->dias;
                            $vencido = $s->status === \App\Enums\ServicoStatus::Vencido;
                            $texto = $vencido
                                ? $s->nome.' está vencido'.($dias !== null ? ' há '.abs($dias).' '.(abs($dias) === 1 ? 'dia' : 'dias') : '')
                                : $s->nome.' vence em '.$dias.' '.($dias === 1 ? 'dia' : 'dias');
                        @endphp
                        <a href="{{ $href }}" wire:navigate style="display:flex;align-items:center;gap:9px;padding:10px 16px;text-decoration:none;{{ $i ? 'border-top:1px solid var(--border-subtle);' : '' }}font-size:var(--text-sm);color:var(--text-body)">
                            <x-ui.icon :name="$vencido ? 'circle-alert' : 'calendar-clock'" size="15" :color="$vencido ? 'var(--status-overdue-fg)' : 'var(--status-due-fg)'" />
                            <span style="flex:1">{{ $texto }}</span>
                            <x-ui.icon name="chevron-right" size="14" color="var(--text-faint)" />
                        </a>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Receita recorrente">
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;flex-direction:column;gap:2px">
                    <span class="eyebrow">Receita mensal recorrente</span>
                    <x-ui.money-value :amount="$totais['mrr']" size="lg" />
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;padding-top:12px;border-top:1px solid var(--border-subtle)">
                    <span class="eyebrow">Receita anual recorrente</span>
                    <x-ui.money-value :amount="$totais['arr']" size="lg" />
                </div>
            </div>
        </x-ui.card>
    </div>
</div>

<livewire:servicos.new-subscription-dialog />
</div>
