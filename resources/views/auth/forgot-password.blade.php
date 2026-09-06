<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repor password · Vencia</title>

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
                <h1 class="auth-card__title">Esqueceu-se da password?</h1>
                <p class="auth-card__subtitle">Indique o seu email e enviamos uma ligação para repor a password.</p>
            </div>

            @if (session('status'))
                <x-ui.alert-banner tone="success">{{ session('status') }}</x-ui.alert-banner>
            @endif

            @if ($errors->any())
                <div class="auth-card__error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="auth-card__form">
                @csrf
                <x-ui.input type="email" name="email" label="Email" value="{{ old('email') }}" required autofocus />
                <x-ui.button type="submit" variant="primary" :full-width="true">Enviar ligação de reposição</x-ui.button>
            </form>

            <p class="auth-card__footnote">
                <a href="{{ route('login') }}">Voltar a iniciar sessão</a>
            </p>
        </div>
    </div>
</body>
</html>
