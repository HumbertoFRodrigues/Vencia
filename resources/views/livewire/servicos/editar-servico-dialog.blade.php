<div>
    @if($show)
        <x-ui.dialog
            title="Editar serviço"
            description="Ajuste os dados desta assinatura. Cliente e estado não são alterados aqui."
            width="720"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:16px">
                <div style="display:flex;align-items:center;gap:14px;padding:12px;background:var(--surface-sunken);border-radius:var(--radius-md)">
                    <x-ui.service-logo :name="$nome !== '' ? $nome : 'Serviço'" :category="\App\Enums\ServicoCategoria::tryFrom($categoria) ?? \App\Enums\ServicoCategoria::Outro" size="44" />
                    <div style="flex:1;display:flex;flex-direction:column;gap:3px">
                        <strong style="font-size:var(--text-h3);color:var(--text-strong)">{{ $nome !== '' ? $nome : 'Serviço' }}</strong>
                        <span style="font-size:var(--text-xs);color:var(--text-muted)">#{{ $servicoId }}</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px">
                    <x-ui.input label="Nome" wire:model.live="nome" placeholder="Nome do serviço" :error="$errors->first('nome')" />
                    <x-ui.input label="Plano" wire:model="plano" placeholder="Opcional" :error="$errors->first('plano')" />
                </div>

                <x-ui.textarea
                    label="Descrição"
                    wire:model="descricao"
                    rows="2"
                    :error="$errors->first('descricao')"
                >{{ $descricao }}</x-ui.textarea>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.select label="Categoria" wire:model.live="categoria" :options="$this->categoriaOptions" />
                    <x-ui.input label="Valor" wire:model="valor" suffix="MZN" :error="$errors->first('valor')" />
                </div>

                <div style="display:grid;grid-template-columns:{{ $periodicidade === 'personalizada' ? 'repeat(4, 1fr)' : 'repeat(3, 1fr)' }};gap:12px">
                    <x-ui.select label="Periodicidade" wire:model.live="periodicidade" :options="$this->periodicidadeOptions" />
                    <x-ui.input label="Data de início" type="date" wire:model="inicio" :error="$errors->first('inicio')" />
                    <x-ui.input label="Vencimento" type="date" wire:model="vencimento" hint="Corrija diretamente aqui — não é recalculado automaticamente." :error="$errors->first('vencimento')" />
                    @if($periodicidade === 'personalizada')
                        <x-ui.input label="Duração" wire:model="duracaoDias" suffix="dias" :error="$errors->first('duracaoDias')" />
                    @endif
                </div>

                <div style="display:flex;flex-direction:column;gap:8px">
                    <span class="eyebrow">Como o cliente paga habitualmente</span>
                    <div class="pill-picker">
                        @foreach($this->metodos as $m)
                            <button type="button" wire:click="$set('metodoHabitual', '{{ $m }}')" class="pill-picker__item {{ $metodoHabitual === $m ? 'pill-picker__item--active' : '' }}">
                                <x-ui.payment-method :method="$m" size="22" :show-label="false" />
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar">
                    <span wire:loading.remove wire:target="guardar">Guardar alterações</span>
                    <span wire:loading wire:target="guardar">A processar…</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
