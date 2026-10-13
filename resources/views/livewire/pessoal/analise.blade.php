@use('App\Services\FinancasPessoais', 'FP')
@php
    $t = $this->totais;
    $serie = $this->serie;
    $ano = $this->anoActual();
    $max = $t['max'];
    $abrev = function (float $v): string {
        $a = abs($v);
        return match (true) {
            $a >= 1_000_000 => str_replace('.', ',', rtrim(rtrim(number_format($v / 1_000_000, 1, '.', ''), '0'), '.')).' M',
            $a >= 1_000 => number_format($v / 1_000, 0, ',', '.').' mil',
            default => number_format($v, 0, ',', '.'),
        };
    };
    $sim = $this->simulacao;
    $baseMedia = $t['mesesMedia'] > 0 ? ' / mês ('.$t['mesesMedia'].' '.($t['mesesMedia'] === 1 ? 'mês' : 'meses completos').')' : ' / mês';
    $comparacao = $ano === (int) now()->year ? 'Jan–'.ucfirst(now()->locale('pt_PT')->translatedFormat('M')).' '.($ano - 1) : (string) ($ano - 1);
    $temDados = $t['entradas'] > 0 || $t['saidas'] > 0;
@endphp

<div class="pessoal-page">
    <x-ui.page-header eyebrow="Painel pessoal" title="Análise">
        <x-slot:actions>
            <x-ui.month-nav :label="'Ano '.$ano" :atual="$ano === (int) now()->year" anterior="mudarAno(-1)" seguinte="mudarAno(1)" hoje="irParaEsteAno" hoje-label="Este ano" />
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card label="Entradas no ano" :value="$t['entradas']" :currency="\App\Models\Configuracao::moeda()" tone="in" icon="arrow-down-to-line" :footnote="'Média '.FP::mzn($t['mediaEntradas']).$baseMedia" />
        <x-ui.stat-card label="Saídas no ano" :value="$t['saidas']" :currency="\App\Models\Configuracao::moeda()" tone="out" icon="arrow-up" :footnote="'Média '.FP::mzn($t['mediaSaidas']).$baseMedia" />
        <x-ui.stat-card label="Guardado no ano" :value="$t['saldo']" :currency="\App\Models\Configuracao::moeda()" :tone="$t['saldo'] >= 0 ? 'in' : 'out'" icon="piggy-bank" :footnote="$t['taxa'] === null ? 'Sem entradas registadas' : round($t['taxa']).'% do que entrou'" />
        <x-ui.stat-card label="Peso dos fixos nas saídas" :value="$t['pctFixo'] === null ? '—' : round($t['pctFixo']).'%'" icon="repeat" :footnote="$t['pctFixo'] === null ? 'Sem saídas registadas' : 'O resto ('.round(100 - $t['pctFixo']).'%) é dia-a-dia'" />
    </div>

    <x-ui.card :title="'Mês a mês em '.$ano" :subtitle="$t['melhor'] && $t['pior'] && $t['melhor'] !== $t['pior'] ? 'Melhor mês: '.$t['melhor']['label'].' ('.FP::mzn($t['melhor']['saldo']).') · Mais apertado: '.$t['pior']['label'].' ('.FP::mzn($t['pior']['saldo']).')' : 'Entradas e saídas de cada mês'">
        <x-slot:action>
            <div class="chart-legend" aria-hidden="true">
                <span class="chart-legend__item"><span class="chart-legend__swatch chart-legend__swatch--in"></span>Entradas</span>
                <span class="chart-legend__item"><span class="chart-legend__swatch chart-legend__swatch--out"></span>Saídas</span>
            </div>
        </x-slot:action>

        @if(! $temDados)
            <x-ui.empty-state icon="chart-line" :title="'Sem movimentos em '.$ano" description="Assim que registares entradas e saídas, aqui vês a evolução mês a mês." />
        @else
            <div class="bar-chart" role="img" aria-label="Entradas e saídas por mês em {{ $ano }}. Os valores exactos estão na tabela abaixo.">
                <div class="bar-chart__grid" aria-hidden="true">
                    @foreach([1, 0.5, 0] as $f)
                        <div class="bar-chart__gridline" style="bottom:{{ $f * 100 }}%"><span>{{ $abrev($max * $f) }}</span></div>
                    @endforeach
                </div>
                <div class="bar-chart__cols">
                    @foreach($serie as $m)
                        <div class="bar-chart__col {{ $m['futuro'] ? 'bar-chart__col--futuro' : '' }}" tabindex="{{ $m['futuro'] ? '-1' : '0' }}">
                            <div class="bar-chart__bars">
                                <span class="bar-chart__bar bar-chart__bar--in" style="height:{{ $m['entradas'] > 0 ? max(1, $m['entradas'] / $max * 100) : 0 }}%"></span>
                                <span class="bar-chart__bar bar-chart__bar--out" style="height:{{ $m['saidas'] > 0 ? max(1, $m['saidas'] / $max * 100) : 0 }}%"></span>
                            </div>
                            <span class="bar-chart__x">{{ $m['label'] }}</span>
                            @unless($m['futuro'])
                                <span class="bar-chart__tip" role="tooltip">
                                    <strong>{{ ucfirst($m['mes']->locale('pt_PT')->translatedFormat('F')) }}</strong>
                                    <span><i class="chart-legend__swatch chart-legend__swatch--in"></i>Entradas <b class="num">{{ FP::mzn($m['entradas']) }}</b></span>
                                    <span><i class="chart-legend__swatch chart-legend__swatch--out"></i>Saídas <b class="num">{{ FP::mzn($m['saidas']) }}</b></span>
                                    <span class="bar-chart__tip-saldo">Saldo <b class="num">{{ FP::mzn($m['saldo']) }}</b></span>
                                </span>
                            @endunless
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-ui.card>

    <div class="pessoal-grid">
        <div class="pessoal-grid__main">
            <x-ui.card title="Onde gastas" :subtitle="'Saídas por categoria em '.$ano.', comparadas com o mesmo período de '.$comparacao" :padding="0">
                <x-ui.data-table :columns="[
                    ['key' => 'cat', 'header' => 'Categoria'],
                    ['key' => 'total', 'header' => 'Total', 'align' => 'right'],
                    ['key' => 'media', 'header' => 'Média / mês', 'align' => 'right'],
                    ['key' => 'pct', 'header' => '% das saídas', 'align' => 'right'],
                    ['key' => 'vs', 'header' => 'vs '.$comparacao, 'align' => 'right'],
                ]" :empty="empty($this->categoriasSaida)">
                    @foreach($this->categoriasSaida as $c)
                        @php($delta = $c['anterior'] > 0 ? (($c['total'] - $c['anterior']) / $c['anterior']) * 100 : null)
                        <tr wire:key="as-{{ $c['categoria']->id }}">
                            <td><x-ui.cat-chip :categoria="$c['categoria']" label size="sm" /></td>
                            <td class="u-text-right"><x-ui.money-value :amount="$c['total']" size="sm" tone="out" /></td>
                            <td class="u-text-right"><x-ui.money-value :amount="$c['media']" size="sm" tone="muted" /></td>
                            <td class="u-text-right"><span class="num">{{ round($c['pct']) }}%</span></td>
                            <td class="u-text-right">
                                @if($delta === null)
                                    <span style="color:var(--text-faint)">—</span>
                                @else
                                    <span class="delta {{ $delta > 5 ? 'delta--up' : ($delta < -5 ? 'delta--down' : '') }}">{{ $delta > 0 ? '+' : '' }}{{ round($delta) }}%</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <x-slot:emptyState>
                        <x-ui.empty-state icon="receipt" :title="'Sem saídas em '.$ano" />
                    </x-slot:emptyState>
                </x-ui.data-table>
            </x-ui.card>

            <x-ui.card title="Detalhe mensal" subtitle="Os mesmos números do gráfico, só os meses com movimentos" :padding="0">
                <x-ui.data-table :columns="[
                    ['key' => 'mes', 'header' => 'Mês'],
                    ['key' => 'e', 'header' => 'Entradas', 'align' => 'right'],
                    ['key' => 's', 'header' => 'Saídas', 'align' => 'right'],
                    ['key' => 'saldo', 'header' => 'Saldo', 'align' => 'right'],
                    ['key' => 'taxa', 'header' => 'Guardado', 'align' => 'right'],
                ]" dense :empty="! $temDados">
                    @foreach($serie as $m)
                        @continue($m['entradas'] == 0 && $m['saidas'] == 0)
                        <tr wire:key="am-{{ $m['mes']->month }}">
                            <td>{{ ucfirst($m['mes']->locale('pt_PT')->translatedFormat('F')) }}</td>
                            <td class="u-text-right"><x-ui.money-value :amount="$m['entradas']" size="sm" :tone="$m['entradas'] > 0 ? 'in' : 'muted'" /></td>
                            <td class="u-text-right"><x-ui.money-value :amount="$m['saidas']" size="sm" :tone="$m['saidas'] > 0 ? 'out' : 'muted'" /></td>
                            <td class="u-text-right"><x-ui.money-value :amount="$m['saldo']" size="sm" :tone="$m['saldo'] > 0 ? 'in' : ($m['saldo'] < 0 ? 'out' : 'muted')" /></td>
                            <td class="u-text-right"><span class="num">{{ $m['taxa'] === null ? '—' : round($m['taxa']).'%' }}</span></td>
                        </tr>
                    @endforeach
                    <x-slot:emptyState>
                        <x-ui.empty-state icon="list" :title="'Sem movimentos em '.$ano" />
                    </x-slot:emptyState>
                </x-ui.data-table>
            </x-ui.card>
        </div>

        <div class="pessoal-grid__side">
            <x-ui.card title="Simular uma decisão" subtitle="E se cortares num gasto ou ganhares mais?">
                @if(! $sim)
                    <p class="pessoal-muted">Precisa de pelo menos um mês com entradas e saídas registadas para simular.</p>
                @else
                    <div class="sim">
                        <p class="pessoal-muted" style="margin:0">Base: média dos últimos {{ $sim['meses'] === 1 ? 'mês' : $sim['meses'].' meses' }} — entra <strong class="num">{{ FP::mzn($sim['entradas']) }}</strong>, sai <strong class="num">{{ FP::mzn($sim['saidas']) }}</strong>.</p>

                        @if(! empty($this->simOpcoes))
                            <x-ui.select label="Cortar em" wire:model.live="simCategoria" :options="$this->simOpcoes" />
                            <label class="sim__range">
                                <span class="field__label">Corte de <strong class="num">{{ $simCorte }}%</strong></span>
                                <input type="range" min="0" max="100" step="5" wire:model.live="simCorte" aria-label="Percentagem de corte">
                            </label>
                        @endif
                        <label class="sim__range">
                            <span class="field__label">Aumento de rendimento de <strong class="num">{{ $simAumento }}%</strong></span>
                            <input type="range" min="0" max="100" step="5" wire:model.live="simAumento" aria-label="Percentagem de aumento de rendimento">
                        </label>

                        <div class="sim__result">
                            <div class="sim__row"><span>Hoje guardas por mês</span><b class="num">{{ FP::mzn($sim['poupancaHoje']) }} <small>({{ round($sim['taxaHoje']) }}%)</small></b></div>
                            <div class="sim__row"><span>Com esta decisão</span><b class="num">{{ FP::mzn($sim['poupancaNova']) }} <small>({{ round($sim['taxaNova']) }}%)</small></b></div>
                            <div class="sim__row sim__row--destaque"><span>Diferença</span><b class="num">+{{ FP::mzn($sim['ganhoMes']) }} / mês · +{{ FP::mzn($sim['ganhoMes'] * 12) }} / ano</b></div>
                            @if($sim['poupancaCorte'] > 0 && $sim['ganhoAumento'] > 0)
                                <p class="pessoal-muted" style="margin:0">{{ FP::mzn($sim['poupancaCorte']) }} vêm do corte e {{ FP::mzn($sim['ganhoAumento']) }} do aumento.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="De onde vem o dinheiro" :subtitle="'Entradas por categoria em '.$ano">
                @if(empty($this->categoriasEntrada))
                    <p class="pessoal-muted">Sem entradas registadas em {{ $ano }}.</p>
                @else
                    <ul class="share-list">
                        @foreach($this->categoriasEntrada as $c)
                            <li class="share">
                                <x-ui.cat-chip :categoria="$c['categoria']" label size="sm" class="share__label" />
                                <span class="share__bar"><span style="width:{{ max(2, round($c['pct'])) }}%;background:{{ $c['categoria']->tinta()[1] }}"></span></span>
                                <span class="share__pct num">{{ round($c['pct']) }}%</span>
                                <x-ui.money-value :amount="$c['total']" size="sm" tone="muted" class="share__valor" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
