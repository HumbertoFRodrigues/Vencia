@props([
    'tone' => 'info',
    'title' => null,
])
@php
    $icons = ['info' => 'info', 'warning' => 'triangle-alert', 'danger' => 'circle-alert', 'success' => 'circle-check'];
    $fgVars = ['info' => 'var(--text-accent)', 'warning' => 'var(--status-due-fg)', 'danger' => 'var(--status-overdue-fg)', 'success' => 'var(--status-active-fg)'];
    $icon = $icons[$tone] ?? $icons['info'];
    $fg = $fgVars[$tone] ?? $fgVars['info'];
@endphp
<div {{ $attributes->class(['alert-banner', "alert-banner--{$tone}"]) }}>
    <x-ui.icon :name="$icon" size="17" :color="$fg" style="margin-top:1px" />
    <div class="alert-banner__body">
        @if($title)<strong class="alert-banner__title">{{ $title }}</strong>@endif
        @if($slot->isNotEmpty())<div class="alert-banner__desc">{{ $slot }}</div>@endif
    </div>
    {{ $action ?? '' }}
</div>
