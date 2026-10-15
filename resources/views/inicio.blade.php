@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');
    // "N meses grátis" on the yearly switch, from the real prices of the first paid plan.
    $pago = $planos->first(fn ($p) => ! $p->gratuito() && (float) $p->preco_mensal > 0);
    $mesesOferta = $pago && (float) $pago->preco_anual > 0 ? (int) round(((float) $pago->preco_mensal * 12 - (float) $pago->preco_anual) / (float) $pago->preco_mensal) : 0;
@endphp
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vencia · Renovações dos clientes e finanças pessoais num só sítio</title>
    <meta name="description" content="Controla os serviços que vendes aos teus clientes, os vencimentos, os pagamentos e os recibos, e as tuas finanças pessoais. Em português, com M-Pesa e e-Mola.">
    <meta property="og:title" content="Vencia — as renovações dos teus clientes, sempre em dia">
    <meta property="og:description" content="Lembretes automáticos, recibos em PDF, finanças da empresa e um painel pessoal privado.">
    <meta property="og:image" content="{{ asset('assets/inicio/painel.png') }}">

    @include('partials.estilos')
    @include('partials.tema')
</head>
<body class="site">
    <a href="#conteudo" class="site__saltar">Saltar para o conteúdo</a>

    <header class="site-topo">
        <div class="site__largura site-topo__barra">
            <a href="{{ route('inicio') }}" class="site-marca"><img src="{{ asset('assets/logo-mark.png') }}" alt="">Vencia</a>
            <nav class="site-topo__nav" aria-label="Secções">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#como-funciona">Como funciona</a>
                <a href="#planos">Planos</a>
                <a href="#perguntas">Perguntas</a>
            </nav>
            <div class="site-topo__accoes">
                <x-ui.theme-toggle />
                <a class="btn btn--ghost btn--md site-topo__entrar" href="{{ route('login') }}">Entrar</a>
                @if($registoAberto)
                    <a class="btn btn--primary btn--md" href="{{ route('registar') }}">Criar conta</a>
                @endif
            </div>
        </div>
    </header>

    <main id="conteudo">
        {{-- Hero --}}
        <section class="heroi">
            <div class="site__largura heroi__texto">
                <span class="selo"><x-ui.icon name="sparkles" size="14" /> Agora com equipas e planos</span>
                <h1 class="heroi__titulo">As renovações dos teus clientes, <span class="destaque">sempre em dia</span>.</h1>
                <p class="heroi__lead">
                    A Vencia lembra os teus clientes antes de cada vencimento, regista os pagamentos com recibo
                    e mostra quanto a empresa ganhou no mês. E dá-te um painel pessoal, só teu, para o dinheiro de casa.
                </p>
                <div class="heroi__accoes">
                    @if($registoAberto)
                        <a class="btn btn--primary btn--lg" href="{{ route('registar') }}">Começar grátis <x-ui.icon name="arrow-right" size="16" /></a>
                    @endif
                    <a class="btn btn--secondary btn--lg" href="#como-funciona">Ver como funciona</a>
                </div>
                <ul class="heroi__garantias">
                    @if($diasTeste > 0)<li><x-ui.icon name="check" size="15" /> {{ $diasTeste }} dias de Pro grátis</li>@endif
                    <li><x-ui.icon name="check" size="15" /> Sem cartão de crédito</li>
                    <li><x-ui.icon name="check" size="15" /> Paga com M-Pesa ou e-Mola</li>
                </ul>
            </div>

            <div class="site__largura">
                <figure class="janela heroi__janela">
                    <div class="janela__barra" aria-hidden="true"><span></span><span></span><span></span><em>vencia · painel</em></div>
                    <img src="{{ asset('assets/inicio/painel.png') }}" alt="Painel da empresa na Vencia: valores a receber, vencimentos próximos e alertas" width="1440" height="900" class="so-claro" fetchpriority="high">
                    <img src="{{ asset('assets/inicio/painel-escuro.png') }}" alt="Painel da empresa na Vencia em tema escuro" width="1440" height="900" class="so-escuro" loading="lazy">
                </figure>
            </div>
        </section>

        {{-- Who it is for --}}
        <section class="faixa">
            <div class="site__largura faixa__conteudo">
                <p>Para quem cobra todos os meses (ou todos os anos)</p>
                <ul>
                    <li><x-ui.icon name="heart-pulse" size="15" /> Ginásios e clubes</li>
                    <li><x-ui.icon name="graduation-cap" size="15" /> Escolas e explicadores</li>
                    <li><x-ui.icon name="house" size="15" /> Senhorios e rendas</li>
                    <li><x-ui.icon name="briefcase" size="15" /> Contabilistas e consultores</li>
                    <li><x-ui.icon name="shield-check" size="15" /> Seguros e vigilância</li>
                    <li><x-ui.icon name="wrench" size="15" /> Limpeza e manutenção</li>
                    <li><x-ui.icon name="globe" size="15" /> Informática e web</li>
                </ul>
            </div>
        </section>

        {{-- Before / after --}}
        <section class="seccao">
            <div class="site__largura">
                <div class="seccao__cabecalho">
                    <span class="seccao__kicker">Antes e depois</span>
                    <h2>Chega de cadernos, Excel e cobranças esquecidas</h2>
                    <p>Quem cobra mensalidades, rendas ou avenças conhece o problema: um pagamento esquecido é dinheiro que não entra.</p>
                </div>

                <div class="antes-depois">
                    <article class="cartao-foto cartao-foto--antes">
                        <div class="cartao-foto__imagem">
                            <img src="{{ asset('assets/inicio/antes-papeis.jpg') }}" alt="Mulher de mãos na cabeça numa secretária cheia de papéis e facturas" loading="lazy" width="1100" height="1650">
                            <span class="cartao-foto__etiqueta">Antes</span>
                        </div>
                        <ul>
                            <li><x-ui.icon name="x" size="16" /> Datas de renovação espalhadas por cadernos e folhas de Excel</li>
                            <li><x-ui.icon name="x" size="16" /> O cliente esquece-se de pagar e tu esqueces-te de cobrar</li>
                            <li><x-ui.icon name="x" size="16" /> Recibos feitos à mão, um a um</li>
                            <li><x-ui.icon name="x" size="16" /> O dinheiro da empresa misturado com o de casa</li>
                        </ul>
                    </article>

                    <article class="cartao-foto cartao-foto--depois">
                        <div class="cartao-foto__imagem">
                            <img src="{{ asset('assets/inicio/com-vencia.jpg') }}" alt="Mulher a sorrir enquanto trabalha no portátil, com o telemóvel na mão" loading="lazy" width="1400" height="933">
                            <span class="cartao-foto__etiqueta">Com a Vencia</span>
                            <div class="notificacao notificacao--a" aria-hidden="true">
                                <span class="notificacao__icone notificacao__icone--ok"><x-ui.icon name="circle-check" size="16" /></span>
                                <div><strong>Pagamento registado</strong><small>2.500 MZN · recibo #000124 enviado</small></div>
                            </div>
                            <div class="notificacao notificacao--b" aria-hidden="true">
                                <span class="notificacao__icone"><x-ui.icon name="bell" size="16" /></span>
                                <div><strong>Lembrete enviado</strong><small>Mensalidade do ginásio vence em 7 dias</small></div>
                            </div>
                        </div>
                        <ul>
                            <li><x-ui.icon name="check" size="16" /> Todos os vencimentos num calendário, com alertas</li>
                            <li><x-ui.icon name="check" size="16" /> O cliente recebe o lembrete por email antes de vencer</li>
                            <li><x-ui.icon name="check" size="16" /> Recibo em PDF enviado num clique</li>
                            <li><x-ui.icon name="check" size="16" /> Empresa e pessoal separados, cada um no seu painel</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="seccao seccao--suave" id="funcionalidades">
            <div class="site__largura">
                <div class="seccao__cabecalho">
                    <span class="seccao__kicker">Funcionalidades</span>
                    <h2>Tudo o que precisas para não perder uma renovação</h2>
                    <p>Deixa de controlar vencimentos em folhas de Excel e mensagens soltas.</p>
                </div>
                <div class="funcoes">
                    @foreach([
                        ['bell', 'Lembretes automáticos', 'Emails ao cliente 30, 15, 7, 3 e 1 dia antes, no dia e depois do vencimento. Tu recebes uma cópia.'],
                        ['receipt', 'Recibos em PDF', 'Cada pagamento gera um recibo numerado com o teu logótipo, enviado ao cliente por email.'],
                        ['calendar-clock', 'Calendário de vencimentos', 'Vê o mês inteiro, o que vence esta semana e o que já está em atraso.'],
                        ['chart-line', 'Finanças da empresa', 'Recebido no mês, a receber, receita recorrente e evolução, com exportação para Excel e PDF.'],
                        ['users', 'Equipa com papéis', 'Convida colegas: o dono gere tudo, o gestor trata do negócio, quem regista só regista.'],
                        ['shield-check', 'Seguro e privado', 'Cada empresa só vê os seus dados. Login em dois passos e cópias de segurança diárias.'],
                    ] as [$icone, $titulo, $texto])
                        <article class="funcao">
                            <span class="funcao__icone"><x-ui.icon :name="$icone" size="20" /></span>
                            <h3>{{ $titulo }}</h3>
                            <p>{{ $texto }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Two panels --}}
        <section class="seccao">
            <div class="site__largura destaques">
                <div class="destaque-linha">
                    <div class="destaque-linha__texto">
                        <span class="seccao__kicker"><x-ui.icon name="briefcase" size="14" /> Painel da empresa</span>
                        <h2>Cada cliente, cada serviço, cada pagamento</h2>
                        <p>Regista o serviço uma vez, com o valor e a periodicidade. A Vencia calcula o próximo vencimento, avisa o cliente e, quando ele paga, renovas com um clique e o recibo segue por email.</p>
                        <ul class="lista-check">
                            <li><x-ui.icon name="check" size="15" /> Histórico completo de cada cliente</li>
                            <li><x-ui.icon name="check" size="15" /> M-Pesa, e-Mola, transferência ou numerário</li>
                            <li><x-ui.icon name="check" size="15" /> Suspender e reactivar com aviso ao cliente</li>
                        </ul>
                    </div>
                    <figure class="janela">
                        <div class="janela__barra" aria-hidden="true"><span></span><span></span><span></span><em>serviço</em></div>
                        <img src="{{ asset('assets/inicio/servico.png') }}" alt="Detalhe de um serviço com vencimento, pagamentos e histórico" loading="lazy">
                    </figure>
                </div>

                <div class="destaque-linha destaque-linha--inversa">
                    <div class="destaque-linha__texto">
                        <span class="seccao__kicker"><x-ui.icon name="wallet" size="14" /> Painel pessoal</span>
                        <h2>E o teu dinheiro, separado do da empresa</h2>
                        <p>Um painel privado, que nem a tua equipa vê: entradas e saídas, fixos mensais, orçamentos por categoria, saldos e metas de poupança. Quando retiras dinheiro da empresa, entra sozinho no teu pessoal.</p>
                        <ul class="lista-check">
                            <li><x-ui.icon name="check" size="15" /> Renda, escola, internet: lançados todos os meses</li>
                            <li><x-ui.icon name="check" size="15" /> Aviso quando passas o orçamento</li>
                            <li><x-ui.icon name="check" size="15" /> Metas de poupança com progresso</li>
                        </ul>
                    </div>
                    <div class="mock-pessoal" aria-hidden="true">
                        <div class="mock-pessoal__topo"><span>Outubro</span><strong>Resumo pessoal</strong></div>
                        <div class="mock-pessoal__numeros">
                            <div><small>Entradas</small><b class="in">48.500</b></div>
                            <div><small>Saídas</small><b class="out">31.240</b></div>
                            <div><small>Guardado</small><b>17.260</b></div>
                        </div>
                        @foreach([['Casa', 12000, 15000], ['Alimentação', 8400, 9000], ['Transporte', 4100, 3500], ['Escola', 6000, 6000]] as [$cat, $gasto, $limite])
                            @php
                                $pct = min(100, round($gasto / $limite * 100));
                            @endphp
                            <div class="mock-pessoal__cat">
                                <div><span>{{ $cat }}</span><span>{{ $fmt($gasto) }} / {{ $fmt($limite) }} MZN</span></div>
                                <div class="uso__barra {{ $gasto > $limite ? 'uso__barra--cheia' : '' }}"><span style="width:{{ $pct }}%"></span></div>
                            </div>
                        @endforeach
                        <div class="mock-pessoal__meta"><x-ui.icon name="piggy-bank" size="16" /> Meta "Férias": 62% · faltam 11.400 MZN</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section class="seccao seccao--suave" id="como-funciona">
            <div class="site__largura">
                <div class="seccao__cabecalho">
                    <span class="seccao__kicker">Como funciona</span>
                    <h2>Em funcionamento em 5 minutos</h2>
                </div>
                <ol class="passos">
                    <li>
                        <span class="passos__n">1</span>
                        <h3>Cria a conta</h3>
                        <p>Nome, email e uma palavra-passe. A tua empresa fica pronta com métodos de pagamento e uma biblioteca de serviços comuns.</p>
                    </li>
                    <li>
                        <span class="passos__n">2</span>
                        <h3>Regista os clientes</h3>
                        <p>Para cada cliente, os serviços que tem contigo, o valor e quando vence. Os estados actualizam-se sozinhos todos os dias.</p>
                    </li>
                    <li>
                        <span class="passos__n">3</span>
                        <h3>Deixa a Vencia lembrar</h3>
                        <p>Os lembretes saem por email nos dias certos. Quando o cliente paga, registas e o recibo segue automaticamente.</p>
                    </li>
                </ol>
            </div>
        </section>

        {{-- Plans --}}
        <section class="seccao" id="planos">
            <div class="site__largura">
                <div class="seccao__cabecalho">
                    <span class="seccao__kicker">Planos</span>
                    <h2>Começa grátis. Passa a Pro quando crescer.</h2>
                    <p>Paga por M-Pesa, e-Mola ou transferência. Se voltares ao Grátis, os teus dados ficam guardados.</p>
                </div>

                <div class="ciclo" role="radiogroup" aria-label="Ciclo de pagamento">
                    <input type="radio" name="ciclo" id="ciclo-mensal" value="mensal" checked>
                    <label for="ciclo-mensal">Mensal</label>
                    <input type="radio" name="ciclo" id="ciclo-anual" value="anual">
                    <label for="ciclo-anual">Anual @if($mesesOferta > 0)<em>{{ $mesesOferta }} {{ $mesesOferta === 1 ? 'mês' : 'meses' }} grátis</em>@endif</label>
                </div>

                <div class="precos">
                    @foreach($planos as $plano)
                        @php
                            $pago = ! $plano->gratuito();
                            $mensal = (float) $plano->preco_mensal;
                            $anual = (float) $plano->preco_anual;
                        @endphp
                        <article class="preco {{ $pago ? 'preco--destaque' : '' }}">
                            @if($pago)<span class="preco__fita">Mais escolhido</span>@endif
                            <h3>{{ $plano->nome }}</h3>
                            @if($plano->descricao)<p class="preco__desc">{{ $plano->descricao }}</p>@endif
                            <div class="preco__valor">
                                @if(! $pago)
                                    <strong>0</strong><span>{{ $moeda }} para sempre</span>
                                @else
                                    <span class="so-mensal"><strong>{{ $fmt($mensal) }}</strong><span>{{ $moeda }} / mês</span></span>
                                    <span class="so-anual"><strong>{{ $fmt($anual > 0 ? $anual : $mensal * 12) }}</strong><span>{{ $moeda }} / ano</span></span>
                                @endif
                            </div>
                            @if($registoAberto)
                                <a class="btn {{ $pago ? 'btn--primary' : 'btn--secondary' }} btn--lg btn--full-width" href="{{ route('registar') }}">
                                    {{ $pago ? ($diasTeste > 0 ? 'Experimentar '.$diasTeste.' dias grátis' : 'Começar') : 'Criar conta grátis' }}
                                </a>
                            @endif
                            <ul class="preco__lista">
                                <li><x-ui.icon name="check" size="16" /> Painel pessoal completo</li>
                                @foreach(\App\Models\Plano::LIMITES as $chave => $nome)
                                    <li><x-ui.icon name="check" size="16" /> {{ $plano->textoLimite($chave) }}</li>
                                @endforeach
                                <li><x-ui.icon name="check" size="16" /> Lembretes por email e recibos em PDF</li>
                                @foreach(\App\Models\Plano::FUNCOES as $chave => $nome)
                                    <li class="{{ $plano->permite($chave) ? '' : 'nao' }}"><x-ui.icon :name="$plano->permite($chave) ? 'check' : 'x'" size="16" /> {{ $nome }}</li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="seccao seccao--suave" id="perguntas">
            <div class="site__largura site__largura--estreita">
                <div class="seccao__cabecalho">
                    <span class="seccao__kicker">Perguntas frequentes</span>
                    <h2>Dúvidas comuns</h2>
                </div>
                <div class="perguntas">
                    @foreach([
                        ['A Vencia cobra os meus clientes automaticamente?', 'Não. A Vencia lembra os teus clientes por email e regista os pagamentos que eles te fazem (M-Pesa, e-Mola, transferência, numerário). O dinheiro vai directamente para ti.'],
                        ['Como pago o plano Pro?', 'Escolhes o plano em Plano, pagas por M-Pesa, e-Mola ou transferência com a referência indicada e envias o código da transacção. Assim que confirmarmos, o Pro fica activo e recebes um email com o recibo.'],
                        ['O que acontece se não renovar o Pro?', 'A conta volta ao plano Grátis. Nada é apagado: só não consegues acrescentar mais clientes ou serviços do que o Grátis permite até voltares ao Pro.'],
                        ['A minha equipa vê as minhas finanças pessoais?', 'Nunca. O painel pessoal é privado de cada pessoa. A equipa só vê os dados da empresa, e cada um de acordo com o seu papel.'],
                        ['Os emails saem com o nome da minha empresa?', 'Sim. Os lembretes e recibos levam o nome e o logótipo da tua empresa, e as respostas dos clientes chegam ao teu email. No Pro podes usar o teu próprio servidor de email.'],
                        ['Posso usar no telemóvel?', 'Sim. A Vencia funciona no browser do telemóvel e pode ser instalada no ecrã principal como uma app.'],
                    ] as [$pergunta, $resposta])
                        <details class="pergunta">
                            <summary>{{ $pergunta }}<x-ui.icon name="chevron-down" size="18" /></summary>
                            <p>{{ $resposta }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Final call to action --}}
        @if($registoAberto)
            <section class="site__largura">
                <div class="apelo">
                    <div>
                        <h2>Não deixes escapar mais nenhuma renovação.</h2>
                        <p>Cria a conta agora e regista o primeiro cliente em menos de 5 minutos.</p>
                    </div>
                    <div class="apelo__accoes">
                        <a class="btn btn--lg apelo__principal" href="{{ route('registar') }}">Criar conta grátis</a>
                        <a class="btn btn--lg apelo__secundario" href="{{ route('login') }}">Já tenho conta</a>
                    </div>
                </div>
            </section>
        @endif
    </main>

    <footer class="site-rodape">
        <div class="site__largura site-rodape__grelha">
            <div class="site-rodape__marca">
                <a href="{{ route('inicio') }}" class="site-marca"><img src="{{ asset('assets/logo-mark.png') }}" alt="">Vencia</a>
                <p>Renovações dos clientes, pagamentos com recibo e finanças pessoais, num só sítio.</p>
            </div>
            <nav aria-label="Produto">
                <h4>Produto</h4>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#como-funciona">Como funciona</a>
                <a href="#planos">Planos e preços</a>
                <a href="#perguntas">Perguntas frequentes</a>
            </nav>
            <nav aria-label="Conta">
                <h4>Conta</h4>
                <a href="{{ route('login') }}">Entrar</a>
                @if($registoAberto)<a href="{{ route('registar') }}">Criar conta</a>@endif
                <a href="{{ route('password.request') }}">Recuperar palavra-passe</a>
            </nav>
            <div>
                <h4>Contacto</h4>
                @if($contacto)
                    <a href="mailto:{{ $contacto }}">{{ $contacto }}</a>
                @endif
                <span>Maputo, Moçambique</span>
            </div>
        </div>
        <div class="site__largura site-rodape__base">
            <span>© {{ now()->year }} Vencia. Todos os direitos reservados.</span>
            <span>Um produto <strong>HTech Group</strong></span>
        </div>
    </footer>
</body>
</html>
