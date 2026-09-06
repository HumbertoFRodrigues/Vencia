{{-- Ported from components/core/ThemeToggle.jsx. No Alpine/Livewire — a
     plain inline <script> in the layout toggles data-theme on <html> and
     persists to localStorage['sm-theme']; which icon shows is pure CSS
     keyed off [data-theme] (see .theme-toggle rules in components.css). --}}
<button type="button" class="theme-toggle" data-theme-toggle aria-label="Alternar tema" title="Alternar tema">
    <x-ui.icon name="moon" size="17" class="theme-toggle__icon-dark" />
    <x-ui.icon name="sun" size="17" class="theme-toggle__icon-light" />
</button>
