<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Plataforma" />

    <div class="grelha-2 grelha-2--colunas">
        <div class="pilha">
            @php
                $envio = \App\Models\Configuracao::plataforma('smtp', []);
                $envioActivo = \App\Models\Configuracao::temEnvio($envio);
                $remetente = \App\Models\Configuracao::remetenteDaPlataforma();
            @endphp
            <x-ui.card title="Envio de email" subtitle="Um só envio para tudo: confirmações de conta, convites e faturas da plataforma, e os lembretes e recibos que as empresas mandam aos clientes.">
                <x-slot:action>
                    <x-ui.tag :tone="$envioActivo ? 'success' : 'warning'">{{ $envioActivo ? match ($envio['modo'] ?? 'smtp') { 'brevo' => 'Brevo', 'php' => 'mail()', default => 'SMTP' } : 'Por configurar' }}</x-ui.tag>
                </x-slot:action>
                <form wire:submit="guardarSmtp" class="pilha pilha--12">
                    <x-ui.select label="Como enviar" wire:model.live="smtpModo" :options="[
                        ['value' => 'brevo', 'label' => 'Brevo — recomendado (chega à caixa de entrada)'],
                        ['value' => 'php', 'label' => 'Servidor do alojamento — simples (pode ir para o spam)'],
                        ['value' => 'smtp', 'label' => 'Conta de email SMTP — avançado'],
                    ]" />

                    @if($smtpModo === 'brevo')
                        <div class="aviso-caixa">
                            <strong>Como activar (5 minutos, grátis até 300 emails por dia)</strong>
                            <ol>
                                <li>Cria conta em <a href="https://www.brevo.com" target="_blank" rel="noopener">brevo.com</a>.</li>
                                <li>Em <em>Senders, Domains &amp; Dedicated IPs → Domains</em>, adiciona o teu domínio e copia os registos DNS que o Brevo mostra para o cPanel → Zone Editor.</li>
                                <li>Em <em>SMTP &amp; API → API Keys</em>, gera uma chave e cola-a aqui.</li>
                            </ol>
                        </div>
                        <x-ui.input label="Chave API do Brevo" type="password" wire:model="brevoChave" autocomplete="off" :placeholder="filled($envio['brevo_chave'] ?? null) ? 'Guardada (cola outra para substituir)' : 'xkeysib-…'" :error="$errors->first('brevoChave')" />
                    @elseif($smtpModo === 'php')
                        <div class="aviso-caixa aviso-caixa--atencao">
                            Usa o servidor de email do alojamento. Não precisa de configuração, mas a reputação é partilhada com outros sites: Outlook e Hotmail costumam mandar para o spam. Para melhorar, o SPF do domínio deve incluir este servidor (cPanel → Email Deliverability).
                        </div>
                    @else
                        <div class="aviso-caixa aviso-caixa--atencao">Muitos alojamentos bloqueiam ligações SMTP para fora (Gmail, Outlook): se o teste der "Connection refused", usa o Brevo.</div>
                        <x-ui.input label="Servidor" wire:model="smtpServidor" placeholder="mail.exemplo.co.mz" :error="$errors->first('smtpServidor')" />
                        <div class="grelha-campos">
                            <x-ui.input label="Porta" wire:model="smtpPorta" placeholder="465" :error="$errors->first('smtpPorta')" />
                            <x-ui.select label="Segurança" wire:model="smtpSeguranca" :options="[['value' => 'STARTTLS', 'label' => 'STARTTLS'], ['value' => 'SSL', 'label' => 'SSL'], ['value' => 'Nenhuma', 'label' => 'Nenhuma']]" />
                        </div>
                        <div class="grelha-campos">
                            <x-ui.input label="Utilizador" wire:model="smtpUtilizador" placeholder="nao-responder@exemplo.co.mz" />
                            <x-ui.input label="Password" type="password" wire:model="smtpPassword" placeholder="Em branco = manter" />
                        </div>
                    @endif

                    <div class="grelha-campos">
                        <x-ui.input label="Endereço de envio" type="email" wire:model="smtpRemetente" :placeholder="$smtpModo === 'smtp' ? 'Em branco = o utilizador' : \App\Models\Configuracao::remetentePorOmissao()" :error="$errors->first('smtpRemetente')" />
                        <x-ui.input label="Nome nos emails da plataforma" wire:model="remetenteNome" placeholder="Vencia" :error="$errors->first('remetenteNome')" />
                    </div>

                    <div class="previa-email">
                        <span class="previa-email__titulo">Assim chegam os emails</span>
                        <div><span>Da plataforma</span> <strong>{{ $remetenteNome ?: config('app.name') }}</strong> &lt;{{ $remetente }}&gt;</div>
                        <div><span>Das empresas</span> <strong>Nome da empresa</strong> &lt;{{ $remetente }}&gt; · as respostas vão para o email da empresa</div>
                    </div>

                    <div class="linha-accoes">
                        @if($smtpModo === 'brevo')
                            <x-ui.button size="sm" variant="ghost" icon="shield-check" wire:click="verificarBrevo">Verificar chave</x-ui.button>
                        @endif
                        <x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Testar emails da plataforma" subtitle="Usa o envio guardado acima. Podes enviar para qualquer email.">
                <form wire:submit="enviarTeste" class="pilha pilha--12">
                    <x-ui.select label="Tipo de email" wire:model="testeTipo" :options="collect(\App\Services\EmailsDeTeste::TIPOS_PLATAFORMA)->map(fn ($t) => ['value' => $t, 'label' => \App\Services\EmailsDeTeste::TIPOS[$t]])->all()" />
                    <x-ui.input label="Enviar para" type="email" wire:model="testeDestino" :error="$errors->first('testeDestino')" />
                    <div class="linha-accoes">
                        <x-ui.button type="submit" icon="send" size="sm" wire:loading.attr="disabled" wire:target="enviarTeste">Enviar teste</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Cópias de segurança" subtitle="Toda a base de dados (todas as contas) num ficheiro, criado todos os dias pela tarefa agendada. O servidor guarda as últimas 14 e só tu as podes descarregar.">
                <x-slot:action>
                    <x-ui.button size="sm" icon="download" wire:click="criarCopia">Criar e descarregar</x-ui.button>
                </x-slot:action>
                <label class="opcao-linha">
                    <input type="checkbox" wire:model.live="copiaAnexar">
                    <span><strong>Anexar a cópia ao email diário</strong> Fica uma cópia fora do servidor, mas quem tiver acesso à tua caixa de email vê os dados de todas as contas. Desligado, o email só avisa que a cópia foi criada.</span>
                </label>
                @php
                    $copias = $this->copias;
                @endphp
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

            @php
                $mpesa = \App\Services\Pagamentos\MpesaConfig::get();
                $mpesaActivo = \App\Services\Pagamentos\MpesaConfig::activo();
                $temChave = filled($mpesa['api_key']);
            @endphp
            <x-ui.card title="Pagamento automático por M-Pesa" subtitle="Cola aqui as chaves do portal M-Pesa da Vodacom (as mesmas do teu payment-api). Os clientes confirmam com o PIN e o plano activa sozinho; o pagamento manual continua disponível.">
                <x-slot:action>
                    <x-ui.tag :tone="$mpesaActivo ? 'success' : 'neutral'">{{ $mpesaActivo ? 'Ligado'.($mpesa['sandbox'] ? ' (sandbox)' : ' (produção)') : 'Desligado' }}</x-ui.tag>
                </x-slot:action>
                <form wire:submit="guardarMpesa" class="pilha pilha--12">
                    <x-ui.input label="API Key" type="password" wire:model="mpesaApiKey" autocomplete="off" :placeholder="$temChave ? 'Guardada (cola outra para substituir)' : 'Cola a API Key'" :error="$errors->first('mpesaApiKey')" />
                    <x-ui.textarea label="Public Key" wire:model="mpesaPublicKey" rows="3" :placeholder="filled($mpesa['public_key']) ? 'Guardada (cola outra para substituir)' : 'MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA…'" :error="$errors->first('mpesaPublicKey')" />
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <x-ui.input label="Service Provider Code" wire:model="mpesaCodigo" inputmode="numeric" placeholder="171717" :error="$errors->first('mpesaCodigo')" />
                        <x-ui.select label="Ambiente" wire:model="mpesaAmbiente" :options="[['value' => 'sandbox', 'label' => 'Sandbox (testes)'], ['value' => 'producao', 'label' => 'Produção (dinheiro real)']]" />
                    </div>
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm)"><input type="checkbox" wire:model="mpesaLigado"> Ligar "Pagar já com M-Pesa" na página Plano dos clientes</label>
                    @if($mpesa['origem'] === 'env' && filled($mpesa['api_key']))
                        <p class="field__hint" style="margin:0">Neste momento as chaves vêm do <code>.env</code> do servidor. O que guardares aqui passa a valer.</p>
                    @endif
                    <div class="linha-accoes">
                        <x-ui.button size="sm" variant="ghost" icon="shield-check" wire:click="verificarMpesa">Verificar chaves</x-ui.button>
                        <x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            @php
                $anunciosActivos = filled(\App\Support\Anuncios::cliente());
            @endphp
            <x-ui.card title="Anúncios (Google AdSense)" subtitle="Mostra anúncios na página inicial, nos Termos e na Privacidade. Nunca aparecem dentro da app, no login nem no registo.">
                <x-slot:action>
                    <x-ui.tag :tone="$anunciosActivos ? 'success' : 'neutral'">{{ $anunciosActivos ? 'Ligados' : 'Desligados' }}</x-ui.tag>
                </x-slot:action>
                <form wire:submit="guardarAnuncios" class="pilha pilha--12">
                    <x-ui.input label="ID de editor" wire:model="anunciosCliente" autocomplete="off" placeholder="ca-pub-1234567890123456" hint="No AdSense: Conta → Informações da conta. Também podes colar o código <script> inteiro que o AdSense mostra." :error="$errors->first('anunciosCliente')" />
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm)"><input type="checkbox" wire:model="anunciosLigado"> Mostrar anúncios (a Google escolhe onde aparecem)</label>
                    @if($anunciosActivos)
                        <p class="field__hint" style="margin:0">O ficheiro <a href="{{ route('ads-txt') }}" target="_blank" rel="noopener">ads.txt</a> já é criado sozinho.</p>
                    @endif
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button></div>
                </form>
            </x-ui.card>

            <x-ui.card title="Registo de novas contas">
                <form wire:submit="guardarRegisto" class="pilha pilha--12">
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm)"><input type="checkbox" wire:model="registoAberto"> Qualquer pessoa pode criar conta (fechado: só por convite)</label>
                    <label style="display:flex;gap:8px;align-items:center;font-size:var(--text-sm)"><input type="checkbox" wire:model="avisarAdmin"> Avisar-me por email quando alguém cria conta</label>
                    <x-ui.input label="Dias de teste do plano Pro para contas novas" wire:model="diasTeste" inputmode="numeric" hint="0 = começam logo no Grátis." :error="$errors->first('diasTeste')" />
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>
    </div>
</div>
