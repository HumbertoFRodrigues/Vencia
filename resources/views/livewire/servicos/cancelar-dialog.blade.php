<div>
    @if($show)
        <x-ui.dialog
            title="Cancelar serviço"
            description="Esta acção é permanente. O serviço deixa de aparecer nas listas activas e não pode ser reactivado."
            width="420"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:12px">
                <x-ui.input
                    label="Motivo do cancelamento"
                    wire:model="motivo"
                    placeholder="Porque é que este serviço está a ser cancelado"
                    :error="$errors->first('motivo')"
                />
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Voltar</x-ui.button>
                <x-ui.button variant="danger" icon="circle-slash" wire:click="confirmar" wire:loading.attr="disabled" wire:target="confirmar">
                    <span wire:loading.remove wire:target="confirmar">Cancelar definitivamente</span>
                    <span wire:loading wire:target="confirmar">A processar…</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
