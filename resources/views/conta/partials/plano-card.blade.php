{{-- One plan: price and what it includes. Shared by the public home and the Plano page. --}}
@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');
@endphp
<div class="plano-card {{ ($destaque ?? false) ? 'plano-card--destaque' : '' }}">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
        <h3 class="plano-card__nome">{{ $plano->nome }}</h3>
        @isset($etiqueta)<x-ui.tag tone="success">{{ $etiqueta }}</x-ui.tag>@endisset
    </div>
    <div class="plano-card__preco">
        @if($plano->gratuito())
            0 <small>{{ $moeda }} · para sempre</small>
        @else
            {{ $fmt($plano->preco_mensal) }} <small>{{ $moeda }} / mês</small>
        @endif
    </div>
    @if(! $plano->gratuito() && (float) $plano->preco_anual > 0)
        <span class="pessoal-muted" style="margin-top:-8px">ou {{ $fmt($plano->preco_anual) }} {{ $moeda }} / ano</span>
    @endif
    @if($plano->descricao)<p class="plano-card__desc">{{ $plano->descricao }}</p>@endif
    <ul>
        <li><x-ui.icon name="check" size="15" color="var(--money-in)" /> Painel pessoal completo</li>
        @foreach(\App\Models\Plano::LIMITES as $chave => $nome)
            <li><x-ui.icon name="check" size="15" color="var(--money-in)" />
                {{ $plano->textoLimite($chave) }}
            </li>
        @endforeach
        @foreach(\App\Models\Plano::FUNCOES as $chave => $nome)
            <li class="{{ $plano->permite($chave) ? '' : 'nao' }}">
                <x-ui.icon :name="$plano->permite($chave) ? 'check' : 'x'" size="15" :color="$plano->permite($chave) ? 'var(--money-in)' : 'var(--text-faint)'" /> {{ $nome }}
            </li>
        @endforeach
    </ul>
</div>
