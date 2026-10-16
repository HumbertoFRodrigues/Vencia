@php($n = $this->numeros)
<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Visão geral" />

    <div class="stats-4">
        <x-ui.stat-card label="Contas" :value="$n['contas']" :footnote="$n['novas'].' novas nos últimos 30 dias'" icon="briefcase" />
        <x-ui.stat-card label="A pagar" :value="$n['pagantes']" :footnote="$n['teste'].' em período de teste'" icon="credit-card" tone="in" />
        <x-ui.stat-card label="Receita este mês" :value="$n['receitaMes']" :currency="$faturacao->moeda()" icon="banknote" tone="in" />
        <x-ui.stat-card label="Faturas por confirmar" :value="$n['emAnalise']" :footnote="$n['utilizadores'].' utilizadores no total'" icon="hourglass" :tone="$n['emAnalise'] > 0 ? 'out' : 'neutral'" />
    </div>

    <div class="grelha-2">
        <x-ui.card title="Pagamentos por confirmar">
            <x-slot:action><a href="{{ route('admin.faturas') }}" wire:navigate class="btn btn--ghost btn--sm">Ver todos</a></x-slot:action>
            @if($this->porConfirmar->isEmpty())
                <x-ui.empty-state icon="circle-check" title="Nada por confirmar" />
            @else
                <ul class="lista-simples">
                    @foreach($this->porConfirmar as $f)
                        <li>
                            <div class="lista-simples__corpo">
                                <span class="lista-simples__titulo">{{ $f->conta->nome }}</span>
                                <span class="lista-simples__meta">{{ $f->referenciaInterna() }} · {{ $f->plano->nome }} · {{ $faturacao->valor($f) }} · ref. {{ $f->referencia ?: '—' }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card title="Contas mais recentes">
            <x-slot:action><a href="{{ route('admin.contas') }}" wire:navigate class="btn btn--ghost btn--sm">Todas</a></x-slot:action>
            <ul class="lista-simples">
                @foreach($this->recentes as $c)
                    <li>
                        <div class="lista-simples__corpo">
                            <a class="lista-simples__titulo" href="{{ route('admin.contas.show', $c) }}" wire:navigate>{{ $c->nome }}</a>
                            <span class="lista-simples__meta">{{ $c->created_at->format('d/m/Y') }} · {{ $c->membros_count }} {{ $c->membros_count === 1 ? 'membro' : 'membros' }}</span>
                        </div>
                        <x-ui.tag :tone="$c->em_teste ? 'info' : ($c->planoEfectivo()->gratuito() ? 'neutral' : 'success')">{{ $c->planoEfectivo()->nome }}{{ $c->em_teste ? ' (teste)' : '' }}</x-ui.tag>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    </div>
</div>
