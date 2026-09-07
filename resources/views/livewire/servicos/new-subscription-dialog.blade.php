<div>
    @if($show)
        <x-ui.dialog
            title="Nova assinatura"
            description="Escolha um serviço da biblioteca — nome, logo e categoria são preenchidos automaticamente."
            width="720"
            wire:click="fechar"
        >
            <x-slot:close>
                <x-ui.icon-button icon="x" label="Fechar" wire:click="fechar" />
            </x-slot:close>

            <div style="display:flex;flex-direction:column;gap:16px">
                <div style="display:flex;flex-direction:column;gap:8px">
                    <span class="eyebrow">Biblioteca de serviços</span>
                    <div class="pill-picker">
                        @foreach($this->biblioteca as $item)
                            <button
                                type="button"
                                wire:click="escolherBiblioteca({{ $item->id }})"
                                class="pill-picker__item {{ $bibliotecaServicoId === $item->id ? 'pill-picker__item--active' : '' }}"
                            >
                                <x-ui.service-logo :name="$item->nome" :category="$item->categoria" size="20" />{{ $item->nome }}
                            </button>
                        @endforeach
                    </div>
                    @error('bibliotecaServicoId')<span class="field__hint field__hint--error">{{ $message }}</span>@enderror
                </div>

                @if($this->bibliotecaSelecionada)
                    @php($svc = $this->bibliotecaSelecionada)
                    <div style="display:flex;align-items:center;gap:14px;padding:12px;background:var(--surface-sunken);border-radius:var(--radius-md)">
                        <x-ui.service-logo :name="$svc->nome" :category="$svc->categoria" size="44" />
                        <div style="flex:1;display:flex;flex-direction:column;gap:3px">
                            <strong style="font-size:var(--text-h3);color:var(--text-strong)">{{ $svc->nome }}</strong>
                            <span style="display:flex;align-items:center;gap:6px">
                                <x-ui.tag>{{ $svc->categoria->label() }}</x-ui.tag>
                                <span style="font-size:var(--text-xs);color:var(--text-muted)">preenchido pela biblioteca</span>
                            </span>
                        </div>
                    </div>
                @endif

                <div style="display:grid;grid-template-columns:{{ $this->novoCliente ? '1fr 1fr 1fr' : '1fr' }};gap:12px">
                    <x-ui.select label="Cliente" wire:model.live="clienteSelecionado" :options="$this->clientesOptions" />
                    @if($this->novoCliente)
                        <x-ui.input label="Nome do novo cliente" wire:model="novoClienteNome" placeholder="Nome completo" :error="$errors->first('novoClienteNome')" />
                        <x-ui.input label="Email" type="email" wire:model="novoClienteEmail" placeholder="cliente@email.com" :error="$errors->first('novoClienteEmail')" />
                    @endif
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                    <x-ui.input label="Valor" wire:model="valor" suffix="MZN" :error="$errors->first('valor')" />
                    <x-ui.select label="Periodicidade" wire:model.live="periodicidade" :options="$this->periodicidadeOptions" />
                    <x-ui.input label="Data de início" type="date" wire:model.live="inicio" :error="$errors->first('inicio')" />
                    @if($periodicidade === 'personalizada')
                        <x-ui.input label="Duração" wire:model.live="duracaoDias" suffix="dias" :error="$errors->first('duracaoDias')" />
                    @endif
                </div>

                @if($periodicidade !== 'unica')
                    <x-ui.input
                        label="Data de vencimento (opcional)"
                        type="date"
                        wire:model.live="vencimentoManual"
                        :error="$errors->first('vencimentoManual')"
                        hint="Deixe em branco para calcular a partir da data de início. Preencha só se este serviço já existe e o vencimento real é outro — por exemplo, um cliente que está a migrar para o sistema."
                    />
                @endif

                <div style="padding:10px 12px;background:var(--surface-sunken);border-radius:var(--radius-sm);display:flex;align-items:center;gap:8px;font-size:var(--text-sm);color:var(--text-body)">
                    <x-ui.icon name="calendar-check" size="15" color="var(--text-muted)" />
                    @if($periodicidade === 'unica')
                        Sem vencimento — periodicidade única, sem ciclo de renovação.
                    @elseif(trim($vencimentoManual) !== '')
                        Vencimento definido manualmente: <strong class="num" style="color:var(--text-strong)">{{ \Illuminate\Support\Carbon::parse($vencimentoManual)->format('d/m/Y') }}</strong>
                    @elseif($this->vencimentoCalculado)
                        Vencimento calculado: <strong class="num" style="color:var(--text-strong)">{{ $this->vencimentoCalculado->format('d/m/Y') }}</strong>
                    @else
                        Preencha a data de início para calcular o vencimento.
                    @endif
                </div>

                <x-ui.textarea
                    label="Breve descrição do serviço para este cliente"
                    wire:model="descricao"
                    rows="2"
                    hint="Aparece no cartão do serviço, na página do cliente e nos emails de aviso."
                >{{ $descricao }}</x-ui.textarea>

                <div style="display:flex;flex-direction:column;gap:8px">
                    <span class="eyebrow">Como o cliente paga</span>
                    <div class="pill-picker">
                        @foreach($this->metodos as $m)
                            <button type="button" wire:click="$set('metodoHabitual', '{{ $m }}')" class="pill-picker__item {{ $metodoHabitual === $m ? 'pill-picker__item--active' : '' }}">
                                <x-ui.payment-method :method="$m" size="22" :show-label="false" />
                            </button>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px;font-size:var(--text-sm);color:var(--text-muted)">
                    <x-ui.icon name="bell" size="15" />Lembretes automáticos: 30, 15, 7, 3 e 1 dia antes, no dia e após o vencimento.
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button wire:click="fechar">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="check" wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar">
                    <span wire:loading.remove wire:target="guardar">Criar assinatura</span>
                    <span wire:loading wire:target="guardar">A processar…</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
</div>
