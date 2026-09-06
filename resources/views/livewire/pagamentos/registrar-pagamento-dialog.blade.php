<div>
    @if($show)
        @php($s = $this->servico)
        <x-ui.dialog
            title="Registrar pagamento"
            :description="$s ? $s->nome.' — '.($s->cliente?->nome ?? '') : 'Registo avulso de um pagamento recebido'"
            width="440"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                @unless($this->bloqueado)
                    <x-ui.select label="Cliente" wire:model.live="clienteSelecionado" :options="$this->clientesOptions" />
                    <x-ui.select label="Serviço" wire:model.live="servicoSelecionado" :options="$this->servicosOptions" />
                @endunless
                <x-ui.input label="Valor" wire:model="valor" suffix="MZN" :error="$errors->first('valor')" />
                <x-ui.input label="Data do pagamento" type="date" wire:model="data" :error="$errors->first('data')" />
                <x-ui.select label="Período" wire:model.live="periodo" :options="$this->periodoOptions" />
                <div style="display:flex;flex-direction:column;gap:6px">
                    <span class="field__label">Método</span>
                    <div class="pill-picker">
                        @foreach(['mpesa', 'emola', 'transferencia', 'dinheiro'] as $m)
                            <button type="button" wire:click="$set('metodo', '{{ $m }}')" class="pill-picker__item {{ $metodo === $m ? 'pill-picker__item--active' : '' }}">
                                <x-ui.payment-method :method="$m" size="22" :show-label="false" />
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="confirmar">Confirmar pagamento</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
