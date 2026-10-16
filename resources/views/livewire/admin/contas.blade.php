<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Contas" />

    <div class="filtros">
        <x-ui.search-input width="280" wire:model.live.debounce.300ms="q" placeholder="Nome da conta ou email de um membro" :shortcut="null" />
        <div style="min-width:180px">
            <x-ui.select wire:model.live="plano" :options="array_merge([['value' => '', 'label' => 'Todos os planos']], $planos->map(fn ($p) => ['value' => (string) $p->id, 'label' => $p->nome])->all())" />
        </div>
    </div>

    <x-ui.card :padding="0">
        <x-ui.data-table :columns="[['header' => 'Conta'], ['header' => 'Plano'], ['header' => 'Membros', 'align' => 'right'], ['header' => 'Clientes', 'align' => 'right'], ['header' => 'Serviços', 'align' => 'right'], ['header' => 'Criada']]" :empty="$this->contas->isEmpty()">
            <x-slot:emptyState><x-ui.empty-state icon="search-x" title="Nenhuma conta encontrada" /></x-slot:emptyState>
            @foreach($this->contas as $c)
                <tr wire:key="conta-{{ $c->id }}">
                    <td>
                        <a href="{{ route('admin.contas.show', $c) }}" wire:navigate style="font-weight:var(--weight-medium)">{{ $c->nome }}</a>
                        @if($c->suspensa())<x-ui.tag tone="danger">suspensa</x-ui.tag>@endif
                    </td>
                    <td>
                        {{ $c->planoEfectivo()->nome }}{{ $c->em_teste ? ' (teste)' : '' }}
                        @if($c->plano_ate)<span class="lista-simples__meta"> até {{ $c->plano_ate->format('d/m/Y') }}</span>@endif
                    </td>
                    <td class="u-text-right num">{{ $c->membros_count }}</td>
                    <td class="u-text-right num">{{ $c->clientes_count }}</td>
                    <td class="u-text-right num">{{ $c->servicos_count }}</td>
                    <td class="num">{{ $c->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </x-ui.data-table>
        <x-ui.pagination :paginator="$this->contas" />
    </x-ui.card>
</div>
