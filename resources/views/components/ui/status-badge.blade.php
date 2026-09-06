<span {{ $attributes->class(['status-badge', "status-badge--{$status}", $size === 'sm' ? 'status-badge--sm' : null]) }}>
    <span class="status-badge__dot"></span>{{ $resolvedLabel }}
</span>
