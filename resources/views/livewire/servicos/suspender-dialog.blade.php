<div>
    @if($show)
        <x-ui.dialog
            title="Suspender serviço"
            description="O histórico é mantido; os lembretes param."
            width="420"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:12px">
                <x-ui.select label="Motivo" wire:model="motivo" :options="$this->motivoOptions" />
                <x-ui.input label="Data da suspensão" type="date" wire:model="data" :error="$errors->first('data')" />
                <x-ui.input label="Observação" wire:model="observacao" placeholder="Registo interno" />
                <x-ui.checkbox
                    label="Enviar email de aviso ao cliente"
                    description="Utiliza o template &ldquo;Serviço terminado&rdquo;."
                    :checked="$enviarEmail"
                    wire:click.prevent="$toggle('enviarEmail')"
                />
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="danger" icon="pause" wire:click="confirmar" wire:loading.attr="disabled" wire:target="confirmar">
                    <span wire:loading.remove wire:target="confirmar">Suspender acesso</span>
                    <span wire:loading wire:target="confirmar">A processar…</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
