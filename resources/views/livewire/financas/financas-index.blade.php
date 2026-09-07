@php
    $totais = $this->totais;
    $porMetodo = $this->porMetodo;
    $entradasPorMes = $this->entradasPorMes;
    $aReceber = $this->aReceber;
    $mesLabel = ucfirst(now()->locale('pt_PT')->translatedFormat('F')).' de '.now()->year;

    $maxMetodo = max(1.0, ...array_column($porMetodo, 'valor'));
    $maxMes = max(1.0, ...array_column($entradasPorMes, 'valor'));
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header :eyebrow="$mesLabel" title="Finanças">
        <x-slot:actions>
            <x-ui.export-menu />
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card
            label="Entradas"
            :value="$totais['entradas']"
            currency="MZN"
            tone="in"
            icon="arrow-down-to-line"
            :footnote="$totais['nPagamentos'].' pagamentos em '.$mesLabel"
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

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:12px;align-items:start">
        <x-ui.card title="Como o dinheiro entrou" :subtitle="$mesLabel.', por método de pagamento'">
            <div style="display:flex;flex-direction:column;gap:12px">
                @foreach($porMetodo as $linha)
                    <div style="display:flex;align-items:center;gap:12px">
                        <span style="width:152px"><x-ui.payment-method :method="$linha['metodo']" size="24" /></span>
                        <span style="flex:1;height:8px;background:var(--surface-sunken);border-radius:var(--radius-pill);overflow:hidden">
                            <span style="display:block;width:{{ round(($linha['valor'] / $maxMetodo) * 100) }}%;height:100%;background:var(--green-600);border-radius:var(--radius-pill)"></span>
                        </span>
                        <x-ui.money-value :amount="$linha['valor']" size="sm" tone="in" style="width:104px;justify-content:flex-end" />
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card title="Entradas por mês" subtitle="Últimos 5 meses">
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach($entradasPorMes as $mes)
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="width:62px;font-size:var(--text-xs);color:var(--text-muted)">{{ $mes['label'] }}</span>
                        <span style="flex:1;height:8px;background:var(--surface-sunken);border-radius:var(--radius-pill);overflow:hidden">
                            <span style="display:block;width:{{ round(($mes['valor'] / $maxMes) * 100) }}%;height:100%;background:var(--accent);border-radius:var(--radius-pill)"></span>
                        </span>
                        <x-ui.money-value :amount="$mes['valor']" size="sm" tone="muted" style="width:108px;justify-content:flex-end" />
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="A receber" subtitle="Serviços vencidos ou a vencer, ainda não pagos" :padding="0">
        <x-slot:action>
            <x-ui.tag tone="accent">{{ $totais['nAReceber'] }} serviços</x-ui.tag>
        </x-slot:action>

        <x-ui.data-table
            :columns="[
                ['key' => 'nome', 'header' => 'Serviço'],
                ['key' => 'cliente', 'header' => 'Cliente'],
                ['key' => 'vencimento', 'header' => 'Vencimento'],
                ['key' => 'periodicidade', 'header' => 'Período'],
                ['key' => 'valor', 'header' => 'Valor', 'align' => 'right'],
            ]"
            :empty="$aReceber->isEmpty()"
        >
            @foreach($aReceber as $s)
                <tr data-clickable onclick="Livewire.navigate('{{ route('servicos.show', $s) }}')">
                    <td>
                        <span style="display:flex;align-items:center;gap:8px">
                            <x-ui.service-logo :name="$s->nome" :category="$s->categoria" size="24" />
                            <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->nome }}</span>
                        </span>
                    </td>
                    <td>{{ $s->cliente?->nome }}</td>
                    <td><span class="num" style="font-size:var(--text-xs)">{{ $s->vencimento?->format('d/m/Y') ?? '—' }}</span></td>
                    <td>{{ $s->periodicidade->label() }}</td>
                    <td class="u-text-right"><x-ui.money-value :amount="$s->valor" size="sm" tone="out" /></td>
                </tr>
            @endforeach

            <x-slot:emptyState>
                <x-ui.empty-state icon="circle-check" title="Nada a receber" description="Não há serviços vencidos ou a vencer." />
            </x-slot:emptyState>
        </x-ui.data-table>
    </x-ui.card>
</div>
