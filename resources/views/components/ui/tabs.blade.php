@props([
    'tabs' => [],
    'active' => null,
    'wireMethod' => 'setTab',
])
<div role="tablist" {{ $attributes->class(['tabs']) }}>
    @foreach($tabs as $t)
        @php
            $id = is_string($t) ? $t : $t['id'];
            $label = is_string($t) ? $t : $t['label'];
            $count = is_string($t) ? null : ($t['count'] ?? null);
            $selected = $id === $active;
        @endphp
        <button
            type="button"
            role="tab"
            aria-selected="{{ $selected ? 'true' : 'false' }}"
            wire:click="{{ $wireMethod }}('{{ $id }}')"
            class="tabs__tab"
        >
            {{ $label }}
            @if($count !== null)<span class="tabs__count">{{ $count }}</span>@endif
        </button>
    @endforeach
</div>
