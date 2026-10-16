{{-- Footer of the public pages. Needs $registoAberto and $contacto. --}}
    <footer class="site-rodape">
        <div class="site__largura site-rodape__grelha">
            <div class="site-rodape__marca">
                <a href="{{ route('inicio') }}" class="site-marca"><img src="{{ asset('assets/logo-mark.png') }}" alt="">Vencia</a>
                <p>Renovações dos clientes, pagamentos com recibo e finanças pessoais, num só sítio.</p>
            </div>
            <nav aria-label="Produto">
                <h4>Produto</h4>
                <a href="{{ route('inicio') }}#funcionalidades">Funcionalidades</a>
                <a href="{{ route('inicio') }}#como-funciona">Como funciona</a>
                <a href="{{ route('inicio') }}#planos">Planos e preços</a>
                <a href="{{ route('inicio') }}#perguntas">Perguntas frequentes</a>
            </nav>
            <nav aria-label="Conta">
                <h4>Conta</h4>
                <a href="{{ route('login') }}">Entrar</a>
                @if($registoAberto)<a href="{{ route('registar') }}">Criar conta</a>@endif
                <a href="{{ route('password.request') }}">Recuperar palavra-passe</a>
                <a href="{{ route('termos') }}">Termos de utilização</a>
                <a href="{{ route('privacidade') }}">Privacidade</a>
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
