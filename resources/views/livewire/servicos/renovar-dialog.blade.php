<div>
    @if($show)
        @php($s = $this->servico)
        <x-ui.dialog
            title="Renovar serviço"
            :description="$s ? $s->nome.' — período '.mb_strtolower($s->periodicidade->label()) : null"
            width="440"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <x-ui.input label="Valor pago" wire:model="valorPago" suffix="MZN" :error="$errors->first('valorPago')" />
                <x-ui.input label="Data do pagamento" type="date" wire:model="dataPagamento" :error="$errors->first('dataPagamento')" />
                <x-ui.select label="Período" wire:model.live="periodoPagamento" :options="$this->periodoOptions" />
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
                <div style="grid-column:1 / -1">
                    <x-ui.input label="Observações" wire:model="observacoes" placeholder="Opcional" />
                </div>
            </div>

            <div style="margin-top:12px;padding:10px 12px;background:var(--surface-sunken);border-radius:var(--radius-sm);display:flex;align-items:center;gap:8px;font-size:var(--text-sm);color:var(--text-body)">
                <x-ui.icon name="calendar-check" size="15" color="var(--text-muted)" />
                @if($this->novoVencimento)
                    Novo vencimento calculado: <strong class="num" style="color:var(--text-strong)">{{ $this->novoVencimento->format('d/m/Y') }}</strong>
                @else
                    Este serviço não tem ciclo de renovação (periodicidade única).
                @endif
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="confirmar">Confirmar renovação</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
