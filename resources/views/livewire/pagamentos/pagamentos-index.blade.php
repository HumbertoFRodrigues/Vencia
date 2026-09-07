@php
    $totais = $this->totais;
    $pagamentos = $this->pagamentos;
    $total = $this->totalFiltrado;
    $mesLabel = ucfirst(now()->locale('pt_PT')->translatedFormat('F')).' de '.now()->year;
    $metodosPill = ['mpesa', 'emola', 'transferencia', 'dinheiro', 'outro'];
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header :eyebrow="$mesLabel" title="Pagamentos">
        <x-slot:actions>
            <x-ui.button icon="download" wire:click="exportarCsv">Exportar CSV</x-ui.button>
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('pagamento:abrir')">Registrar pagamento</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card
            label="Entradas do período"
            :value="$totais['entradas']"
            currency="MZN"
            tone="in"
            icon="arrow-down-to-line"
            :footnote="$totais['nPagamentos'].' pagamentos'"
        />
        <x-ui.stat-card
            label="A receber"
            :value="$totais['aReceber']"
            currency="MZN"
            tone="out"
            icon="hourglass"
            :footnote="$totais['nAReceber'].' serviços'"
        />
        <x-ui.stat-card label="Receita mensal recorrente" :value="$totais['mrr']" currency="MZN" icon="repeat" />
        <x-ui.stat-card label="Receita anual recorrente" :value="$totais['arr']" currency="MZN" icon="chart-line" />
    </div>

    <div class="pill-picker">
        @foreach($metodosPill as $m)
            <button type="button" wire:click="alternarMetodo('{{ $m }}')" class="pill-picker__item {{ in_array($m, $metodos, true) ? 'pill-picker__item--active' : '' }}">
                <x-ui.payment-method :method="$m" size="22" />
            </button>
        @endforeach
    </div>

    <div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap">
        <div style="width:170px">
            <x-ui.select wire:model.live="mes" :options="$this->mesesDisponiveis" />
        </div>
        <div style="width:190px">
            <x-ui.select wire:model.live="cliente" :options="$this->clientesOptions" />
        </div>
        <x-ui.search-input width="230" wire:model.live.debounce.300ms="q" placeholder="Pesquisar pagamento" :shortcut="null" style="margin-left:auto" />
    </div>

    <x-ui.card
        title="Total recebido no período"
        :subtitle="number_format($total, 0, ',', '.').' MZN'"
        :padding="0"
    >
        <x-slot:action>
            <x-ui.tag tone="accent">{{ $pagamentos->total() }} {{ $pagamentos->total() === 1 ? 'registo' : 'registos' }}</x-ui.tag>
        </x-slot:action>

        <x-ui.data-table
            :columns="[
                ['key' => 'data', 'header' => 'Data'],
                ['key' => 'cliente', 'header' => 'Cliente'],
                ['key' => 'servico', 'header' => 'Serviço'],
                ['key' => 'periodo', 'header' => 'Período'],
                ['key' => 'metodo', 'header' => 'Método'],
                ['key' => 'valor', 'header' => 'Valor', 'align' => 'right'],
                ['key' => 'acoes', 'header' => '', 'align' => 'right'],
            ]"
            :empty="$pagamentos->isEmpty()"
        >
            @foreach($pagamentos as $p)
                <tr>
                    <td><span class="num" style="font-size:var(--text-xs)">{{ $p->data->format('d/m/Y') }}</span></td>
                    <td><span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $p->cliente?->nome }}</span></td>
                    <td>
                        <span style="display:flex;align-items:center;gap:8px">
                            <x-ui.service-logo :name="$p->servico?->nome ?? ''" :category="$p->servico?->categoria" size="24" />
                            {{ $p->servico?->nome ?? '—' }}
                        </span>
                    </td>
                    <td><x-ui.tag tone="outline">{{ $p->periodo }}</x-ui.tag></td>
                    <td><x-ui.payment-method :method="$p->metodo" size="22" /></td>
                    <td class="u-text-right"><x-ui.money-value :amount="$p->valor" tone="in" size="sm" /></td>
                    <td class="u-text-right">
                        <span style="display:inline-flex;align-items:center;gap:6px">
                            @if($confirmandoRemocaoId === $p->id)
                                <span style="font-size:var(--text-xs);color:var(--text-muted);white-space:nowrap">Apagar este pagamento?</span>
                                <x-ui.icon-button icon="check" label="Confirmar remoção" size="sm" class="action-danger-quiet" wire:click="apagar({{ $p->id }})" />
                                <x-ui.icon-button icon="x" label="Cancelar remoção" size="sm" wire:click="cancelarRemocao" />
                            @else
                                <a href="{{ route('pagamentos.recibo', $p) }}" aria-label="Gerar recibo" title="Gerar recibo" class="icon-btn icon-btn--ghost icon-btn--sm">
                                    <x-ui.icon name="receipt" size="15" />
                                </a>
                                <x-ui.icon-button icon="pencil" label="Editar" size="sm" wire:click="$dispatch('pagamento:abrir', { pagamentoId: {{ $p->id }} })" />
                                <x-ui.icon-button icon="trash-2" label="Apagar" size="sm" wire:click="pedirConfirmacaoRemocao({{ $p->id }})" />
                            @endif
                        </span>
                    </td>
                </tr>
            @endforeach

            <x-slot:emptyState>
                <x-ui.empty-state icon="search-x" title="Sem pagamentos" description="Nenhum pagamento corresponde aos filtros aplicados." />
            </x-slot:emptyState>
        </x-ui.data-table>

        <x-ui.pagination :paginator="$pagamentos" />
    </x-ui.card>

    <livewire:pagamentos.registrar-pagamento-dialog />
</div>
