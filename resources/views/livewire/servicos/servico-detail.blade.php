@php
    use App\Enums\Periodicidade;
    use App\Enums\Periodo;
    use App\Enums\ServicoStatus;

    $s = $this->servico;
    $c = $s->cliente;
    $dias = $s->dias;
    $historicos = $this->historicos;
    $lembretes = $this->lembretesEfetivos;
    $labels = $this->intervaloLabels;
    $periodoLabel = $s->periodicidade === Periodicidade::Unica ? null : ($s->periodo === Periodo::Mes ? 'mês' : 'ano');
@endphp

<div style="display:flex;flex-direction:column;gap:18px">
    <x-ui.page-header :back="$c->nome" :back-href="route('clientes.show', $c)">
        <x-slot:titleHtml>
            <span style="display:inline-flex;align-items:center;gap:12px">
                <x-ui.service-logo :name="$s->nome" :category="$s->categoria" size="38" />{{ $s->nome }}
            </span>
        </x-slot:titleHtml>
        <x-slot:meta>
            <x-ui.tag>{{ $s->plano ?? $s->nome }}</x-ui.tag>
            <x-ui.tag tone="outline">{{ $s->periodicidade->label() }}</x-ui.tag>
            <span>{{ $c->email }}</span>
        </x-slot:meta>
        <x-slot:actions>
            @if($s->periodicidade !== Periodicidade::Unica)
                <x-ui.button variant="primary" icon="refresh-cw" wire:click="$dispatch('renovar:abrir', { servicoId: {{ $s->id }} })">Renovar</x-ui.button>
            @endif
            <x-ui.button icon="banknote" wire:click="$dispatch('pagamento:abrir')">Registrar pagamento</x-ui.button>
            <x-ui.button icon="pencil" disabled title="Disponível em breve.">Editar</x-ui.button>
            @if(! in_array($s->status, [ServicoStatus::Suspenso, ServicoStatus::Cancelado], true))
                <x-ui.button variant="danger" icon="pause" wire:click="$dispatch('suspender:abrir', { servicoId: {{ $s->id }} })">Suspender</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if($s->status === ServicoStatus::Vencido)
        <x-ui.alert-banner tone="danger" title="Este acesso terminou">
            Terminou em {{ $s->vencimento?->format('d/m/Y') ?? '—' }}. É necessário renovar ou suspender.
        </x-ui.alert-banner>
    @elseif($s->status === ServicoStatus::Suspenso)
        @php($susp = $this->ultimaSuspensao)
        <x-ui.alert-banner tone="warning" title="Serviço suspenso em {{ $susp?->data?->format('d/m/Y') ?? '—' }}">
            Motivo: {{ $susp?->motivo?->label() ?? '—' }}. Os lembretes estão parados.
        </x-ui.alert-banner>
    @elseif($s->status === ServicoStatus::AVencer)
        @php($proximoAviso = $this->proximoAvisoDias)
        <x-ui.alert-banner tone="warning" title="Vence em {{ $dias }} {{ $dias === 1 ? 'dia' : 'dias' }}">
            @if($proximoAviso !== null)
                Próximo aviso automático: {{ $proximoAviso }} {{ $proximoAviso === 1 ? 'dia' : 'dias' }} antes do vencimento.
            @else
                Nenhum lembrete automático está activo para este serviço.
            @endif
        </x-ui.alert-banner>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.9fr) minmax(280px,1fr);gap:12px;align-items:start">
        <div style="display:flex;flex-direction:column;gap:12px;min-width:0">
            <x-ui.card title="Assinatura" :subtitle="$s->descricao">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:18px">
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Cliente</span>
                        <span style="font-size:var(--text-body-size);color:var(--text-strong);font-weight:var(--weight-medium)">{{ $c->nome }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Categoria</span>
                        <span style="font-size:var(--text-body-size);color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->categoria->label() }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Método habitual</span>
                        @if($s->metodo_habitual)
                            <x-ui.payment-method :method="$s->metodo_habitual" size="20" />
                        @else
                            <span style="color:var(--text-faint)">—</span>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Valor</span>
                        <x-ui.money-value :amount="$s->valor" :period="$periodoLabel" />
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Período</span>
                        <span style="font-size:var(--text-body-size);color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->periodicidade->label() }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Início</span>
                        <span class="num" style="font-size:var(--text-body-size);color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->inicio->format('d/m/Y') }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Vencimento</span>
                        <span class="num" style="font-size:var(--text-body-size);color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->vencimento?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Próximo aviso</span>
                        <span class="num" style="font-size:var(--text-body-size);color:var(--text-strong);font-weight:var(--weight-medium)">{{ $s->proximo_aviso?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <span class="eyebrow">Estado</span>
                        <x-ui.status-badge :status="$s->status" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Lembretes" subtitle="Intervalos activos para este serviço">
                <div style="display:flex;gap:16px;flex-wrap:wrap">
                    @foreach($labels as $key => $label)
                        <x-ui.checkbox :label="$label" :checked="$lembretes[$key]" wire:click.prevent="alternarLembrete('{{ $key }}')" />
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <x-ui.card title="Histórico do serviço" subtitle="Registo imutável">
            @if($historicos->isEmpty())
                <x-ui.empty-state icon="history" title="Sem histórico" description="Ainda não há eventos registados para este serviço." />
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

    <livewire:servicos.renovar-dialog />
    <livewire:servicos.suspender-dialog />
    <livewire:pagamentos.registrar-pagamento-dialog :servico-id="$s->id" />
</div>
