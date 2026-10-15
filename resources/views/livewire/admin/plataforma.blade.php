<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Plataforma" />

    <div class="grelha-2">
        <x-ui.card title="Servidor de email (SMTP)" subtitle="Envia os emails da plataforma (confirmação de conta, password, convites, faturas) e os das empresas que não têm servidor próprio.">
            <form wire:submit="guardarSmtp" class="pilha pilha--12">
                <x-ui.input label="Servidor" wire:model="smtpServidor" placeholder="mail.vencia.co.mz" :error="$errors->first('smtpServidor')" />
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <x-ui.input label="Porta" wire:model="smtpPorta" placeholder="587" :error="$errors->first('smtpPorta')" />
                    <x-ui.select label="Segurança" wire:model="smtpSeguranca" :options="[['value' => 'STARTTLS', 'label' => 'STARTTLS'], ['value' => 'SSL', 'label' => 'SSL'], ['value' => 'Nenhuma', 'label' => 'Nenhuma']]" />
                </div>
                <x-ui.input label="Utilizador" wire:model="smtpUtilizador" placeholder="nao-responder@vencia.co.mz" />
                <x-ui.input label="Password" type="password" wire:model="smtpPassword" placeholder="Deixe em branco para manter a actual" />
                <x-ui.input label="Endereço de envio (From)" wire:model="smtpRemetente" placeholder="Em branco = o utilizador acima" :error="$errors->first('smtpRemetente')" hint="Os emails das empresas saem deste endereço com o nome da empresa, e as respostas vão para o email da empresa." />
                <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button></div>
            </form>
        </x-ui.card>

        <div class="pilha">
            <x-ui.card title="Dados de pagamento dos planos" subtitle="Aparecem ao cliente quando pede um plano, e no recibo.">
                <form wire:submit="guardarFaturacao" class="pilha pilha--12">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <x-ui.input label="M-Pesa (número)" wire:model="mpesa" placeholder="84 000 0000" />
                        <x-ui.input label="e-Mola (número)" wire:model="emola" placeholder="86 000 0000" />
                    </div>
                    <x-ui.input label="Transferência bancária" wire:model="banco" placeholder="Banco, NIB/IBAN" />
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <x-ui.input label="Titular" wire:model="titular" />
                        <x-ui.input label="NUIT" wire:model="nif" />
                    </div>
                    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px">
                        <x-ui.input label="Email de faturação" wire:model="emailFaturacao" :error="$errors->first('emailFaturacao')" />
                        <x-ui.input label="Moeda" wire:model="moeda" :error="$errors->first('moeda')" />
                    </div>
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button></div>
                </form>
            </x-ui.card>

            <x-ui.card title="Registo de novas contas">
                <form wire:submit="guardarRegisto" class="pilha pilha--12">
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm)"><input type="checkbox" wire:model="registoAberto"> Qualquer pessoa pode criar conta (fechado: só por convite)</label>
                    <x-ui.input label="Dias de teste do plano Pro para contas novas" wire:model="diasTeste" inputmode="numeric" hint="0 = começam logo no Grátis." :error="$errors->first('diasTeste')" />
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>
    </div>

    <div class="grelha-2">
        <x-ui.card title="Testar emails da plataforma" subtitle="Usa o SMTP guardado acima.">
            <form wire:submit="enviarTeste" class="pilha pilha--12">
                <x-ui.select label="Tipo de email" wire:model="testeTipo" :options="collect(\App\Services\EmailsDeTeste::TIPOS_PLATAFORMA)->map(fn ($t) => ['value' => $t, 'label' => \App\Services\EmailsDeTeste::TIPOS[$t]])->all()" />
                <x-ui.input label="Enviar para" type="email" wire:model="testeDestino" :error="$errors->first('testeDestino')" />
                <div class="linha-accoes">
                    <x-ui.button type="submit" icon="send" size="sm" wire:loading.attr="disabled" wire:target="enviarTeste">Enviar teste</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Cópias de segurança" subtitle="Toda a base de dados (todas as contas) num ficheiro. Criadas todos os dias pela tarefa agendada e enviadas ao super-admin; o servidor guarda as últimas 14.">
            <x-slot:action>
                <x-ui.button size="sm" icon="download" wire:click="criarCopia">Criar e descarregar</x-ui.button>
            </x-slot:action>
            @php($copias = $this->copias)
            @if(empty($copias))
                <p class="pessoal-muted">Ainda não há cópias. Cria uma agora ou agenda <code>php artisan vencia:copia-seguranca</code> (ver DEPLOY.md).</p>
            @else
                <ul class="copias-list">
                    @foreach($copias as $c)
                        <li wire:key="copia-{{ $c['nome'] }}">
                            <span class="num">{{ $c['data']->format('d/m/Y H:i') }}</span>
                            <span class="copias-list__tamanho">{{ number_format($c['bytes'] / 1024, 0, ',', '.') }} KB</span>
                            <x-ui.icon-button size="sm" icon="download" label="Descarregar esta cópia" wire:click="descarregarCopia({{ \Illuminate\Support\Js::from($c['nome']) }})" />
                        </li>
                    @endforeach
                </ul>
                <p class="field__hint" style="margin-top:10px">Para restaurar: phpMyAdmin → base de dados vazia → Importar o ficheiro.</p>
            @endif
        </x-ui.card>
    </div>
</div>
