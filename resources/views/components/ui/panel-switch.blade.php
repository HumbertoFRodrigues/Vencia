@props(['active' => 'empresa'])
<div class="panel-switch" role="tablist" aria-label="Escolher painel">
    <a href="{{ route('dashboard') }}" wire:navigate role="tab" aria-selected="{{ $active === 'empresa' ? 'true' : 'false' }}"
       class="panel-switch__item {{ $active === 'empresa' ? 'panel-switch__item--active' : '' }}" title="Painel da empresa">
        <x-ui.icon name="briefcase" size="14" />
        <span class="panel-switch__label">Empresa</span>
    </a>
    <a href="{{ route('pessoal.resumo') }}" wire:navigate role="tab" aria-selected="{{ $active === 'pessoal' ? 'true' : 'false' }}"
       class="panel-switch__item {{ $active === 'pessoal' ? 'panel-switch__item--active' : '' }}" title="Painel pessoal">
        <x-ui.icon name="wallet" size="14" />
        <span class="panel-switch__label">Pessoal</span>
    </a>
</div>
