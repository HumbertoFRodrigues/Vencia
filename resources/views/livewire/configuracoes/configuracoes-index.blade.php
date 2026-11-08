@php
    use App\Models\Configuracao;
    use App\View\Components\Ui\PaymentMethod;
    use Illuminate\Support\Facades\Storage;

    $itens = $this->itensBiblioteca;
    $editandoItem = $this->editandoItem;
    $itensMetodos = $this->itensMetodos;
    $editandoMetodoItem = $this->editandoMetodoItem;
    $lembretes = $this->lembretesGlobais;
    $intervalos = $this->intervaloLabels;
    $empresaConfig = Configuracao::obter('empresa', []);
@endphp

<div style="display:flex;flex-direction:column;gap:16px">
    <x-ui.page-header :eyebrow="\App\Support\ContaActual::get()?->nome ?? 'Empresa'" title="Configurações" />

    <x-ui.tabs
        :active="$tab"
        :tabs="[
            ['id' => 'biblioteca', 'label' => 'Biblioteca de Serviços', 'count' => $itens->count()],
            ['id' => 'lembretes', 'label' => 'Lembretes'],
            ['id' => 'empresa', 'label' => 'Empresa'],
        ]"
    />

    @if($tab === 'biblioteca')
        <div style="display:flex;flex-direction:column;gap:12px">
            <x-ui.card title="Adicionar serviço à biblioteca" subtitle="Logo, categoria e descrição ficam guardados para reutilizar em qualquer cliente">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start">
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <x-ui.input label="Nome do serviço" wire:model="novoNome" placeholder="Ex.: Notion" :error="$errors->first('novoNome')" />
                        <x-ui.select label="Categoria" wire:model="novaCategoria" :options="$this->categoriaOptions" />
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <div>
                            <label style="cursor:pointer;display:inline-block">
                                <input type="file" wire:model="novoLogo" accept="image/*" style="display:none" />
                                <x-ui.logo-slot
                                    :size="64"
                                    :src="$novoLogo ? $novoLogo->temporaryUrl() : null"
                                    label="Adicionar logo personalizado"
                                    hint="PNG, JPG ou WebP até 2 MB — redimensionado automaticamente"
                                />
                            </label>
                            @if($errors->first('novoLogo'))
                                <div style="margin-top:4px;font-size:var(--text-xs);color:var(--red-600)">{{ $errors->first('novoLogo') }}</div>
                            @endif
                        </div>
                        <x-ui.textarea label="Breve descrição" rows="2" wire:model="novaDescricao" placeholder="Uma linha que explica o que o cliente recebe.">{{ $novaDescricao }}</x-ui.textarea>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                    <x-ui.button variant="primary" icon="plus" wire:click="guardarBiblioteca">Adicionar serviço</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card title="Biblioteca de Serviços" subtitle="Preenche automaticamente nome, logo e categoria numa nova assinatura" :padding="0">
                <div style="padding:12px 16px 0">
                    <x-ui.tabs
                        :active="$bibliotecaFiltro"
                        :tabs="[
                            ['id' => 'activos', 'label' => 'Activos', 'count' => $this->bibliotecaActivosCount],
                            ['id' => 'arquivados', 'label' => 'Arquivados', 'count' => $this->bibliotecaArquivadosCount],
                        ]"
                        wireMethod="setBibliotecaFiltro"
                    />
                </div>

                @if($itens->isEmpty())
                    <x-ui.empty-state
                        :icon="$bibliotecaFiltro === 'arquivados' ? 'archive' : 'library-big'"
                        :title="$bibliotecaFiltro === 'arquivados' ? 'Sem serviços arquivados' : 'Biblioteca vazia'"
                        :description="$bibliotecaFiltro === 'arquivados' ? 'Serviços arquivados aparecem aqui — nunca são apagados.' : 'Ainda não há nenhum serviço guardado.'"
                    />
                @else
                    <x-ui.data-table
                        dense
                        :columns="[
                            ['key' => 'nome', 'header' => 'Serviço'],
                            ['key' => 'categoria', 'header' => 'Categoria'],
                            ['key' => 'descricao', 'header' => 'Descrição padrão'],
                            ['key' => 'acoes', 'header' => '', 'align' => 'right'],
                        ]"
                    >
                        @foreach($itens as $item)
                            <tr>
                                <td>
                                    <span style="display:flex;align-items:center;gap:9px">
                                        <x-ui.service-logo :name="$item->nome" :category="$item->categoria" :src="$item->logo_path ? Storage::disk('public')->url($item->logo_path) : null" size="26" />
                                        <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ $item->nome }}</span>
                                    </span>
                                </td>
                                <td><x-ui.tag>{{ $item->categoria->label() }}</x-ui.tag></td>
                                <td><span style="font-size:var(--text-xs);color:var(--text-muted)">{{ $item->descricao_padrao ?: '—' }}</span></td>
                                <td class="u-text-right">
                                    <span style="display:inline-flex;gap:2px">
                                        <x-ui.icon-button icon="pencil" label="Editar" size="sm" wire:click="editarBiblioteca({{ $item->id }})" />
                                        <x-ui.icon-button
                                            :icon="$item->arquivado ? 'refresh-cw' : 'archive'"
                                            :label="$item->arquivado ? 'Reactivar' : 'Arquivar'"
                                            size="sm"
                                            wire:click="alternarArquivado({{ $item->id }})"
                                        />
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.data-table>
                @endif
            </x-ui.card>

            {{-- Editar abre em modal, não reaproveitando o formulário do topo — que
                 ficava fora de vista quando se editava um item mais abaixo na lista. --}}
            @if($editandoId)
                <x-ui.dialog title="Editar serviço da biblioteca" width="620" wire:click="cancelarEdicaoBiblioteca">
                    <x-slot:close>
                        <x-ui.icon-button icon="x" label="Fechar" wire:click="cancelarEdicaoBiblioteca" />
                    </x-slot:close>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start">
                        <div style="display:flex;flex-direction:column;gap:12px">
                            <x-ui.input label="Nome do serviço" wire:model="novoNome" placeholder="Ex.: Notion" :error="$errors->first('novoNome')" />
                            <x-ui.select label="Categoria" wire:model="novaCategoria" :options="$this->categoriaOptions" />
                        </div>
                        <div style="display:flex;flex-direction:column;gap:12px">
                            <div>
                                <label style="cursor:pointer;display:inline-block">
                                    <input type="file" wire:model="novoLogo" accept="image/*" style="display:none" />
                                    <x-ui.logo-slot
                                        :size="64"
                                        :src="$novoLogo ? $novoLogo->temporaryUrl() : ($editandoItem?->logo_path ? Storage::disk('public')->url($editandoItem->logo_path) : null)"
                                        label="Alterar logo"
                                        hint="PNG, JPG ou WebP até 2 MB"
                                    />
                                </label>
                                @if($errors->first('novoLogo'))
                                    <div style="margin-top:4px;font-size:var(--text-xs);color:var(--red-600)">{{ $errors->first('novoLogo') }}</div>
                                @endif
                            </div>
                            <x-ui.textarea label="Breve descrição" rows="2" wire:model="novaDescricao" placeholder="Uma linha que explica o que o cliente recebe.">{{ $novaDescricao }}</x-ui.textarea>
                        </div>
                    </div>
                    <x-slot:footer>
                        <x-ui.button wire:click="cancelarEdicaoBiblioteca">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" icon="check" wire:click="guardarBiblioteca">Guardar alterações</x-ui.button>
                    </x-slot:footer>
                </x-ui.dialog>
            @endif

            <x-ui.card
                :title="$editandoMetodoId ? 'Editar método de pagamento' : 'Adicionar método de pagamento'"
                subtitle="Aparecem na renovação e no registo de pagamento em toda a app"
            >
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start">
                    <x-ui.input
                        label="Nome do método"
                        wire:model="novoMetodoNome"
                        placeholder="Ex.: PayPal"
                        :error="$errors->first('novoMetodoNome')"
                        :disabled="$this->editandoMetodoEhConhecido"
                        :hint="$this->editandoMetodoEhConhecido ? 'Um método padrão do sistema não pode ser renomeado.' : null"
                    />
                    <div>
                        <label style="cursor:pointer;display:inline-block">
                            <input type="file" wire:model="novoMetodoLogo" accept="image/*" style="display:none" {{ $this->editandoMetodoEhConhecido ? 'disabled' : '' }} />
                            <x-ui.logo-slot
                                :size="56"
                                :src="$novoMetodoLogo ? $novoMetodoLogo->temporaryUrl() : ($editandoMetodoItem?->logo_path ? Storage::disk('public')->url($editandoMetodoItem->logo_path) : null)"
                                label="Logo personalizado (opcional)"
                                hint="PNG, JPG ou WebP até 2 MB — só para métodos personalizados"
                            />
                        </label>
                        @if($errors->first('novoMetodoLogo'))
                            <div style="margin-top:4px;font-size:var(--text-xs);color:var(--red-600)">{{ $errors->first('novoMetodoLogo') }}</div>
                        @endif
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                    @if($editandoMetodoId)
                        <x-ui.button wire:click="cancelarEdicaoMetodo">Cancelar</x-ui.button>
                    @endif
                    <x-ui.button variant="primary" icon="plus" wire:click="guardarMetodo">{{ $editandoMetodoId ? 'Guardar alterações' : 'Adicionar método' }}</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card title="Métodos de pagamento" subtitle="Arquivar remove um método das opções de escolha, sem apagar o histórico de pagamentos já registados com ele" :padding="0">
                <x-ui.data-table
                    dense
                    :columns="[
                        ['key' => 'nome', 'header' => 'Método'],
                        ['key' => 'tipo', 'header' => 'Tipo'],
                        ['key' => 'acoes', 'header' => '', 'align' => 'right'],
                    ]"
                >
                    @foreach($itensMetodos as $item)
                        @php
                            $conhecido = $this->metodoEhConhecido($item->nome);
                        @endphp
                        <tr style="{{ $item->arquivado ? 'opacity:.55' : '' }}">
                            <td>
                                <span style="display:flex;align-items:center;gap:9px">
                                    <x-ui.payment-method :method="$item->nome" :size="26" :show-label="false" />
                                    <span style="color:var(--text-strong);font-weight:var(--weight-medium)">{{ PaymentMethod::labelFor($item->nome) }}</span>
                                    @if($item->arquivado)<x-ui.tag tone="outline">Arquivado</x-ui.tag>@endif
                                </span>
                            </td>
                            <td><x-ui.tag tone="{{ $conhecido ? 'accent' : 'outline' }}">{{ $conhecido ? 'Padrão do sistema' : 'Personalizado' }}</x-ui.tag></td>
                            <td class="u-text-right">
                                <span style="display:inline-flex;gap:2px">
                                    @unless($conhecido)
                                        <x-ui.icon-button icon="upload" label="Alterar logo" size="sm" wire:click="editarMetodo({{ $item->id }})" />
                                        <x-ui.icon-button icon="pencil" label="Editar" size="sm" wire:click="editarMetodo({{ $item->id }})" />
                                    @endunless
                                    <x-ui.icon-button
                                        :icon="$item->arquivado ? 'refresh-cw' : 'archive'"
                                        :label="$item->arquivado ? 'Reactivar' : 'Arquivar'"
                                        size="sm"
                                        wire:click="alternarArquivadoMetodo({{ $item->id }})"
                                    />
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            </x-ui.card>
        </div>
    @elseif($tab === 'lembretes')
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px;align-items:start">
            <x-ui.card title="Intervalos padrão" subtitle="Valores por omissão — cada serviço pode substituir estes no seu próprio detalhe">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <x-ui.switch label="Verificação diária automática" :checked="(bool) ($lembretes['verificacao_diaria'] ?? false)" wire:click.prevent="alternarVerificacaoDiaria" />
                    @foreach($intervalos as $chave => $label)
                        <x-ui.checkbox :label="$label" :checked="(bool) ($lembretes[$chave] ?? false)" wire:click.prevent="alternarIntervaloGlobal('{{ $chave }}')" />
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Primeiro aviso" subtitle="Quando os avisos começam, conforme o ciclo do serviço. Os intervalos mais próximos continuam a ser enviados.">
                <div style="display:flex;flex-direction:column;gap:12px">
                    @foreach(['mensal' => 'Serviços mensais', 'trimestral' => 'Serviços trimestrais', 'semestral' => 'Serviços semestrais', 'anual' => 'Serviços anuais'] as $ciclo => $rotulo)
                        <div style="display:grid;grid-template-columns:1fr 170px;align-items:center;gap:12px">
                            <span class="field__label" style="font-weight:var(--weight-regular)">{{ $rotulo }}</span>
                            <x-ui.select wire:model.live="antecedencias.{{ $ciclo }}" :options="$this->antecedenciaOpcoes" :aria-label="'Primeiro aviso para '.mb_strtolower($rotulo)" />
                        </div>
                    @endforeach
                    <span class="field__hint">Ex.: com «7 dias antes», um serviço mensal recebe avisos a 7, 3 e 1 dia(s), no dia e depois — e só passa a «a vencer» na última semana. Um serviço com duração personalizada segue o ciclo mais próximo. Num serviço concreto podes sempre ligar outros intervalos no seu próprio ecrã.</span>
                </div>
            </x-ui.card>

            <x-ui.card title="Template — Aviso de renovação">
                <x-slot:action>
                    @if($editandoTemplate === 'renovacao')
                        <x-ui.button size="sm" wire:click="cancelarTemplate">Cancelar</x-ui.button>
                    @else
                        <x-ui.button size="sm" icon="pencil" wire:click="editarTemplate('renovacao')">Editar</x-ui.button>
                    @endif
                </x-slot:action>

                @if($editandoTemplate === 'renovacao')
                    <div style="display:flex;flex-direction:column;gap:10px">
                        <x-ui.input label="Assunto" wire:model="renovAssunto" :error="$errors->first('renovAssunto')" />
                        <x-ui.textarea label="Corpo" rows="8" wire:model="renovCorpo" :error="$errors->first('renovCorpo')">{{ $renovCorpo }}</x-ui.textarea>
                        <span style="font-size:var(--text-xs);color:var(--text-muted)">Marcadores disponíveis: [NOME] [SERVIÇO] [VALOR] [DATA] [NOME DA EMPRESA]</span>
                        <div style="display:flex;justify-content:flex-end">
                            <x-ui.button variant="primary" size="sm" wire:click="guardarTemplateRenovacao">Guardar template</x-ui.button>
                        </div>
                    </div>
                @else
                    <div style="font-size:var(--text-sm);color:var(--text-muted);margin-bottom:8px">Assunto: {{ $renovAssunto }}</div>
                    <div style="font-size:var(--text-sm);color:var(--text-body);line-height:1.65;background:var(--surface-sunken);padding:12px;border-radius:var(--radius-sm)">
                        {!! $this->destacarPlaceholders($renovCorpo) !!}
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Template — Serviço terminado">
                <x-slot:action>
                    @if($editandoTemplate === 'terminado')
                        <x-ui.button size="sm" wire:click="cancelarTemplate">Cancelar</x-ui.button>
                    @else
                        <x-ui.button size="sm" icon="pencil" wire:click="editarTemplate('terminado')">Editar</x-ui.button>
                    @endif
                </x-slot:action>

                @if($editandoTemplate === 'terminado')
                    <div style="display:flex;flex-direction:column;gap:10px">
                        <x-ui.input label="Assunto" wire:model="termAssunto" :error="$errors->first('termAssunto')" />
                        <x-ui.textarea label="Corpo" rows="8" wire:model="termCorpo" :error="$errors->first('termCorpo')">{{ $termCorpo }}</x-ui.textarea>
                        <span style="font-size:var(--text-xs);color:var(--text-muted)">Marcadores disponíveis: [NOME] [SERVIÇO] [VALOR] [DATA] [NOME DA EMPRESA]</span>
                        <div style="display:flex;justify-content:flex-end">
                            <x-ui.button variant="primary" size="sm" wire:click="guardarTemplateTerminado">Guardar template</x-ui.button>
                        </div>
                    </div>
                @else
                    <div style="font-size:var(--text-sm);color:var(--text-muted);margin-bottom:8px">Assunto: {{ $termAssunto }}</div>
                    <div style="font-size:var(--text-sm);color:var(--text-body);line-height:1.65;background:var(--surface-sunken);padding:12px;border-radius:var(--radius-sm)">
                        {!! $this->destacarPlaceholders($termCorpo) !!}
                    </div>
                @endif
            </x-ui.card>
        </div>
    @elseif($tab === 'empresa')
        @php
            $remetenteVencia = Configuracao::remetenteDaPlataforma();
            $nomeEmpresa = $empresaNome ?: 'A tua empresa';
        @endphp
        <div class="grelha-2 grelha-2--colunas">
            <x-ui.card title="Dados da empresa" subtitle="Aparecem nos recibos, nos emails aos clientes e no topo da app.">
                <div class="pilha pilha--12">
                    <x-ui.input label="Nome da empresa" wire:model.live.debounce.400ms="empresaNome" :error="$errors->first('empresaNome')" />
                    <x-ui.input label="Email da empresa" type="email" icon="mail" wire:model.live.debounce.400ms="empresaEmail" :error="$errors->first('empresaEmail')" hint="Mostrado nos recibos. Quando um cliente responde a um email, a resposta chega aqui." />
                    <div class="grelha-campos">
                        <x-ui.select
                            label="Moeda"
                            wire:model="empresaMoeda"
                            :options="[['value' => 'MZN', 'label' => 'MZN — Metical'], ['value' => 'USD', 'label' => 'USD — Dólar'], ['value' => 'ZAR', 'label' => 'ZAR — Rand']]"
                        />
                        <x-ui.select
                            label="Fuso horário"
                            wire:model="empresaFuso"
                            :options="[['value' => 'Africa/Maputo', 'label' => 'Maputo (CAT)'], ['value' => 'UTC', 'label' => 'UTC']]"
                        />
                    </div>
                    <p class="field__hint" style="margin:-4px 0 0">Mudar a moeda muda só o símbolo mostrado: não converte valores já registados.</p>
                    <div>
                        <label style="cursor:pointer;display:inline-block">
                            <input type="file" wire:model="novoLogoEmpresa" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none" />
                            <x-ui.logo-slot
                                :size="56"
                                :src="$novoLogoEmpresa ? $novoLogoEmpresa->temporaryUrl() : (($empresaConfig['logo_path'] ?? null) ? Storage::disk('public')->url($empresaConfig['logo_path']) : null)"
                                label="Logótipo"
                                hint="PNG, JPG ou WebP até 2 MB. Aparece nos emails e recibos."
                            />
                        </label>
                        @if($errors->first('novoLogoEmpresa'))
                            <div class="field__hint field__hint--error" style="margin-top:4px">{{ $errors->first('novoLogoEmpresa') }}</div>
                        @endif
                    </div>
                    <div class="linha-accoes">
                        <x-ui.button variant="primary" size="sm" wire:click="guardarEmpresa">Guardar</x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <div class="pilha">
                <x-ui.card title="Emails aos teus clientes" subtitle="A Vencia envia os lembretes e recibos por ti. Não precisas de configurar nenhum servidor de email.">
                    <div class="previa-email">
                        <span class="previa-email__titulo">Assim o cliente recebe</span>
                        <div><span>De</span> <strong>{{ $nomeEmpresa }}</strong> &lt;{{ $remetenteVencia }}&gt;</div>
                        <div><span>Responder para</span> {{ $empresaEmail ?: 'o email da empresa (preenche ao lado)' }}</div>
                        <div><span>Com</span> o teu logótipo e o nome da empresa no topo</div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Testar emails" subtitle="Recebe um exemplo de cada email com dados fictícios. Nada é gravado e nenhum cliente recebe nada.">
                    <form wire:submit="enviarTeste" class="pilha pilha--12">
                        <x-ui.select
                            label="Tipo de email"
                            wire:model="testeTipo"
                            :options="collect(\App\Services\EmailsDeTeste::TIPOS_CONTA)->map(fn ($t) => ['value' => $t, 'label' => \App\Services\EmailsDeTeste::TIPOS[$t]])->all()"
                        />
                        <x-ui.input label="Enviar para" type="email" wire:model="testeDestino" placeholder="o-teu-email@exemplo.co.mz" :error="$errors->first('testeDestino')" hint="O teu email ou o da empresa." />
                        <div class="linha-accoes">
                            <x-ui.button type="submit" size="sm" icon="send" wire:loading.attr="disabled" wire:target="enviarTeste">
                                <span wire:loading.remove wire:target="enviarTeste">Enviar teste</span>
                                <span wire:loading wire:target="enviarTeste">A enviar…</span>
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
    @endif
</div>
