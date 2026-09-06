@php
    $termo = trim($q);
@endphp

<div class="global-search">
    <x-ui.search-input wire:model.live.debounce.300ms="q" placeholder="Pesquisar cliente, serviço, domínio…" :shortcut="null" />

    @if($termo !== '')
        <div class="global-search__panel" onmousedown="event.preventDefault()">
            @if($clientesResultado->isEmpty() && $servicosResultado->isEmpty())
                <div class="global-search__empty">Sem resultados para &ldquo;{{ $termo }}&rdquo;.</div>
            @else
                @if($clientesResultado->isNotEmpty())
                    <div class="global-search__group">
                        <span class="global-search__group-label">Clientes</span>
                        @foreach($clientesResultado as $c)
                            <a href="{{ route('clientes.show', $c) }}" wire:navigate class="global-search__item">
                                <span class="global-search__avatar">{{ mb_strtoupper(mb_substr($c->nome, 0, 1)) }}</span>
                                <span class="global-search__item-text">
                                    <span class="global-search__item-title">{{ $c->nome }}</span>
                                    <span class="global-search__item-sub">{{ $c->email }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($servicosResultado->isNotEmpty())
                    <div class="global-search__group">
                        <span class="global-search__group-label">Serviços</span>
                        @foreach($servicosResultado as $s)
                            <a href="{{ route('servicos.show', $s) }}" wire:navigate class="global-search__item">
                                <x-ui.service-logo :name="$s->nome" :category="$s->categoria" size="22" />
                                <span class="global-search__item-text">
                                    <span class="global-search__item-title">{{ $s->nome }}</span>
                                    <span class="global-search__item-sub">{{ $s->cliente?->nome }} · {{ $s->plano }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
