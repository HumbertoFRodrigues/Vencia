@php
    $t = $this->totais;
    $grupos = [
        'entrada' => ['titulo' => 'Entradas fixas', 'sub' => 'O que recebes todos os meses (salário, rendas, retiradas…)', 'vazio' => 'Ainda não registaste nenhuma fonte de renda fixa.', 'botao' => 'Entrada fixa', 'icone' => 'arrow-down-to-line'],
        'saida' => ['titulo' => 'Débitos fixos', 'sub' => 'O que pagas todos os meses (renda, contas, prestações, mensalidades…)', 'vazio' => 'Ainda não registaste nenhum débito fixo.', 'botao' => 'Débito fixo', 'icone' => 'plus'],
    ];
@endphp

<div class="pessoal-page">
    <x-ui.page-header eyebrow="Painel pessoal" title="Fixos mensais">
        <x-slot:actions>
            <x-ui.button icon="arrow-down-to-line" wire:click="$dispatch('fixo:abrir', { tipo: 'entrada' })">Entrada fixa</x-ui.button>
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('fixo:abrir', { tipo: 'saida' })">Débito fixo</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card label="Renda fixa / mês" :value="$t['entradas']" :currency="\App\Models\Configuracao::moeda()" tone="in" icon="arrow-down-to-line" footnote="Soma das entradas fixas activas" />
        <x-ui.stat-card label="Débitos fixos / mês" :value="$t['saidas']" :currency="\App\Models\Configuracao::moeda()" tone="out" icon="repeat" footnote="Soma dos débitos fixos activos" />
        <x-ui.stat-card label="Sobra depois dos fixos" :value="$t['sobra']" :currency="\App\Models\Configuracao::moeda()" :tone="$t['sobra'] >= 0 ? 'in' : 'out'" icon="wallet" footnote="Para o dia-a-dia e poupança" />
        <x-ui.stat-card label="Peso dos fixos" :value="$t['peso'] === null ? '—' : round($t['peso']).'%'" :tone="$t['peso'] !== null && $t['peso'] >= 70 ? 'out' : ($t['peso'] !== null && $t['peso'] <= 50 ? 'in' : 'neutral')" icon="target"
            :footnote="$t['peso'] === null ? 'Regista a tua renda fixa' : ($t['peso'] >= 70 ? 'Alto — pouca margem' : ($t['peso'] <= 50 ? 'Saudável (abaixo de 50%)' : 'Razoável — ideal abaixo de 50%'))" />
    </div>

    @foreach($grupos as $tipo => $g)
        @php($lista = $this->fixos->get($tipo, collect()))
        <x-ui.card :title="$g['titulo']" :subtitle="$g['sub']" :padding="0">
            <x-slot:action>
                <x-ui.button size="sm" variant="ghost" icon="plus" wire:click="$dispatch('fixo:abrir', { tipo: '{{ $tipo }}' })">{{ $g['botao'] }}</x-ui.button>
            </x-slot:action>

            <x-ui.data-table
                :columns="[
                    ['key' => 'nome', 'header' => 'Nome'],
                    ['key' => 'categoria', 'header' => 'Categoria'],
                    ['key' => 'dia', 'header' => 'Dia'],
                    ['key' => 'meio', 'header' => 'Meio'],
                    ['key' => 'estado', 'header' => 'Estado'],
                    ['key' => 'valor', 'header' => 'Valor / mês', 'align' => 'right'],
                ]"
                :empty="$lista->isEmpty()"
            >
                @foreach($lista as $f)
                    <tr data-clickable wire:key="fx-{{ $f->id }}" wire:click="$dispatch('fixo:abrir', { fixoId: {{ $f->id }} })" tabindex="0" onkeydown="if (event.key === 'Enter') this.click()" @class(['row--muted' => ! $f->activo])>
                        <td>
                            <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $f->descricao }}</span>
                            @if($f->notas)<span style="display:block;font-size:var(--text-xs);color:var(--text-muted);margin-top:2px">{{ $f->notas }}</span>@endif
                        </td>
                        <td><x-ui.cat-chip :categoria="$f->categoria" label size="sm" /></td>
                        <td><span class="num" style="font-size:var(--text-xs)">Dia {{ $f->dia_mes }}</span></td>
                        <td>
                            @if($f->meio)
                                <x-ui.payment-method :method="$f->meio" size="20" />
                            @else
                                <span style="color:var(--text-faint)">—</span>
                            @endif
                        </td>
                        <td>
                            @if($f->activo)
                                <span class="estado-pill estado-pill--confirmado">Activo</span>
                            @else
                                <span class="estado-pill estado-pill--ignorado">Pausado</span>
                            @endif
                        </td>
                        <td class="u-text-right"><x-ui.money-value :amount="$f->valor" size="sm" :tone="$tipo === 'entrada' ? 'in' : 'out'" /></td>
                    </tr>
                @endforeach

                <x-slot:emptyState>
                    <x-ui.empty-state icon="repeat" :title="$g['vazio']" description="Regista uma vez e passa a aparecer todos os meses no Resumo para confirmares com um clique.">
                        <x-slot:action>
                            <x-ui.button variant="primary" :icon="$g['icone']" wire:click="$dispatch('fixo:abrir', { tipo: '{{ $tipo }}' })">Adicionar {{ mb_strtolower($g['botao']) }}</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </x-slot:emptyState>
            </x-ui.data-table>
        </x-ui.card>
    @endforeach

    <livewire:pessoal.fixo-dialog />
</div>
