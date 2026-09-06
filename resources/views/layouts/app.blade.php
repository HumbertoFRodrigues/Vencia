<!DOCTYPE html>
{{-- data-no-progress-bar switches off Livewire's own default top progress
     bar for wire:navigate transitions — the branded #nav-loading pill below
     is the one loading indicator we want, not two competing signals. --}}
<html lang="pt-PT" data-no-progress-bar>
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

    {{-- Branded wire:navigate loading indicator (replaces Livewire's default
         progress bar, disabled above via data-no-progress-bar). A small pill
         with the pulsing logo mark — not a blocking overlay — shown only
         while a page-to-page navigation request is in flight. --}}
    <div id="nav-loading" class="nav-loading" aria-hidden="true">
        <img src="{{ asset('assets/logo-mark.png') }}" alt="" class="nav-loading__logo" />
        <span class="nav-loading__label">A carregar…</span>
    </div>

    <livewire:shared.toast-host />

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

    {{-- Profile dropdown in the top bar: same plain click-toggle pattern as
         the theme toggle above, no Alpine/Livewire involved. --}}
    <script>
        document.addEventListener('click', function (e) {
            var panel = document.querySelector('[data-profile-panel]');
            var toggle = document.querySelector('[data-profile-toggle]');
            if (!panel || !toggle) return;

            if (e.target.closest('[data-profile-toggle]')) {
                var isHidden = panel.hasAttribute('hidden');
                if (isHidden) {
                    panel.removeAttribute('hidden');
                    toggle.setAttribute('aria-expanded', 'true');
                } else {
                    panel.setAttribute('hidden', '');
                    toggle.setAttribute('aria-expanded', 'false');
                }
                return;
            }

            if (!panel.hasAttribute('hidden') && !e.target.closest('[data-profile-panel]')) {
                panel.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    </script>

    {{-- Branded wire:navigate loading indicator: no Alpine, just the two
         events Livewire fires around every wire:navigate transition. Shown
         after a short delay so a fast local navigation barely flashes it —
         only slower transitions (or the artificially-throttled ones we test
         with) actually reveal the pill. --}}
    <script>
        (function () {
            var el = document.getElementById('nav-loading');
            if (!el) return;

            var showTimer = null;
            var visible = false;

            function show() {
                showTimer = window.setTimeout(function () {
                    el.classList.add('nav-loading--visible');
                    visible = true;
                }, 150);
            }

            function hide() {
                if (showTimer) {
                    window.clearTimeout(showTimer);
                    showTimer = null;
                }
                if (visible) {
                    el.classList.remove('nav-loading--visible');
                    visible = false;
                }
            }

            document.addEventListener('livewire:navigate', show);
            document.addEventListener('livewire:navigated', hide);
        })();
    </script>
</body>
</html>
