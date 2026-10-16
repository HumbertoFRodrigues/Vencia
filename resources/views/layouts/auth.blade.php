<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('titulo') · Vencia</title>

    @include('partials.estilos')
    @include('partials.tema')
</head>
<body>
    <div class="acesso">
        {{-- Brand side: hidden on small screens, where the form comes first. --}}
        <aside class="acesso__marca" aria-hidden="true">
            <a href="{{ route('inicio') }}" class="acesso__logo acesso__logo--claro" tabindex="-1">
                <img src="{{ asset('assets/logo-mark.png') }}" alt="">
                <span>Vencia</span>
            </a>

            <div class="acesso__mensagem">
                <h2>As cobranças dos teus clientes em dia. As tuas finanças em ordem.</h2>
                <ul>
                    <li><x-ui.icon name="bell" size="16" /> Lembretes por email antes de cada vencimento</li>
                    <li><x-ui.icon name="receipt" size="16" /> Pagamentos com recibo em PDF, enviado ao cliente</li>
                    <li><x-ui.icon name="wallet" size="16" /> Painel pessoal privado para o dinheiro de casa</li>
                    <li><x-ui.icon name="users" size="16" /> A tua equipa, cada um com o seu papel</li>
                </ul>
            </div>

            <div class="acesso__foto">
                <img src="{{ asset('assets/inicio/com-vencia.jpg') }}" alt="">
                <div class="notificacao notificacao--a">
                    <span class="notificacao__icone notificacao__icone--ok"><x-ui.icon name="circle-check" size="16" /></span>
                    <div><strong>Pagamento registado</strong><small>2.500 MZN · recibo enviado ao cliente</small></div>
                </div>
                <div class="notificacao notificacao--b">
                    <span class="notificacao__icone"><x-ui.icon name="bell" size="16" /></span>
                    <div><strong>3 renovações esta semana</strong><small>Os clientes já foram avisados</small></div>
                </div>
            </div>

            <p class="acesso__nota">Feito em Moçambique · M-Pesa · e-Mola · Transferência</p>
        </aside>

        <main class="acesso__painel">
            <header class="acesso__topo">
                <a href="{{ route('inicio') }}" class="acesso__logo">
                    <img src="{{ asset('assets/logo-mark.png') }}" alt="">
                    <span>Vencia</span>
                </a>
                <x-ui.theme-toggle />
            </header>

            <div class="acesso__conteudo">
                <div class="acesso__cabecalho">
                    <h1>@yield('titulo')</h1>
                    @hasSection('subtitulo')
                        <p>@yield('subtitulo')</p>
                    @endif
                </div>

                @if (session('status'))
                    <x-ui.alert-banner tone="success">{{ session('status') }}</x-ui.alert-banner>
                @endif

                @if ($errors->any())
                    <div class="auth-card__error" role="alert">{{ $errors->first() }}</div>
                @endif

                @yield('conteudo')
            </div>

            <footer class="acesso__rodape">
                <span>© {{ now()->year }} Vencia · HTech Group</span>
                <nav aria-label="Ligações">
                    <a href="{{ route('inicio') }}">Página inicial</a>
                    <a href="{{ route('inicio') }}#planos">Planos</a>
                    <a href="{{ route('inicio') }}#perguntas">Ajuda</a>
                </nav>
            </footer>
        </main>
    </div>

    <script>
        // "Mostrar" on every password field: typing a password on a phone is hard enough.
        document.querySelectorAll('.acesso input[type="password"]').forEach(function (input) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mostrar-password';
            btn.textContent = 'Mostrar';
            btn.setAttribute('aria-label', 'Mostrar a palavra-passe');
            btn.addEventListener('click', function () {
                var visivel = input.type === 'text';
                input.type = visivel ? 'password' : 'text';
                btn.textContent = visivel ? 'Mostrar' : 'Esconder';
                btn.setAttribute('aria-label', visivel ? 'Mostrar a palavra-passe' : 'Esconder a palavra-passe');
            });
            input.parentNode.appendChild(btn);
        });
    </script>
</body>
</html>
