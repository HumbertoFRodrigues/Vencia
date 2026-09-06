<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · Vencia' : 'Vencia' }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Applied before paint to avoid a flash of the wrong theme. --}}
    <script>
        (function () {
            try {
                var saved = window.localStorage.getItem('sm-theme');
                var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

    @livewireStyles
</head>
<body>
    <div class="app-shell">
        <x-ui.sidebar-nav :items="$navItems" :active="$navActive">
            <x-slot:footer>Verificação diária às 07:00</x-slot:footer>
        </x-ui.sidebar-nav>

        <div class="app-shell__main">
            <x-ui.top-bar :alert-count="$sidebarAVencer + $sidebarAReceber" />

            <main class="app-shell__content">
                <div class="app-shell__content-inner">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts

    <script>
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-theme-toggle]');
            if (!btn) return;
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { window.localStorage.setItem('sm-theme', next); } catch (e) {}
        });
    </script>
</body>
</html>
