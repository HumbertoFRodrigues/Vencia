<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Planos">
        <x-slot:actions>
            <x-ui.button size="sm" variant="primary" icon="plus" wire:click="novo">Novo plano</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert-banner tone="info">Mudar um preço não altera o que já foi pago: vale para os próximos pedidos. Os limites aplicam-se de imediato a todas as contas desse plano (nada é apagado; só impede acrescentar mais).</x-ui.alert-banner>

    <div class="planos-grid">
        @foreach($planos as $plano)
            <div class="pilha pilha--12" wire:key="plano-{{ $plano->id }}">
                @include('conta.partials.plano-card', ['plano' => $plano, 'moeda' => $moeda, 'etiqueta' => $plano->activo ? null : 'inactivo'])
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                    <span class="lista-simples__meta">{{ $plano->contas_count }} {{ $plano->contas_count === 1 ? 'conta' : 'contas' }}{{ $plano->activo ? '' : ' · não aparece a clientes novos' }}</span>
                    <x-ui.button size="sm" icon="pencil" wire:click="editar({{ $plano->id }})">Editar</x-ui.button>
                </div>
            </div>
        @endforeach
    </div>

    @if($aberto)
        <x-ui.dialog :title="$editandoId ? 'Editar plano' : 'Novo plano'" width="520" wire:click="fechar">
            <x-slot:close><x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" /></x-slot:close>
            <form wire:submit="guardar" class="pilha pilha--12">
                <x-ui.input label="Nome" wire:model="nome" :error="$errors->first('nome')" />
                <x-ui.input label="Descrição curta" wire:model="descricao" :error="$errors->first('descricao')" />
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Preço mensal" wire:model="precoMensal" inputmode="decimal" :suffix="$moeda" :error="$errors->first('precoMensal')" />
                    <x-ui.input label="Preço anual" wire:model="precoAnual" inputmode="decimal" :suffix="$moeda" :error="$errors->first('precoAnual')" />
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                    @foreach(\App\Models\Plano::LIMITES as $chave => $nomeLimite)
                        <x-ui.input :label="$nomeLimite" wire:model="limites.{{ $chave }}" inputmode="numeric" placeholder="sem limite" :error="$errors->first('limites.'.$chave)" />
                    @endforeach
                </div>
                @foreach(\App\Models\Plano::FUNCOES as $chave => $nomeFuncao)
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm)"><input type="checkbox" wire:model="funcoes.{{ $chave }}"> {{ $nomeFuncao }}</label>
                @endforeach
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end">
                    <x-ui.input label="Ordem" type="number" wire:model="ordem" :error="$errors->first('ordem')" />
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm);padding-bottom:10px"><input type="checkbox" wire:model="activo"> Disponível para escolher</label>
                </div>
                <div class="linha-accoes">
                    <x-ui.button size="sm" variant="ghost" wire:click="fechar">Cancelar</x-ui.button>
                    <x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button>
                </div>
            </form>
        </x-ui.dialog>
    @endif
</div>
