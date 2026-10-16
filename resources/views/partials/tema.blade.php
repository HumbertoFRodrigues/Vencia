{{-- Public pages (home, login, sign-up…): the same light/dark preference as
     the app (localStorage 'sm-theme', else the system setting), applied before
     the first paint, plus the click handler for any [data-theme-toggle]. --}}
<script>
    (function () {
        var root = document.documentElement;
        try {
            var saved = window.localStorage.getItem('sm-theme');
            root.setAttribute('data-theme', saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
        } catch (e) {
            root.setAttribute('data-theme', 'light');
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-theme-toggle]');
            if (!btn) return;
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { window.localStorage.setItem('sm-theme', next); } catch (err) {}
        });
    })();
</script>
