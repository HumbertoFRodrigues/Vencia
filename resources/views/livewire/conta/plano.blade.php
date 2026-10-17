@php
    $conta = $this->conta;
    $efectivo = $conta->planoEfectivo();
    $aberta = $this->faturaAberta;
    $moeda = $faturacao->moeda();
@endphp

<div class="pilha">
    <x-ui.page-header eyebrow="{{ $conta->nome }}" title="Plano e faturação" />

    @if($conta->em_teste && $conta->plano_ate)
        <div class="faixa-teste">
            <span>Estás a experimentar o plano {{ $efectivo->nome }} até {{ $conta->plano_ate->format('d/m/Y') }}. Depois a conta passa para Grátis, a não ser que escolhas um plano.</span>
        </div>
    @endif

    <div class="grelha-2">
        <x-ui.card title="O teu plano">
            <div class="pilha pilha--12">
                <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap">
                    <span style="font-size:var(--text-h2);font-weight:var(--weight-semibold);color:var(--text-strong)">{{ $efectivo->nome }}</span>
                    @if($conta->plano_ate && ! $conta->planoExpirado())
                        <x-ui.tag :tone="$conta->plano_ate->diffInDays(today()) <= 7 ? 'warning' : 'neutral'">até {{ $conta->plano_ate->format('d/m/Y') }}</x-ui.tag>
                    @elseif(! $efectivo->gratuito())
                        <x-ui.tag tone="success">sem data de fim</x-ui.tag>
                    @endif
                </div>
                <div class="uso-lista">
                    @foreach($this->uso as $u)
                        @php
                            $pct = $u['limite'] ? min(100, round($u['usado'] / max(1, $u['limite']) * 100)) : 0;
                        @endphp
                        <div class="uso">
                            <div class="uso__linha">
                                <span>{{ $u['nome'] }}</span>
                                <span class="num">{{ $u['usado'] }}{{ $u['limite'] !== null ? ' de '.$u['limite'] : ' · sem limite' }}</span>
                            </div>
                            @if($u['limite'] !== null)
                                <div class="uso__barra {{ $u['usado'] >= $u['limite'] ? 'uso__barra--cheia' : '' }}"><span style="width:{{ $pct }}%"></span></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </x-ui.card>

        @if($aberta)
            <x-ui.card :title="'Fatura '.$aberta->referenciaInterna()" :subtitle="$aberta->plano->nome.' · '.(\App\Services\Faturacao::CICLOS[$aberta->ciclo] ?? $aberta->ciclo).' · '.$faturacao->valor($aberta)">
                <x-slot:action><x-ui.tag :tone="$aberta->estadoTom()">{{ $aberta->estadoLabel() }}</x-ui.tag></x-slot:action>

                @if($aberta->estado === \App\Models\Fatura::PENDENTE)
                    <div class="pilha pilha--12">
                        @if($faturacao->gateway() instanceof \App\Contracts\CobrancaImediata)
                            <form wire:submit="pagarMpesa" class="mpesa-ja">
                                <strong>Pagar já com M-Pesa</strong>
                                <span class="pessoal-muted">Recebes no telemóvel um pedido de {{ $faturacao->valor($aberta) }}. Confirma com o teu PIN e o plano fica activo de imediato.</span>
                                <div class="mpesa-ja__linha">
                                    <x-ui.input wire:model="telefone" inputmode="tel" placeholder="84 123 4567" autocomplete="tel" :error="$errors->first('telefone')" aria-label="Número M-Pesa" />
                                    <x-ui.button type="submit" variant="primary" icon="smartphone" wire:loading.attr="disabled" wire:target="pagarMpesa">
                                        <span wire:loading.remove wire:target="pagarMpesa">Pagar</span>
                                        <span wire:loading wire:target="pagarMpesa">Confirma no telemóvel…</span>
                                    </x-ui.button>
                                </div>
                            </form>
                            <p class="pessoal-muted" style="margin:0">Ou paga de outra forma:</p>
                        @endif
                        <p class="pessoal-muted" style="margin:0">1. Paga {{ $faturacao->valor($aberta) }} por um destes meios, com a referência <strong>{{ $aberta->referenciaInterna() }}</strong>:</p>
                        <ul class="instrucoes">
                            @foreach($faturacao->gateway()->instrucoes($aberta) as $i)
                                <li><strong>{{ $i['titulo'] }}</strong>{{ $i['detalhe'] }}</li>
                            @endforeach
                        </ul>
                        <p class="pessoal-muted" style="margin:0">2. Diz-nos como pagaste:</p>
                        <form wire:submit="enviar" class="pilha pilha--12">
                            <x-ui.select label="Pagaste por" wire:model="metodo" :options="[
                                ['value' => 'mpesa', 'label' => 'M-Pesa'],
                                ['value' => 'emola', 'label' => 'e-Mola'],
                                ['value' => 'transferencia', 'label' => 'Transferência bancária'],
                                ['value' => 'outro', 'label' => 'Outro'],
                            ]" />
                            <x-ui.input label="Referência da transacção" wire:model="referencia" placeholder="Ex.: o código da mensagem de confirmação" :error="$errors->first('referencia')" />
                            <div class="field">
                                <label class="field__label" for="comprovativo">Comprovativo (opcional)</label>
                                <input id="comprovativo" type="file" wire:model="comprovativo" accept="image/*,application/pdf">
                                <span class="field__hint {{ $errors->has('comprovativo') ? 'field__hint--error' : '' }}">{{ $errors->first('comprovativo') ?: 'Fotografia ou PDF até 4 MB.' }}</span>
                            </div>
                            <div class="linha-accoes">
                                <x-ui.button variant="ghost" size="sm" wire:click="cancelar">Cancelar pedido</x-ui.button>
                                <x-ui.button type="submit" variant="primary" size="sm" icon="send">Enviar para confirmação</x-ui.button>
                            </div>
                        </form>
                    </div>
                @else
                    <p class="pessoal-muted" style="margin:0">Recebemos a referência {{ $aberta->referencia ?: '(comprovativo anexado)' }} a {{ $aberta->enviada_em?->format('d/m/Y H:i') }}. Assim que o pagamento for confirmado, o plano fica activo e recebes um email.</p>
                @endif
            </x-ui.card>
        @endif
    </div>

    @unless($aberta)
        <x-ui.card title="Mudar de plano" subtitle="Paga por M-Pesa, e-Mola ou transferência. O tempo pago soma-se ao que ainda tens.">
            <x-slot:action>
                <x-ui.tabs :active="$ciclo" wire-method="setCiclo" :tabs="[['id' => 'mensal', 'label' => 'Mensal'], ['id' => 'anual', 'label' => 'Anual']]" />
            </x-slot:action>
            <div class="planos-grid">
                @foreach($this->planos as $plano)
                    <div class="pilha pilha--12">
                        @include('conta.partials.plano-card', ['plano' => $plano, 'moeda' => $moeda, 'destaque' => $plano->id === $efectivo->id, 'etiqueta' => $plano->id === $efectivo->id ? 'Actual' : null])
                        @if(! $plano->gratuito() && $plano->preco($ciclo) > 0)
                            <x-ui.button variant="primary" :full-width="true" wire:click="escolher({{ $plano->id }})">
                                {{ $plano->id === $efectivo->id ? 'Renovar' : 'Escolher' }} {{ $plano->nome }} · {{ number_format($plano->preco($ciclo), 0, ',', '.') }} {{ $moeda }}/{{ $ciclo === 'anual' ? 'ano' : 'mês' }}
                            </x-ui.button>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endunless

    <x-ui.card title="Faturas" :padding="0">
        @if($this->faturas->isEmpty())
            <x-ui.empty-state icon="receipt" title="Ainda sem faturas" />
        @else
            <x-ui.data-table :columns="[['header' => 'Fatura'], ['header' => 'Plano'], ['header' => 'Valor', 'align' => 'right'], ['header' => 'Estado'], ['header' => 'Período']]">
                @foreach($this->faturas as $f)
                    <tr wire:key="fatura-{{ $f->id }}">
                        <td class="num">{{ $f->referenciaInterna() }}</td>
                        <td>{{ $f->plano->nome }} · {{ \App\Services\Faturacao::CICLOS[$f->ciclo] ?? $f->ciclo }}</td>
                        <td class="u-text-right num">{{ $faturacao->valor($f) }}</td>
                        <td><x-ui.tag :tone="$f->estadoTom()">{{ $f->estadoLabel() }}</x-ui.tag>@if($f->estado === 'rejeitada' && $f->nota)<span class="lista-simples__meta" style="display:block">{{ $f->nota }}</span>@endif</td>
                        <td class="num">
                            @if($f->periodo_inicio){{ $f->periodo_inicio->format('d/m/Y') }} – {{ $f->periodo_fim->format('d/m/Y') }}@else — @endif
                            @if($f->estado === 'paga') · <a href="{{ route('faturas.recibo', $f) }}">recibo</a>@endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        @endif
    </x-ui.card>
</div>
