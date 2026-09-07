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
    <x-ui.page-header eyebrow="Administração" title="Configurações" />

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
                                    hint="PNG, JPG, SVG ou WebP — redimensionado automaticamente"
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
                                        hint="PNG, JPG, SVG ou WebP"
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
                                hint="PNG, JPG, SVG ou WebP — só para métodos personalizados"
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
                        @php($conhecido = $this->metodoEhConhecido($item->nome))
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
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px;align-items:start">
            <x-ui.card title="Empresa">
                <div style="display:flex;flex-direction:column;gap:12px">
                    <x-ui.input label="Nome da empresa" wire:model="empresaNome" :error="$errors->first('empresaNome')" />
                    <x-ui.input label="Email remetente" type="email" icon="mail" wire:model="empresaEmail" :error="$errors->first('empresaEmail')" />
                    <x-ui.select
                        label="Moeda padrão"
                        wire:model="empresaMoeda"
                        :options="[['value' => 'MZN', 'label' => 'MZN — Metical'], ['value' => 'USD', 'label' => 'USD — Dólar americano'], ['value' => 'ZAR', 'label' => 'ZAR — Rand sul-africano']]"
                        hint="Muda só o símbolo mostrado nos valores — não converte nenhum valor já registado."
                    />
                    <x-ui.select
                        label="Fuso horário"
                        wire:model="empresaFuso"
                        :options="[['value' => 'Africa/Maputo', 'label' => 'Africa/Maputo (CAT)'], ['value' => 'UTC', 'label' => 'UTC']]"
                    />
                    <div>
                        <label style="cursor:pointer;display:inline-block">
                            <input type="file" wire:model="novoLogoEmpresa" accept="image/*" style="display:none" />
                            <x-ui.logo-slot
                                :size="56"
                                :src="$novoLogoEmpresa ? $novoLogoEmpresa->temporaryUrl() : (($empresaConfig['logo_path'] ?? null) ? Storage::disk('public')->url($empresaConfig['logo_path']) : null)"
                                label="Logo da empresa"
                                hint="Usado nos emails enviados ao cliente"
                            />
                        </label>
                        @if($errors->first('novoLogoEmpresa'))
                            <div style="margin-top:4px;font-size:var(--text-xs);color:var(--red-600)">{{ $errors->first('novoLogoEmpresa') }}</div>
                        @endif
                    </div>
                    <div style="display:flex;justify-content:flex-end">
                        <x-ui.button variant="primary" size="sm" wire:click="guardarEmpresa">Guardar</x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Envio de email (SMTP)" subtitle="Guardado aqui — sem precisar de editar o .env do servidor">
                <div style="display:flex;flex-direction:column;gap:12px">
                    <x-ui.input label="Servidor" wire:model="smtpServidor" placeholder="smtp.exemplo.co.mz" />
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <x-ui.input label="Porta" wire:model="smtpPorta" placeholder="587" />
                        <x-ui.select
                            label="Segurança"
                            wire:model="smtpSeguranca"
                            :options="[
                                ['value' => 'STARTTLS', 'label' => 'STARTTLS'],
                                ['value' => 'SSL', 'label' => 'SSL'],
                                ['value' => 'Nenhuma', 'label' => 'Nenhuma'],
                            ]"
                        />
                    </div>
                    <x-ui.input label="Utilizador" wire:model="smtpUtilizador" placeholder="cobrancas@exemplo.co.mz" />
                    <x-ui.input label="Password" type="password" wire:model="smtpPassword" placeholder="Deixe em branco para manter a actual" hint="Nunca é reexibida depois de guardada — só substituída se preencher um valor novo." />
                    <div style="display:flex;justify-content:space-between;gap:8px">
                        <x-ui.button size="sm" wire:click="guardarSmtp">Guardar</x-ui.button>
                        <x-ui.button variant="secondary" icon="send" wire:click="testarEmail">Enviar email de teste</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </div>
    @endif
</div>
