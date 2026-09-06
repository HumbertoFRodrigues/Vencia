@php
    use App\Enums\Periodicidade;
    use App\Enums\Periodo;

    $servicos = $this->servicos;
    $counts = $this->counts;
    $categorias = $this->categorias;
    $biblioteca = $this->biblioteca;
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header :eyebrow="$counts['todos'].' serviços'" title="Serviços">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" wire:click="$dispatch('assinatura:abrir')">Nova assinatura</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <x-ui.tabs
            style="flex:1;min-width:300px"
            :active="$tab"
            :tabs="[
                ['id' => 'todos', 'label' => 'Todos', 'count' => $counts['todos']],
                ['id' => 'activo', 'label' => 'Activos', 'count' => $counts['activo']],
                ['id' => 'a_vencer', 'label' => 'A vencer', 'count' => $counts['a_vencer']],
                ['id' => 'vencido', 'label' => 'Vencidos', 'count' => $counts['vencido']],
                ['id' => 'suspenso', 'label' => 'Suspensos', 'count' => $counts['suspenso']],
            ]"
        />
        <div style="width:190px">
            <x-ui.select wire:model.live="categoria" :options="$categorias" />
        </div>
        <x-ui.search-input width="230" wire:model.live.debounce.300ms="q" placeholder="Serviço ou cliente" :shortcut="null" />
    </div>

    @if($servicos->isEmpty())
        <x-ui.card :padding="0">
            <x-ui.empty-state icon="search-x" title="Sem serviços" description="Nenhum serviço corresponde aos filtros aplicados." />
        </x-ui.card>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(268px,1fr));gap:12px">
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
                    $periodoLabel = $s->periodicidade === Periodicidade::Unica
                        ? null
                        : ($s->periodo === Periodo::Mes ? 'mês' : 'ano');
                @endphp
                <x-ui.service-card
                    :name="$s->nome"
                    :client="$s->cliente->nome"
                    :category="$s->categoria"
                    :description="$s->descricao"
                    :amount="$s->valor"
                    :period="$periodoLabel"
                    :due-label="$dueLabel"
                    :status="$s->status"
                    :href="route('servicos.show', $s)"
                />
            @endforeach
        </div>
    @endif

    <x-ui.card title="Biblioteca de serviços" subtitle="Serviços conhecidos com logo pronto — selecione um ao criar a assinatura">
        <div class="pill-picker">
            @foreach($biblioteca as $item)
                <span class="pill-picker__item pill-picker__item--static">
                    <x-ui.service-logo :name="$item->nome" :category="$item->categoria" size="22" />{{ $item->nome }}
                </span>
            @endforeach
        </div>
    </x-ui.card>

    <livewire:servicos.new-subscription-dialog />
</div>
