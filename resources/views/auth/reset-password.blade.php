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
                <h1 class="auth-card__title">Repor password</h1>
                <p class="auth-card__subtitle">Defina uma nova password para a sua conta.</p>
            </div>

            @if ($errors->any())
                <div class="auth-card__error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="auth-card__form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-ui.input type="email" name="email" label="Email" value="{{ old('email', $email) }}" required autofocus />
                <x-ui.input type="password" name="password" label="Nova password" required />
                <x-ui.input type="password" name="password_confirmation" label="Confirmar nova password" required />
                <x-ui.button type="submit" variant="primary" :full-width="true">Repor password</x-ui.button>
            </form>
        </div>
    </div>
</body>
</html>
