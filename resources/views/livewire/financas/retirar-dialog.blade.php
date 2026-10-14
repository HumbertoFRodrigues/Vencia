<div>
    @if($show)
        @php($disponivel = $this->disponivel)
        <x-ui.dialog title="Retirar para pessoal" description="Passa dinheiro da empresa para as tuas finanças pessoais. Entra automaticamente como entrada no painel Pessoal." width="460" wire:click="fechar">
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:12px">
                <div class="retirada-disponivel">
                    <span>Disponível na empresa</span>
                    <x-ui.money-value :amount="$disponivel" size="md" :tone="$disponivel >= 0 ? 'in' : 'out'" />
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Quanto retirar" wire:model.live.debounce.300ms="valor" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" :error="$errors->first('valor')" autofocus />
                    <x-ui.input label="Data" type="date" wire:model="data" :error="$errors->first('data')" />
                </div>

                @if($this->valorNumerico > $disponivel)
                    <p class="field__hint field__hint--aviso">Este valor é maior do que o disponível registado ({{ \App\Services\FinancasPessoais::mzn($disponivel) }}). Podes continuar se a empresa tiver dinheiro que não passou pela Vencia.</p>
                @endif

                <div style="display:flex;flex-direction:column;gap:6px">
                    <span class="field__label">Para onde <span style="color:var(--text-faint);font-weight:var(--weight-regular)">(opcional)</span></span>
                    <div class="pill-picker">
                        @foreach($this->meios as $m)
                            <button type="button" wire:click="$set('meio', {{ \Illuminate\Support\Js::from($meio === $m ? '' : $m) }})" class="pill-picker__item {{ $meio === $m ? 'pill-picker__item--active' : '' }}" aria-pressed="{{ $meio === $m ? 'true' : 'false' }}">
                                <x-ui.payment-method :method="$m" size="20" />
                            </button>
                        @endforeach
                    </div>
                </div>

                <x-ui.input label="Nota (opcional)" wire:model="nota" placeholder="Ex: salário de Outubro, despesas da casa" :error="$errors->first('nota')" />
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="confirmar">Retirar</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
