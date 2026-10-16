<div id="toast-host" class="toast-host" aria-live="polite"></div>

@script
<script>
    // The host is re-mounted on every wire:navigate visit, but Livewire.on()
    // listeners are global: registering again would show every toast once per
    // page visited. Register a single listener for the whole session instead.
    if (! window.__venciaToasts) {
        window.__venciaToasts = { abertos: new Map() };

        Livewire.on('toast', (payload) => {
            // Livewire hands named-dispatch params as a plain object; defend
            // against the (older/alternate) array-wrapped shape too.
            const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
            const title = data.title ?? '';
            const body = data.body ?? null;
            const tone = data.tone ?? 'success';

            const host = document.getElementById('toast-host');
            if (! host || ! title) return;

            // Same message already on screen (e.g. Exportar clicked twice):
            // keep that one a little longer instead of stacking a copy.
            const chave = tone + '|' + title + '|' + (body ?? '');
            const aberto = window.__venciaToasts.abertos.get(chave);
            if (aberto && aberto.el.isConnected) {
                aberto.prolongar();
                return;
            }

            const el = document.createElement('div');
            el.className = 'toast toast--' + tone;
            el.setAttribute('role', 'status');

            const icon = document.createElement('span');
            icon.className = 'toast__icon';

            const body_ = document.createElement('div');
            body_.className = 'toast__body';

            const titleEl = document.createElement('strong');
            titleEl.className = 'toast__title';
            titleEl.textContent = title;
            body_.appendChild(titleEl);

            if (body) {
                const descEl = document.createElement('span');
                descEl.className = 'toast__desc';
                descEl.textContent = body;
                body_.appendChild(descEl);
            }

            const closeEl = document.createElement('span');
            closeEl.className = 'toast__close';
            closeEl.textContent = '×';

            el.appendChild(icon);
            el.appendChild(body_);
            el.appendChild(closeEl);
            host.appendChild(el);

            requestAnimationFrame(() => el.classList.add('toast--visible'));

            let timer = null;
            const remove = () => {
                clearTimeout(timer);
                window.__venciaToasts.abertos.delete(chave);
                el.classList.remove('toast--visible');
                setTimeout(() => el.remove(), 220);
            };
            const prolongar = () => {
                clearTimeout(timer);
                timer = setTimeout(remove, 3200);
            };

            window.__venciaToasts.abertos.set(chave, { el, prolongar });
            prolongar();
            closeEl.addEventListener('click', remove);
        });
    }

    // A toast flashed by a plain redirect (sign-up, switching conta, accepting an invite).
    const inicial = @js(session('toast'));
    if (inicial && inicial.title) {
        Livewire.dispatch('toast', { tone: 'success', ...inicial });
    }
</script>
@endscript
