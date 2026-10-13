<div>
    @if($show)
        <x-ui.dialog
            :title="$this->titulo"
            :description="$fixoId ? 'Débito/entrada fixa — ajusta o valor se este mês foi diferente.' : 'Regista um movimento do teu dinheiro pessoal.'"
            width="460"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:12px">
                @unless($fixoId)
                    <div class="seg" role="radiogroup" aria-label="Tipo de movimento">
                        <button type="button" role="radio" aria-checked="{{ $tipo === 'saida' ? 'true' : 'false' }}" wire:click="setTipo('saida')" class="seg__item {{ $tipo === 'saida' ? 'seg__item--saida' : '' }}">
                            <x-ui.icon name="arrow-up" size="14" /> Saída
                        </button>
                        <button type="button" role="radio" aria-checked="{{ $tipo === 'entrada' ? 'true' : 'false' }}" wire:click="setTipo('entrada')" class="seg__item {{ $tipo === 'entrada' ? 'seg__item--entrada' : '' }}">
                            <x-ui.icon name="arrow-down-to-line" size="14" /> Entrada
                        </button>
                    </div>
                @endunless

                <x-ui.input label="Descrição" wire:model="descricao" :placeholder="$tipo === 'entrada' ? 'Ex: Salário de Outubro' : 'Ex: Mercado, Combustível, Farmácia'" :error="$errors->first('descricao')" autofocus />

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Valor" wire:model="valor" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" :error="$errors->first('valor')" />
                    <x-ui.input label="Data" type="date" wire:model="data" :error="$errors->first('data')" />
                </div>

                <div class="field">
                    <x-ui.select label="Categoria" wire:model="categoriaId" :options="$this->categoriaOptions" />
                    @error('categoriaId')<span class="field__hint field__hint--error">{{ $message }}</span>@enderror
                </div>

                <div style="display:flex;flex-direction:column;gap:6px">
                    <span class="field__label">Meio <span style="color:var(--text-faint);font-weight:var(--weight-regular)">(opcional)</span></span>
                    <div class="pill-picker">
                        @foreach($this->meios as $m)
                            <button type="button" wire:click="$set('meio', '{{ $meio === $m ? '' : $m }}')" class="pill-picker__item {{ $meio === $m ? 'pill-picker__item--active' : '' }}" aria-pressed="{{ $meio === $m ? 'true' : 'false' }}">
                                <x-ui.payment-method :method="$m" size="20" />
                            </button>
                        @endforeach
                    </div>
                </div>

                <x-ui.input label="Nota (opcional)" wire:model="notas" placeholder="Algum detalhe para te lembrares depois" />
            </div>

            <x-slot:footer>
                @if($movimentoId)
                    <x-ui.button variant="ghost" icon="trash-2" class="action-danger-quiet" style="margin-right:auto" wire:click="apagar" wire:confirm="Apagar este movimento? Esta acção não pode ser desfeita.">Apagar</x-ui.button>
                @endif
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardar">
                    {{ $fixoId && ! $movimentoId ? 'Confirmar' : 'Guardar' }}
                </x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
