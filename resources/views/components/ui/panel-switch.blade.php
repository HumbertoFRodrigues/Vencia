@props(['active' => 'empresa'])
@php
    $admin = (bool) auth()->user()?->super_admin;
    $conta = \App\Support\ContaActual::get();
    $nContas = auth()->user()?->contas()->count() ?? 0;
@endphp
<div class="panel-switch {{ $admin ? 'panel-switch--3' : '' }}" role="tablist" aria-label="Escolher painel">
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
    @if($admin)
        <a href="{{ route('admin.visao') }}" wire:navigate role="tab" aria-selected="{{ $active === 'admin' ? 'true' : 'false' }}"
           class="panel-switch__item {{ $active === 'admin' ? 'panel-switch__item--active' : '' }}" title="Administração da plataforma">
            <x-ui.icon name="shield-check" size="14" />
            <span class="panel-switch__label">Admin</span>
        </a>
    @endif
</div>
@if($active === 'empresa' && $conta)
    <div class="conta-switch">
        <span class="conta-switch__label">Conta</span>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
            <span class="conta-switch__nome" title="{{ $conta->nome }}">{{ $conta->nome }}</span>
            @if($nContas > 1)
                <a href="{{ route('conta.escolher') }}" class="link-button" style="font-size:var(--text-xs)">Mudar</a>
            @endif
        </div>
    </div>
@endif
