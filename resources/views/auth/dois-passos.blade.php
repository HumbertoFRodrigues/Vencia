<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificação em dois passos · Vencia</title>

    @include('partials.estilos')

    <script>
        (function () {
            try {
                var saved = window.localStorage.getItem('sm-theme');
                var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card__brand">
                <img src="{{ asset('assets/logo-mark.png') }}" alt="" class="auth-card__logo">
                <span class="auth-card__brand-name">Vencia</span>
            </div>

            <div>
                <h1 class="auth-card__title">Verificação em dois passos</h1>
                <p class="auth-card__subtitle">Abre a app autenticadora no telemóvel e escreve o código de 6 dígitos da Vencia.</p>
            </div>

            @if ($errors->any())
                <div class="auth-card__error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.dois-passos.store') }}" class="auth-card__form">
                @csrf
                <x-ui.input name="codigo" label="Código" inputmode="numeric" autocomplete="one-time-code" placeholder="123 456" required autofocus />
                <x-ui.button type="submit" variant="primary" :full-width="true">Confirmar</x-ui.button>
            </form>

            <p class="auth-card__footnote">
                Sem o telemóvel? Usa um dos códigos de recuperação que guardaste. · <a href="{{ route('login') }}">Voltar</a>
            </p>
        </div>
    </div>
</body>
</html>
