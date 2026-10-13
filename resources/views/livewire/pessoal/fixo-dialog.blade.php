<div>
    @if($show)
        <x-ui.dialog
            :title="$fixoId ? 'Editar fixo' : ($tipo === 'entrada' ? 'Nova entrada fixa' : 'Novo débito fixo')"
            description="Repete-se todos os meses. Aparece no resumo como previsto até o confirmares."
            width="480"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:12px">
                @unless($fixoId)
                    <div class="seg" role="radiogroup" aria-label="Tipo de fixo">
                        <button type="button" role="radio" aria-checked="{{ $tipo === 'saida' ? 'true' : 'false' }}" wire:click="setTipo('saida')" class="seg__item {{ $tipo === 'saida' ? 'seg__item--saida' : '' }}">
                            <x-ui.icon name="arrow-up" size="14" /> Débito fixo
                        </button>
                        <button type="button" role="radio" aria-checked="{{ $tipo === 'entrada' ? 'true' : 'false' }}" wire:click="setTipo('entrada')" class="seg__item {{ $tipo === 'entrada' ? 'seg__item--entrada' : '' }}">
                            <x-ui.icon name="arrow-down-to-line" size="14" /> Entrada fixa
                        </button>
                    </div>
                @endunless

                <x-ui.input label="Nome" wire:model="descricao" :placeholder="$tipo === 'entrada' ? 'Ex: Salário, Renda do apartamento' : 'Ex: Renda de casa, Internet, Prestação do carro'" :error="$errors->first('descricao')" autofocus />

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Valor mensal" wire:model="valor" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" :error="$errors->first('valor')" />
                    <x-ui.select label="Dia do mês" wire:model="diaMes" :options="$this->diasOptions" hint="Se o mês for mais curto, conta o último dia." />
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.select label="Categoria" wire:model="categoriaId" :options="$this->categoriaOptions" />
                    <x-ui.input label="A partir de" type="month" wire:model="inicio" :error="$errors->first('inicio')" />
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

                <x-ui.input label="Nota (opcional)" wire:model="notas" placeholder="Ex: contrato até Dezembro, referência do pagamento" />

                @if($fixoId)
                    <x-ui.switch label="Activo — continua a aparecer todos os meses" wire:model="activo" :checked="$activo" />
                @endif
            </div>

            <x-slot:footer>
                @if($fixoId)
                    <x-ui.button variant="ghost" icon="trash-2" class="action-danger-quiet" style="margin-right:auto" wire:click="apagar" wire:confirm="Remover este fixo? Os meses já confirmados continuam no histórico.">Remover</x-ui.button>
                @endif
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardar">Guardar</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
