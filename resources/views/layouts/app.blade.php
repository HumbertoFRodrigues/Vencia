<!DOCTYPE html>
{{-- data-no-progress-bar switches off Livewire's own default top progress
     bar for wire:navigate transitions — the branded #nav-loading pill below
     is the one loading indicator we want, not two competing signals. --}}
<html lang="pt-PT" data-no-progress-bar>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · Vencia' : 'Vencia' }}</title>

    @include('partials.estilos')

    {{-- Viewer preferences (theme, hidden/compact sidebar) live on <html>,
         applied before paint to avoid a flash. wire:navigate copies the new
         page's <html> attributes and REMOVES any it doesn't have, so they are
         re-applied at the moment each navigation swaps the page — otherwise
         dark mode turned back to light on every menu click. --}}
    <script>
        (function () {
            function aplicarPreferencias() {
                var root = document.documentElement;
                try {
                    var saved = window.localStorage.getItem('sm-theme');
                    var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                    root.setAttribute('data-theme', theme);
                    root.toggleAttribute('data-sidebar-collapsed', window.localStorage.getItem('sm-sidebar-collapsed') === '1');
                    root.toggleAttribute('data-sidebar-hidden', window.localStorage.getItem('sm-sidebar-hidden') === '1');
                } catch (e) {}
            }

            aplicarPreferencias();

            if (!window.__venciaPreferencias) {
                window.__venciaPreferencias = true;
                document.addEventListener('livewire:navigating', function (e) {
                    if (e.detail && e.detail.onSwap) e.detail.onSwap(aplicarPreferencias);
                });
                document.addEventListener('livewire:navigated', aplicarPreferencias);
            }
        })();
    </script>

    @livewireStyles
</head>
<body>
    @if(session('admin_original_id'))
        <form method="POST" action="{{ route('suporte.voltar') }}" class="faixa-suporte">
            @csrf
            <span>Estás a ver a Vencia como <strong>{{ auth()->user()->email }}</strong> (suporte).</span>
            <button type="submit" class="btn btn--secondary btn--sm">Voltar ao admin</button>
        </form>
    @endif
    <div class="app-shell">
        <x-ui.sidebar-nav id="menu-principal" :items="$navItems" :active="$navActive">
            <x-slot:switcher><x-ui.panel-switch :active="$painel" /></x-slot:switcher>
            <x-slot:footer>{{ match ($painel) { 'pessoal' => 'Painel privado — só tu vês', 'admin' => 'Administração da plataforma', default => 'Verificação diária às 07:00' } }}</x-slot:footer>
        </x-ui.sidebar-nav>
        <div class="nav-scrim" data-nav-close aria-hidden="true"></div>

        <div class="app-shell__main">
            <x-ui.top-bar :search="$painel === 'empresa'" :bell="$painel === 'empresa'" />

            <main class="app-shell__content">
                <div class="app-shell__content-inner">
                    @if($avisoPlano ?? null)
                        <div class="faixa-plano faixa-plano--{{ $avisoPlano['tom'] }}" role="status">
                            <x-ui.icon name="hourglass" size="16" />
                            <span>{{ $avisoPlano['texto'] }}</span>
                            <a href="{{ route('conta.plano') }}" wire:navigate class="btn btn--primary btn--sm">Ver planos</a>
                        </div>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts

    {{-- Every script below carries data-navigate-once. wire:navigate re-runs
         body scripts on each visit, which stacked one more copy of every
         document listener per page: a dropdown then toggled several times per
         click (opening and closing again) and each toast appeared N times. --}}

    {{-- Branded wire:navigate loading indicator (replaces Livewire's default
         progress bar, disabled above via data-no-progress-bar). A small pill
         with the pulsing logo mark — not a blocking overlay — shown only
         while a page-to-page navigation request is in flight. --}}
    <div id="nav-loading" class="nav-loading" aria-hidden="true">
        <img src="{{ asset('assets/logo-mark.png') }}" alt="" class="nav-loading__logo" />
        <span class="nav-loading__label">A carregar…</span>
    </div>

    <livewire:shared.toast-host />

    <script data-navigate-once>
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-theme-toggle]');
            if (!btn) return;
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { window.localStorage.setItem('sm-theme', next); } catch (e) {}
        });
    </script>

    {{-- Sidebar collapse toggle: same click-toggle-and-persist pattern as the
         theme toggle. The attribute lives on <html>, not on .sidebar-nav
         itself, precisely so it survives wire:navigate's body morphing. --}}
    <script data-navigate-once>
        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-sidebar-toggle]')) return;
            var collapsed = document.documentElement.hasAttribute('data-sidebar-collapsed');
            if (collapsed) {
                document.documentElement.removeAttribute('data-sidebar-collapsed');
            } else {
                document.documentElement.setAttribute('data-sidebar-collapsed', '');
            }
            try { window.localStorage.setItem('sm-sidebar-collapsed', collapsed ? '0' : '1'); } catch (e) {}
        });
    </script>

    {{-- Installable app (manifest + service worker, see public/sw.js). --}}
    <script data-navigate-once>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () { navigator.serviceWorker.register(@js(asset('sw.js'))).catch(function () {}); });
        }
    </script>

    {{-- Session expired mid-action: Livewire's own prompt is in English. --}}
    <script data-navigate-once>
        document.addEventListener('livewire:init', function () {
            var avisado = false;
            Livewire.interceptRequest(function (pedido) {
                pedido.onError(function (e) {
                    if (!e.response || e.response.status !== 419) return;
                    e.preventDefault();
                    if (avisado) return;
                    avisado = true;
                    if (confirm('A sessão expirou por inactividade.\nRecarregar a página para continuar?')) window.location.reload();
                });
            });
        });
    </script>

    {{-- The ☰ button in the top-left corner. On a computer it hides/shows the
         whole sidebar for a distraction-free page (remembered, like the theme);
         on a phone it opens the sidebar as a drawer that the scrim, Escape or
         any navigation closes. Both states live on <html>, so they survive
         wire:navigate's body morphing. --}}
    <script data-navigate-once>
        (function () {
            var root = document.documentElement;
            var telemovel = window.matchMedia('(max-width: 860px)');

            function sincronizarBotao() {
                var visivel = telemovel.matches ? root.hasAttribute('data-nav-open') : !root.hasAttribute('data-sidebar-hidden');
                var rotulo = visivel ? 'Esconder menu' : 'Mostrar menu';
                document.querySelectorAll('[data-nav-toggle]').forEach(function (b) {
                    b.setAttribute('aria-expanded', visivel ? 'true' : 'false');
                    b.setAttribute('aria-label', rotulo);
                    b.setAttribute('title', rotulo);
                });
            }

            function abrirGaveta(aberta) {
                if (aberta) { root.setAttribute('data-nav-open', ''); } else { root.removeAttribute('data-nav-open'); }
                sincronizarBotao();
            }

            function alternarBarraLateral() {
                var esconder = !root.hasAttribute('data-sidebar-hidden');
                if (esconder) { root.setAttribute('data-sidebar-hidden', ''); } else { root.removeAttribute('data-sidebar-hidden'); }
                try { window.localStorage.setItem('sm-sidebar-hidden', esconder ? '1' : '0'); } catch (e) {}
                sincronizarBotao();
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('[data-nav-toggle]')) {
                    if (telemovel.matches) { abrirGaveta(!root.hasAttribute('data-nav-open')); } else { alternarBarraLateral(); }
                    return;
                }
                if (e.target.closest('[data-nav-close]')) { abrirGaveta(false); }
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && root.hasAttribute('data-nav-open')) { abrirGaveta(false); } });
            document.addEventListener('livewire:navigate', function () { abrirGaveta(false); });
            document.addEventListener('livewire:navigated', sincronizarBotao);
            telemovel.addEventListener('change', sincronizarBotao);
            sincronizarBotao();
        })();
    </script>

    {{-- Sidebar badges are rendered with the page; components dispatch
         'nav-badge' {id, n} after an action so the count never lags behind. --}}
    <script data-navigate-once>
        window.addEventListener('nav-badge', function (e) {
            var item = document.querySelector('.sidebar-nav__item[data-nav-id="' + e.detail.id + '"]');
            if (!item) return;
            var badge = item.querySelector('.sidebar-nav__badge');
            if (e.detail.n > 0) {
                if (!badge) { badge = document.createElement('span'); badge.className = 'sidebar-nav__badge'; item.appendChild(badge); }
                badge.textContent = e.detail.n;
            } else if (badge) {
                badge.remove();
            }
        });
    </script>

    {{-- Generic dropdown mechanism: a data-dropdown-toggle button paired with
         a data-dropdown-panel by a shared data-dropdown-group value, so any
         number of independent dropdowns (today: the profile menu and the
         export menu) can coexist on one page without interfering with each
         other. Same plain click-toggle pattern as the theme toggle above, no
         Alpine/Livewire involved. --}}
    <script data-navigate-once>
        document.addEventListener('click', function (e) {
            var toggle = e.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                var group = toggle.getAttribute('data-dropdown-group');
                var panel = document.querySelector('[data-dropdown-panel][data-dropdown-group="' + group + '"]');
                if (!panel) return;

                var wasHidden = panel.hasAttribute('hidden');

                // Opening one dropdown closes every other open one first.
                document.querySelectorAll('[data-dropdown-panel]:not([hidden])').forEach(function (openPanel) {
                    if (openPanel === panel) return;
                    closeDropdown(openPanel);
                });

                if (wasHidden) {
                    panel.removeAttribute('hidden');
                    toggle.setAttribute('aria-expanded', 'true');
                } else {
                    closeDropdown(panel);
                }
                return;
            }

            // Any other click (an outside click, or a click on a menu item
            // inside an open panel) closes every currently open panel.
            document.querySelectorAll('[data-dropdown-panel]:not([hidden])').forEach(closeDropdown);
        });

        function closeDropdown(panel) {
            panel.setAttribute('hidden', '');
            var group = panel.getAttribute('data-dropdown-group');
            var toggle = document.querySelector('[data-dropdown-toggle][data-dropdown-group="' + group + '"]');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    </script>

    {{-- Branded wire:navigate loading indicator: no Alpine, just the two
         events Livewire fires around every wire:navigate transition. Shown
         after a short delay so a fast local navigation barely flashes it —
         only slower transitions (or the artificially-throttled ones we test
         with) actually reveal the pill. --}}
    <script data-navigate-once>
        (function () {
            // Looked up on each use: the pill is part of the body that every navigation replaces.
            var showTimer = null;

            function show() {
                showTimer = window.setTimeout(function () {
                    var el = document.getElementById('nav-loading');
                    if (el) el.classList.add('nav-loading--visible');
                }, 150);
            }

            function hide() {
                if (showTimer) {
                    window.clearTimeout(showTimer);
                    showTimer = null;
                }
                var el = document.getElementById('nav-loading');
                if (el) el.classList.remove('nav-loading--visible');
            }

            document.addEventListener('livewire:navigate', show);
            document.addEventListener('livewire:navigated', hide);
        })();
    </script>
</body>
</html>
