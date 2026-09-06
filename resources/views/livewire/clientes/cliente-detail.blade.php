@php
    use Illuminate\Support\Facades\Route;

    $c = $this->cliente;
    $servicos = $this->servicos;
    $historicos = $this->historicos;
    $resumo = $this->resumoFinanceiro;

    $servicosHref = Route::has('servicos.index') ? route('servicos.index', ['cliente' => $c->id]) : null;
@endphp

<div style="display:flex;flex-direction:column;gap:18px">
    <x-ui.page-header back="Clientes" :back-href="route('clientes.index')" :title="$c->nome">
        <x-slot:meta>
            <span style="display:inline-flex;align-items:center;gap:5px">
                <x-ui.icon name="mail" size="14" /><a href="mailto:{{ $c->email }}">{{ $c->email }}</a>
            </span>
            @if($c->tel)
                <span style="display:inline-flex;align-items:center;gap:5px">
                    <x-ui.icon name="phone" size="14" />{{ $c->tel }}
                </span>
            @endif
            @if($c->empresa)<x-ui.tag>{{ $c->empresa }}</x-ui.tag>@endif
            <span>Cliente desde {{ $c->desde->format('d/m/Y') }}</span>
        </x-slot:meta>
        <x-slot:actions>
            <x-ui.button icon="pencil" wire:click="$dispatch('cliente-form:abrir', { clienteId: {{ $c->id }} })">Editar</x-ui.button>
            @if($servicosHref)
                {{-- Serviços (Fase 4) not built yet: links straight to the future
                     "novo serviço" flow pre-filled with this client via ?cliente=. --}}
                <a href="{{ $servicosHref }}" class="btn btn--primary btn--md">
                    <x-ui.icon name="plus" size="15" class="btn__icon" />Novo serviço
                </a>
            @else
                <x-ui.button variant="primary" icon="plus" disabled title="Disponível quando Serviços (Fase 4) estiver pronto.">Novo serviço</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div style="display:grid;grid-template-columns:minmax(0,1.9fr) minmax(280px,1fr);gap:12px;align-items:start">
        <div style="display:flex;flex-direction:column;gap:12px;min-width:0">
            <div style="display:flex;align-items:baseline;justify-content:space-between">
                <h2 style="font-size:var(--text-h2);letter-spacing:var(--text-h2-ls)">Serviços</h2>
                <span style="font-size:var(--text-sm);color:var(--text-muted)">{{ $servicos->count() }} {{ $servicos->count() === 1 ? 'serviço' : 'serviços' }}</span>
            </div>

            @if($servicos->isEmpty())
                <x-ui.card :padding="0">
                    <x-ui.empty-state icon="package" title="Sem serviços" description="Este cliente ainda não tem nenhum serviço registado." />
                </x-ui.card>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(232px,1fr));gap:12px">
                    @foreach($servicos as $s)
                        @php
                            $dias = $s->dias;
                            $dueLabel = null;
                            if ($dias !== null) {
                                $dueLabel = match (true) {
                                    $dias < 0 => 'Venceu em '.$s->vencimento->format('d/m/Y'),
                                    $dias <= 10 => 'Vence em '.$dias.' '.($dias === 1 ? 'dia' : 'dias'),
                                    default => 'Vence: '.$s->vencimento->format('d/m/Y'),
                                };
                            }
                            $periodoLabel = $s->periodo === \App\Enums\Periodo::Mes ? 'mês' : 'ano';
                            $servicoHref = Route::has('servicos.show') ? route('servicos.show', $s) : null;
                        @endphp
                        <x-ui.service-card
                            :name="$s->nome"
                            :client="$s->plano"
                            :category="$s->categoria"
                            :description="$s->descricao"
                            :amount="$s->valor"
                            :period="$periodoLabel"
                            :due-label="$dueLabel"
                            :status="$s->status"
                            :href="$servicoHref"
                        />
                    @endforeach
                </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:12px">
            <x-ui.card title="Resumo financeiro">
                <div style="display:flex;flex-direction:column">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--border-subtle)">
                        <span style="font-size:var(--text-sm);color:var(--text-muted)">Total pago</span>
                        <x-ui.money-value :amount="$resumo['totalPago']" tone="in" />
                    </div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--border-subtle)">
                        <span style="font-size:var(--text-sm);color:var(--text-muted)">Total pendente</span>
                        <x-ui.money-value :amount="$resumo['totalPendente']" tone="out" />
                    </div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--border-subtle)">
                        <span style="font-size:var(--text-sm);color:var(--text-muted)">Receita mensal</span>
                        <x-ui.money-value :amount="$resumo['mrr']" />
                    </div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--border-subtle)">
                        <span style="font-size:var(--text-sm);color:var(--text-muted)">Receita anual</span>
                        <x-ui.money-value :amount="$resumo['arr']" />
                    </div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--border-subtle)">
                        <span style="font-size:var(--text-sm);color:var(--text-muted)">Último pagamento</span>
                        <span class="num" style="font-size:var(--text-sm);color:var(--text-strong)">{{ $resumo['ultimoPagamento']?->data?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:8px">
                        <span style="font-size:var(--text-sm);color:var(--text-muted)">Próximo vencimento</span>
                        <span class="num" style="font-size:var(--text-sm);color:var(--status-due-fg);font-weight:500">{{ $resumo['proximoServico']?->vencimento?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Histórico" subtitle="Nada é apagado">
                @if($historicos->isEmpty())
                    <x-ui.empty-state icon="history" title="Sem histórico" description="Ainda não há eventos registados para este cliente." />
                @else
                    <x-ui.timeline :items="$historicos->map(fn ($h) => [
                        'kind' => $h->kind,
                        'date' => $h->occurred_at->format('d/m/Y'),
                        'title' => $h->title,
                        'description' => $h->description,
                    ])" />
                @endif
            </x-ui.card>
        </div>
    </div>

    <livewire:clientes.cliente-form />
</div>
