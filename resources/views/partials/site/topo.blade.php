{{-- Header of the public pages. Needs $registoAberto. --}}
    <header class="site-topo">
        <div class="site__largura site-topo__barra">
            <a href="{{ route('inicio') }}" class="site-marca"><img src="{{ asset('assets/logo-mark.png') }}" alt="">Vencia</a>
            <nav class="site-topo__nav" aria-label="Secções">
                <a href="{{ route('inicio') }}#funcionalidades">Funcionalidades</a>
                <a href="{{ route('inicio') }}#como-funciona">Como funciona</a>
                <a href="{{ route('inicio') }}#planos">Planos</a>
                <a href="{{ route('inicio') }}#perguntas">Perguntas</a>
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
