@use('App\Services\FinancasPessoais', 'FP')
@php
    $saldos = $this->saldos;
    $total = collect($saldos)->sum('saldo') + $this->semMeio;
    $metas = $this->metas;
@endphp

<div class="pessoal-page">
    <x-ui.page-header eyebrow="Painel pessoal" title="Saldos e metas">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" wire:click="abrirMeta">Nova meta</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tile-grid">
        <x-ui.stat-card label="Tens no total" :value="$total" :currency="\App\Models\Configuracao::moeda()" :tone="$total >= 0 ? 'in' : 'out'" icon="wallet" footnote="Soma das contas, sem o que já guardaste em metas" />
        <x-ui.stat-card label="Guardado em metas" :value="collect($metas)->sum('guardado')" :currency="\App\Models\Configuracao::moeda()" icon="piggy-bank" :footnote="count($metas).' '.(count($metas) === 1 ? 'meta' : 'metas')" />
    </div>

    <div class="pessoal-grid">
        <div class="pessoal-grid__main">
            <x-ui.card title="Metas de poupança" subtitle="Cada valor que guardas entra como saída em «Poupança & Investimento»" :padding="0">
                @if(empty($metas))
                    <x-ui.empty-state icon="target" title="Ainda não tens metas" description="Ex.: «Fundo de emergência — 100.000 até Dezembro». Vês quanto tens de guardar por mês para chegar lá.">
                        <x-slot:action>
                            <x-ui.button variant="primary" icon="plus" wire:click="abrirMeta">Criar meta</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <ul class="metas-list">
                        @foreach($metas as $l)
                            @php($m = $l['meta'])
                            <li class="meta" wire:key="meta-{{ $m->id }}">
                                <div class="meta__head">
                                    <div>
                                        <strong class="meta__nome">{{ $m->nome }}</strong>
                                        <span class="meta__info">
                                            @if($m->concluida_em)
                                                Atingida em {{ $m->concluida_em->format('d/m/Y') }}
                                            @elseif($l['atrasada'])
                                                Prazo passou ({{ $m->data_alvo->format('m/Y') }}) — faltam {{ FP::mzn($l['falta']) }}
                                            @elseif($l['porMes'])
                                                Até {{ $m->data_alvo->format('m/Y') }} · guarda {{ FP::mzn($l['porMes']) }} por mês
                                            @else
                                                Sem prazo · faltam {{ FP::mzn($l['falta']) }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="meta__accoes">
                                        @unless($m->concluida_em)
                                            <x-ui.button size="sm" icon="plus" wire:click="abrirContribuicao({{ $m->id }})">Guardar</x-ui.button>
                                        @endunless
                                        <x-ui.icon-button size="sm" icon="pencil" label="Editar meta" wire:click="abrirMeta({{ $m->id }})" />
                                    </div>
                                </div>
                                <div class="progress" role="img" aria-label="{{ round($l['pct']) }}% da meta">
                                    <span class="progress__fill {{ $m->concluida_em ? 'progress__fill--ok' : ($l['atrasada'] ? 'progress__fill--alerta' : 'progress__fill--meta') }}" style="width:{{ $l['pct'] }}%"></span>
                                </div>
                                <span class="meta__nums"><span class="num">{{ FP::mzn($l['guardado']) }}</span> de <span class="num">{{ FP::mzn((float) $m->valor_alvo) }}</span> · {{ round($l['pct']) }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        <div class="pessoal-grid__side">
            <x-ui.card title="Saldos por conta" subtitle="Calculados a partir dos movimentos. Acerta uma vez com o valor real e depois actualiza sozinho." :padding="0">
                <ul class="contas-list">
                    @foreach($saldos as $c)
                        <li wire:key="conta-{{ md5($c['meio']) }}">
                            @if($acertando === $c['meio'])
                                <div class="contas-list__acerto">
                                    <x-ui.payment-method :method="$c['meio']" size="20" />
                                    <x-ui.input wire:model="valorActual" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" size="sm" :error="$errors->first('valorActual')" aria-label="Quanto tens agora" autofocus />
                                    <x-ui.icon-button size="sm" icon="check" label="Guardar" wire:click="guardarAcerto" />
                                    <x-ui.icon-button size="sm" icon="x" label="Cancelar" wire:click="cancelarAcerto" />
                                </div>
                            @else
                                <x-ui.payment-method :method="$c['meio']" size="20" />
                                <span class="contas-list__saldo">
                                    <x-ui.money-value :amount="$c['saldo']" size="sm" :tone="$c['saldo'] < 0 ? 'out' : 'neutral'" />
                                    @unless($c['definido'])<span class="contas-list__hint">saldo inicial por acertar</span>@endunless
                                </span>
                                <x-ui.icon-button size="sm" icon="pencil" label="Acertar saldo" wire:click="acertar({{ \Illuminate\Support\Js::from($c['meio']) }})" />
                            @endif
                        </li>
                    @endforeach
                    @if(abs($this->semMeio) > 0.004)
                        <li>
                            <span class="contas-list__semmeio">Movimentos sem meio</span>
                            <span class="contas-list__saldo"><x-ui.money-value :amount="$this->semMeio" size="sm" tone="muted" /></span>
                            <span style="width:28px"></span>
                        </li>
                    @endif
                </ul>
            </x-ui.card>
        </div>
    </div>

    @if($metaAberta)
        <x-ui.dialog :title="$metaId ? 'Editar meta' : 'Nova meta'" description="Quanto queres juntar e, se quiseres, até quando." width="420" wire:click="$set('metaAberta', false)">
            <x-slot:close><x-ui.icon-button icon="x" label="Fechar" wire:click="$set('metaAberta', false)" /></x-slot:close>
            <div style="display:flex;flex-direction:column;gap:12px">
                <x-ui.input label="Nome" wire:model="metaNome" placeholder="Ex: Fundo de emergência, Carro, Férias" :error="$errors->first('metaNome')" autofocus />
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Valor a juntar" wire:model="metaValor" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" :error="$errors->first('metaValor')" />
                    <x-ui.input label="Até (opcional)" type="month" wire:model="metaData" :error="$errors->first('metaData')" />
                </div>
            </div>
            <x-slot:footer>
                @if($metaId)
                    <x-ui.button variant="ghost" icon="trash-2" class="action-danger-quiet" style="margin-right:auto" wire:click="apagarMeta" wire:confirm="Remover esta meta? O dinheiro já guardado continua registado nos movimentos.">Remover</x-ui.button>
                @endif
                <x-ui.button wire:click="$set('metaAberta', false)">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardarMeta">Guardar</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif

    @if($contribuicaoAberta)
        <x-ui.dialog title="Guardar na meta" description="Fica registado como saída em «Poupança & Investimento»." width="440" wire:click="$set('contribuicaoAberta', false)">
            <x-slot:close><x-ui.icon-button icon="x" label="Fechar" wire:click="$set('contribuicaoAberta', false)" /></x-slot:close>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Quanto" wire:model="contribuicaoValor" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" :error="$errors->first('contribuicaoValor')" autofocus />
                    <x-ui.input label="Data" type="date" wire:model="contribuicaoData" :error="$errors->first('contribuicaoData')" />
                </div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    <span class="field__label">De onde saiu <span style="color:var(--text-faint);font-weight:var(--weight-regular)">(opcional)</span></span>
                    <div class="pill-picker">
                        @foreach($this->meios as $mm)
                            <button type="button" wire:click="$set('contribuicaoMeio', {{ \Illuminate\Support\Js::from($contribuicaoMeio === $mm ? '' : $mm) }})" class="pill-picker__item {{ $contribuicaoMeio === $mm ? 'pill-picker__item--active' : '' }}">
                                <x-ui.payment-method :method="$mm" size="20" />
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <x-ui.button wire:click="$set('contribuicaoAberta', false)">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardarContribuicao">Guardar</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif

</div>
