{{-- Livewire requires exactly one root element at all times, so the
     always-present wrapper lives here and the dialog itself is the
     conditional part inside it (rather than @if-ing the whole template,
     which leaves no root tag when $show is false). --}}
<div>
    @if($show)
        {{-- wire:click on the root forwards through $attributes to the scrim div;
             the panel's own onclick stops propagation, so this only fires for an
             actual backdrop click ("click-outside closes", per the brief). --}}
        <x-ui.dialog :title="$clienteId ? 'Editar cliente' : 'Novo cliente'" width="440" wire:click="fechar">
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:14px">
                <x-ui.input label="Nome" wire:model="nome" placeholder="Nome do cliente" :error="$errors->first('nome')" />
                <x-ui.input label="Email" type="email" wire:model="email" placeholder="email@exemplo.com" :error="$errors->first('email')" />
                <x-ui.input label="Telefone" wire:model="tel" placeholder="+258 8… … ….." :error="$errors->first('tel')" />
                <x-ui.input label="Empresa" wire:model="empresa" placeholder="Opcional" :error="$errors->first('empresa')" />
                <x-ui.select
                    label="Estado"
                    wire:model="status"
                    :options="[
                        ['value' => 'activo', 'label' => 'Activo'],
                        ['value' => 'inactivo', 'label' => 'Inactivo'],
                    ]"
                />
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar">
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">A processar…</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
