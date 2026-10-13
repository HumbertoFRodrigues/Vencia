@php
    $r = $this->resumo;
    $fixos = $this->fixos;
    $mesStr = $this->mesActual()->format('Y-m');
    $mesAtual = $this->mesActual()->isSameMonth(now());
    $estadoRotulo = ['atraso' => 'Em atraso', 'proximo' => 'Vence em breve', 'previsto' => 'Previsto', 'confirmado' => 'Confirmado', 'ignorado' => 'Ignorado'];
    $pctConfirmados = $r['nFixos'] > 0 ? round(($r['nConfirmados'] / max(1, $r['nFixos'] - $fixos->where('estado', 'ignorado')->count())) * 100) : 0;
    $gastos = array_slice($this->gastosPorCategoria, 0, 6);
@endphp

<div class="pessoal-page">
    <x-ui.page-header eyebrow="Painel pessoal" title="Resumo">
        <x-slot:actions>
            <x-ui.month-nav :label="$this->rotuloMes()" :atual="$mesAtual" />
            <x-ui.button icon="arrow-down-to-line" wire:click="$dispatch('movimento:abrir', { tipo: 'entrada' })">Entrada</x-ui.button>
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('movimento:abrir', { tipo: 'saida' })">Saída</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card label="Entradas" :value="$r['entradas']" :currency="\App\Models\Configuracao::moeda()" tone="in" icon="arrow-down-to-line"
            :footnote="$r['entradasPendentes'] > 0 ? '+ '.\App\Services\FinancasPessoais::mzn($r['entradasPendentes']).' ainda previstas' : 'Recebido neste mês'" />
        <x-ui.stat-card label="Saídas" :value="$r['saidas']" :currency="\App\Models\Configuracao::moeda()" tone="out" icon="arrow-up"
            :footnote="$r['saidasPendentes'] > 0 ? '+ '.\App\Services\FinancasPessoais::mzn($r['saidasPendentes']).' de fixos por pagar' : 'Fixos '.\App\Services\FinancasPessoais::mzn($r['saidasFixas']).' · dia-a-dia '.\App\Services\FinancasPessoais::mzn($r['saidasVariaveis'])" />
        <x-ui.stat-card label="Saldo do mês" :value="$r['saldo']" :currency="\App\Models\Configuracao::moeda()" :tone="$r['saldo'] >= 0 ? 'in' : 'out'" icon="wallet"
            :footnote="$r['nPendentes'] > 0 ? 'Com os fixos pendentes fecha em '.\App\Services\FinancasPessoais::mzn($r['saldoProjectado']) : 'Entradas menos saídas'" />
        @php
            $prevista = $r['nPendentes'] > 0;
            $taxa = $prevista ? $r['taxaProjectada'] : $r['taxaPoupanca'];
        @endphp
        <x-ui.stat-card :label="$prevista ? 'Poupança prevista' : 'Poupança'" :value="$taxa === null ? '—' : round($taxa).'%'" :tone="$taxa !== null && $taxa >= 20 ? 'in' : ($taxa !== null && $taxa < 0 ? 'out' : 'neutral')" icon="piggy-bank"
            :footnote="$prevista ? 'No fim do mês, com os fixos que faltam · referência 20%' : 'Do que entrou · referência 20%'" />
    </div>

    <div class="pessoal-grid">
        <div class="pessoal-grid__main">
            <x-ui.card title="Fixos do mês" :subtitle="$r['nFixos'] > 0 ? $r['nConfirmados'].' de '.$r['nFixos'].' confirmados'.($r['nAtraso'] > 0 ? ' · '.$r['nAtraso'].' em atraso' : '') : 'Entradas e débitos que se repetem todos os meses'" :padding="0">
                <x-slot:action>
                    <a class="btn btn--ghost btn--sm" href="{{ route('pessoal.fixos') }}" wire:navigate>Gerir fixos <x-ui.icon name="arrow-right" size="15" /></a>
                </x-slot:action>

                @if($fixos->isEmpty())
                    <x-ui.empty-state icon="repeat" title="Ainda não tens fixos" description="Regista o teu salário, renda de casa, internet, prestações… Todos os meses aparecem aqui sozinhos para confirmares com um clique.">
                        <x-slot:action>
                            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('fixo:abrir', { tipo: 'saida' })">Adicionar fixo</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <div class="progress progress--flush" aria-label="{{ $pctConfirmados }}% dos fixos confirmados">
                        <span class="progress__fill progress__fill--ok" style="width:{{ min(100, $pctConfirmados) }}%"></span>
                    </div>
                    <ul class="fixo-list">
                        @foreach($fixos as $linha)
                            @php
                                $f = $linha['fixo'];
                                $mov = $linha['movimento'];
                                $entrada = $f->tipo === \App\Enums\TipoMovimento::Entrada;
                                $pendente = in_array($linha['estado'], ['atraso', 'proximo', 'previsto'], true);
                                $dias = (int) now()->startOfDay()->diffInDays($linha['data'], false);
                            @endphp
                            <li class="fixo-list__item fixo-list__item--{{ $linha['estado'] }}" wire:key="fixo-{{ $f->id }}">
                                <x-ui.cat-chip :categoria="$f->categoria" />
                                <div class="fixo-list__text">
                                    <span class="fixo-list__name">{{ $f->descricao }}</span>
                                    <span class="fixo-list__meta">
                                        {{ $entrada ? 'Entrada' : 'Débito' }} · dia {{ $linha['data']->day }} · {{ $f->categoria?->nome }}
                                    </span>
                                </div>
                                <span class="estado-pill estado-pill--{{ $linha['estado'] }}">
                                    @if($linha['estado'] === 'proximo')
                                        {{ $dias === 0 ? 'Hoje' : ($dias === 1 ? 'Amanhã' : 'Em '.$dias.' dias') }}
                                    @elseif($linha['estado'] === 'atraso')
                                        {{ abs($dias) === 1 ? 'Há 1 dia' : 'Há '.abs($dias).' dias' }}
                                    @else
                                        {{ $estadoRotulo[$linha['estado']] }}
                                    @endif
                                </span>
                                <span class="fixo-list__valor">
                                    @if($linha['estado'] === 'confirmado')
                                        <x-ui.money-value :amount="$mov->valor" size="sm" :tone="$entrada ? 'in' : 'out'" />
                                        @if((float) $mov->valor !== (float) $f->valor)
                                            <span class="fixo-list__previsto">previsto {{ \App\Services\FinancasPessoais::mzn((float) $f->valor) }}</span>
                                        @endif
                                    @else
                                        <x-ui.money-value :amount="$linha['valorPrevisto']" size="sm" tone="muted" :class="$linha['estado'] === 'ignorado' ? 'u-strike' : ''" />
                                    @endif
                                </span>
                                <span class="fixo-list__actions">
                                    @if($pendente)
                                        <x-ui.button size="sm" variant="{{ $linha['estado'] === 'atraso' ? 'primary' : 'secondary' }}" icon="check" wire:click="confirmarFixo({{ $f->id }})">{{ $entrada ? 'Recebi' : 'Paguei' }}</x-ui.button>
                                        <x-ui.icon-button size="sm" icon="pencil" label="Confirmar com outro valor ou data" wire:click="$dispatch('movimento:abrir', { fixoId: {{ $f->id }}, competencia: '{{ $mesStr }}' })" />
                                        <x-ui.icon-button size="sm" icon="circle-slash" label="Ignorar este mês" wire:click="ignorarFixo({{ $f->id }})" />
                                    @elseif($linha['estado'] === 'confirmado')
                                        <x-ui.icon-button size="sm" icon="pencil" label="Editar este movimento" wire:click="$dispatch('movimento:abrir', { movimentoId: {{ $mov->id }} })" />
                                        <x-ui.icon-button size="sm" icon="refresh-cw" label="Desfazer confirmação" wire:click="reporFixo({{ $f->id }})" wire:confirm="Desfazer a confirmação de «{{ $f->descricao }}»? Volta a ficar pendente neste mês." />
                                    @else
                                        <x-ui.button size="sm" variant="ghost" icon="refresh-cw" wire:click="reporFixo({{ $f->id }})">Repor</x-ui.button>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card title="Últimos movimentos" :subtitle="$r['nMovimentos'].' '.($r['nMovimentos'] === 1 ? 'movimento' : 'movimentos').' em '.$this->rotuloMes()" :padding="0">
                <x-slot:action>
                    <a class="btn btn--ghost btn--sm" href="{{ route('pessoal.movimentos', array_filter(['mes' => $mes])) }}" wire:navigate>Ver todos <x-ui.icon name="arrow-right" size="15" /></a>
                </x-slot:action>

                @if($this->ultimos->isEmpty())
                    <x-ui.empty-state icon="receipt" title="Sem movimentos neste mês" description="Os gastos do dia-a-dia (mercado, combustível, farmácia…) entram aqui. Usa o botão «Saída» lá em cima." />
                @else
                    <ul class="mov-list">
                        @foreach($this->ultimos as $m)
                            <li wire:key="mov-{{ $m->id }}">
                                <button type="button" class="mov-list__item" wire:click="$dispatch('movimento:abrir', { movimentoId: {{ $m->id }} })">
                                    <x-ui.cat-chip :categoria="$m->categoria" />
                                    <span class="mov-list__text">
                                        <span class="mov-list__name">{{ $m->descricao }}</span>
                                        <span class="mov-list__meta">{{ $m->data->format('d/m') }} · {{ $m->categoria?->nome }}@if($m->fixo_pessoal_id) · fixo @endif</span>
                                    </span>
                                    <x-ui.money-value :amount="$m->tipo === \App\Enums\TipoMovimento::Entrada ? $m->valor : -$m->valor" size="sm" :tone="$m->tipo === \App\Enums\TipoMovimento::Entrada ? 'in' : 'out'" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        <div class="pessoal-grid__side">
            <x-ui.card title="O que os números dizem" subtitle="Leitura automática do mês">
                <ul class="insight-list">
                    @foreach($this->insights as $i)
                        <li class="insight insight--{{ $i['tom'] }}">
                            <span class="insight__icon"><x-ui.icon :name="$i['icone']" size="15" /></span>
                            <span class="insight__text">{{ $i['texto'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card title="Orçamentos" subtitle="Gasto real e fixos por pagar vs limite">
                <x-slot:action>
                    <a class="btn btn--ghost btn--sm" href="{{ route('pessoal.orcamentos', array_filter(['mes' => $mes])) }}" wire:navigate>Gerir <x-ui.icon name="arrow-right" size="15" /></a>
                </x-slot:action>

                @if(empty($this->orcamentos))
                    <p class="pessoal-muted">Ainda não definiste limites. Em <a href="{{ route('pessoal.orcamentos') }}" wire:navigate>Orçamentos</a> podes dar um tecto mensal a cada categoria (ex: Alimentação 8.000) e és avisado aos 80%.</p>
                @else
                    <ul class="budget-list">
                        @foreach($this->orcamentos as $o)
                            <li class="budget budget--{{ $o['estado'] }}">
                                <div class="budget__head">
                                    <x-ui.cat-chip :categoria="$o['categoria']" label size="sm" />
                                    <span class="budget__nums"><span class="num">{{ number_format($o['gasto'], 0, ',', '.') }}</span> / <span class="num">{{ number_format($o['limite'], 0, ',', '.') }}</span></span>
                                </div>
                                <div class="progress" role="img" aria-label="{{ round($o['pct']) }}% do limite gasto">
                                    <span class="progress__fill progress__fill--{{ $o['estado'] }}" style="width:{{ min(100, $o['pct']) }}%"></span>
                                    @if($o['pctPendente'] > 0)<span class="progress__pending" style="width:{{ $o['pctPendente'] }}%"></span>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card title="Para onde foi o dinheiro" subtitle="Saídas por categoria">
                @if(empty($gastos))
                    <p class="pessoal-muted">Sem saídas registadas neste mês.</p>
                @else
                    <ul class="share-list">
                        @foreach($gastos as $g)
                            @php($fg = $g['categoria']->tinta()[1])
                            <li class="share">
                                <x-ui.cat-chip :categoria="$g['categoria']" label size="sm" class="share__label" />
                                <span class="share__bar"><span style="width:{{ max(2, round($g['pct'])) }}%;background:{{ $fg }}"></span></span>
                                <span class="share__pct num">{{ round($g['pct']) }}%</span>
                                <x-ui.money-value :amount="$g['total']" size="sm" tone="muted" class="share__valor" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>

    <livewire:pessoal.movimento-dialog />
    <livewire:pessoal.fixo-dialog />
</div>
