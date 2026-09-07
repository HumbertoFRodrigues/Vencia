@props(['label' => 'Exportar'])
@php
    // Unique per render so several export menus (or any other dropdown) can
    // sit on the same page without their toggle/panel pairs cross-matching.
    $grupo = 'export-'.Illuminate\Support\Str::random(8);
@endphp
<div class="dropdown">
    <x-ui.button icon="download" icon-end="chevron-down" data-dropdown-toggle data-dropdown-group="{{ $grupo }}" aria-haspopup="true" aria-expanded="false">
        {{ $label }}
    </x-ui.button>
    <div class="dropdown-panel" data-dropdown-panel data-dropdown-group="{{ $grupo }}" hidden>
        <button type="button" class="dropdown-menu-item" wire:click="exportarCsv">
            <x-ui.icon name="file-spreadsheet" size="16" />
            Exportar CSV
        </button>
        <button type="button" class="dropdown-menu-item" wire:click="exportarPdf">
            <x-ui.icon name="file-text" size="16" />
            Exportar PDF
        </button>
    </div>
</div>
