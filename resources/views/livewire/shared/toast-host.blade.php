<div id="toast-host" class="toast-host" aria-live="polite"></div>

@script
<script>
    Livewire.on('toast', (payload) => {
        // Livewire hands named-dispatch params as a plain object; defend
        // against the (older/alternate) array-wrapped shape too.
        const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
        const title = data.title ?? '';
        const body = data.body ?? null;
        const tone = data.tone ?? 'success';

        const host = document.getElementById('toast-host');
        if (! host || ! title) return;

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

        const remove = () => {
            el.classList.remove('toast--visible');
            setTimeout(() => el.remove(), 220);
        };

        const timer = setTimeout(remove, 3200);
        closeEl.addEventListener('click', () => { clearTimeout(timer); remove(); });
    });
</script>
@endscript
