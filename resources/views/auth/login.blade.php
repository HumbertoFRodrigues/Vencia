<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sessão · Vencia</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

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
                <h1 class="auth-card__title">Iniciar sessão</h1>
                <p class="auth-card__subtitle">Entre com as suas credenciais de administrador.</p>
            </div>

            @if ($errors->any())
                <div class="auth-card__error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-card__form">
                @csrf
                <x-ui.input type="email" name="email" label="Email" value="{{ old('email') }}" required autofocus />
                <x-ui.input type="password" name="password" label="Palavra-passe" required />
                <x-ui.button type="submit" variant="primary" :full-width="true">Entrar</x-ui.button>
            </form>
        </div>
    </div>
</body>
</html>
