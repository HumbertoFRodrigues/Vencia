<!DOCTYPE html>
{{-- Standalone on purpose: no database, session or Livewire, so it still
     renders when the error is precisely that one of those is down. --}}
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('titulo') · Vencia</title>
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
    <style>
        .erro { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--surface-app); }
        .erro__card { width: min(440px, 100%); display: flex; flex-direction: column; align-items: flex-start; gap: 14px; padding: 28px; background: var(--surface-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); }
        .erro__marca { display: flex; align-items: center; gap: 8px; font-size: var(--text-sm); font-weight: var(--weight-semibold); color: var(--text-strong); }
        .erro__marca img { width: 24px; height: 24px; }
        .erro__codigo { font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: .08em; color: var(--text-muted); text-transform: uppercase; }
        .erro__titulo { margin: 0; font-size: var(--text-h2, 1.375rem); color: var(--text-strong); text-wrap: balance; }
        .erro__texto { margin: 0; font-size: var(--text-sm); line-height: 1.55; color: var(--text-body); }
        .erro__accoes { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
    </style>
</head>
<body>
    <main class="erro">
        <section class="erro__card" role="alert">
            <span class="erro__marca"><img src="{{ asset('assets/logo-mark.png') }}" alt="">Vencia</span>
            <span class="erro__codigo">Erro @yield('codigo')</span>
            <h1 class="erro__titulo">@yield('titulo')</h1>
            <p class="erro__texto">@yield('texto')</p>
            <div class="erro__accoes">
                @hasSection('accoes')
                    @yield('accoes')
                @else
                    <a class="btn btn--primary btn--md" href="{{ url('/') }}">Voltar ao início</a>
                    <button type="button" class="btn btn--secondary btn--md" onclick="history.back()">Página anterior</button>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
