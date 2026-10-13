@props([
    'label' => '',
    'atual' => true,
    'anterior' => 'mudarMes(-1)',
    'seguinte' => 'mudarMes(1)',
    'hoje' => 'irParaHoje',
    'hojeLabel' => 'Este mês',
])
<div {{ $attributes->class(['month-nav']) }}>
    <button type="button" class="month-nav__arrow" wire:click="{{ $anterior }}" aria-label="Anterior" title="Anterior">
        <x-ui.icon name="chevron-right" size="15" style="transform:rotate(180deg)" />
    </button>
    <span class="month-nav__label">{{ $label }}</span>
    <button type="button" class="month-nav__arrow" wire:click="{{ $seguinte }}" aria-label="Seguinte" title="Seguinte">
        <x-ui.icon name="chevron-right" size="15" />
    </button>
    @unless($atual)
        <button type="button" class="month-nav__today" wire:click="{{ $hoje }}">{{ $hojeLabel }}</button>
    @endunless
</div>
