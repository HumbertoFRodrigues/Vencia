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
            <x-ui.button variant="primary" icon="wallet" wire:click="$dispatch('retirada:abrir')">Retirar para pessoal</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card
            label="Entradas"
            :value="$totais['entradas']"
            tone="in"
            icon="arrow-down-to-line"
            :footnote="$totais['nPagamentos'].' pagamentos em '.$mesLabel"
        />
        <x-ui.stat-card
            label="A receber"
            :value="$totais['aReceber']"
            tone="out"
            icon="hourglass"
            :footnote="$totais['nAReceber'].' serviços'"
        />
        <x-ui.stat-card label="Receita mensal recorrente" :value="$totais['mrr']" icon="repeat" />
        <x-ui.stat-card label="Receita anual recorrente" :value="$totais['arr']" icon="chart-line" />
        <x-ui.stat-card label="Disponível na empresa" :value="$this->retiradasTotais['disponivel']" :currency="\App\Models\Configuracao::moeda()" :tone="$this->retiradasTotais['disponivel'] >= 0 ? 'in' : 'out'" icon="landmark" footnote="Tudo o que os clientes pagaram menos as retiradas" />
        <x-ui.stat-card label="Retirado este mês" :value="$this->retiradasTotais['mes']" :currency="\App\Models\Configuracao::moeda()" icon="wallet" footnote="Passado para o painel pessoal" />
    </div>

    <x-ui.card title="Retiradas para conta pessoal" subtitle="Cada retirada entra automaticamente como entrada no painel Pessoal" :padding="0">
        <x-slot:action>
            <x-ui.button size="sm" variant="ghost" icon="plus" wire:click="$dispatch('retirada:abrir')">Retirar</x-ui.button>
        </x-slot:action>

        <x-ui.data-table :columns="[
            ['key' => 'data', 'header' => 'Data'],
            ['key' => 'meio', 'header' => 'Para onde'],
            ['key' => 'nota', 'header' => 'Nota'],
            ['key' => 'valor', 'header' => 'Valor', 'align' => 'right'],
            ['key' => 'accoes', 'header' => '', 'align' => 'right'],
        ]" :empty="$this->retiradas->isEmpty()">
            @foreach($this->retiradas as $r)
                <tr wire:key="ret-{{ $r->id }}">
                    <td><span class="num" style="font-size:var(--text-xs)">{{ $r->data->format('d/m/Y') }}</span></td>
                    <td>
                        @if($r->meio)
                            <x-ui.payment-method :method="$r->meio" size="20" />
                        @else
                            <span style="color:var(--text-faint)">—</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted)">{{ $r->nota ?? '—' }}</td>
                    <td class="u-text-right"><x-ui.money-value :amount="$r->valor" size="sm" tone="out" /></td>
                    <td class="u-text-right">
                        <x-ui.icon-button size="sm" icon="trash-2" label="Anular retirada" class="action-danger-quiet"
                            wire:click="anularRetirada({{ $r->id }})"
                            wire:confirm="Anular esta retirada? A entrada correspondente também sai do painel pessoal." />
                    </td>
                </tr>
            @endforeach
            <x-slot:emptyState>
                <x-ui.empty-state icon="wallet" title="Ainda não fizeste retiradas" description="Quando tirares dinheiro da empresa para ti, regista aqui — fica logo como entrada no teu painel pessoal." />
            </x-slot:emptyState>
        </x-ui.data-table>
    </x-ui.card>

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

    <livewire:financas.retirar-dialog />
</div>
