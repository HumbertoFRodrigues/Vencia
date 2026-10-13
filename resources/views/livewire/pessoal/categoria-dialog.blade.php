<div>
    @if($show)
        <x-ui.dialog
            :title="$categoriaId ? 'Editar categoria' : 'Nova categoria'"
            :description="$tipo === 'saida' ? 'Categoria de saídas. Define um limite mensal para seres avisado antes de passares.' : 'Categoria de entradas (de onde vem o teu dinheiro).'"
            width="480"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:14px">
                <div style="display:flex;align-items:flex-end;gap:12px">
                    @php($tinta = $cores[$cor] ?? $cores['cinza'])
                    <span class="cat-chip cat-chip--lg" style="background:{{ $tinta[0] }};color:{{ $tinta[1] }}" aria-hidden="true">
                        <x-ui.icon :name="$icone" size="20" />
                    </span>
                    <div style="flex:1">
                        <x-ui.input label="Nome" wire:model="nome" placeholder="Ex: Combustível, Ginásio, Igreja" :error="$errors->first('nome')" autofocus />
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px">
                    <span class="field__label">Ícone</span>
                    <div class="icon-grid" role="radiogroup" aria-label="Ícone">
                        @foreach($icones as $i)
                            <button type="button" role="radio" aria-checked="{{ $icone === $i ? 'true' : 'false' }}" aria-label="{{ $i }}" wire:click="$set('icone', '{{ $i }}')" class="icon-grid__item {{ $icone === $i ? 'icon-grid__item--active' : '' }}">
                                <x-ui.icon :name="$i" size="17" />
                            </button>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px">
                    <span class="field__label">Cor</span>
                    <div class="swatches" role="radiogroup" aria-label="Cor">
                        @foreach($cores as $chave => [$bg, $fg])
                            <button type="button" role="radio" aria-checked="{{ $cor === $chave ? 'true' : 'false' }}" aria-label="{{ $chave }}" wire:click="$set('cor', '{{ $chave }}')" class="swatches__item {{ $cor === $chave ? 'swatches__item--active' : '' }}" style="background:{{ $bg }};color:{{ $fg }}">
                                <span class="swatches__dot" style="background:{{ $fg }}"></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if($tipo === 'saida')
                    <x-ui.input label="Limite mensal (opcional)" wire:model="limite" inputmode="decimal" :suffix="\App\Models\Configuracao::moeda()" hint="Deixa vazio para não ter limite. Avisamos aos 80% e quando passares." :error="$errors->first('limite')" />
                @endif

                @if($categoriaId)
                    <x-ui.switch label="Arquivar — deixa de aparecer nas escolhas, mas mantém o histórico" wire:model="arquivado" :checked="$arquivado" />
                @endif
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardar">Guardar</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
