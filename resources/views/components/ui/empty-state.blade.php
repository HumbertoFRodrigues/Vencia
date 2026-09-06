@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
])
<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-state__icon-wrap">
        <x-ui.icon :name="$icon" size="20" color="var(--text-faint)" />
    </span>
    <strong class="empty-state__title">{{ $title }}</strong>
    @if($description)<p class="empty-state__desc">{{ $description }}</p>@endif
    @if(isset($action) && trim($action))
        <div class="empty-state__action">{{ $action }}</div>
    @endif
</div>
