@props([
    'items' => [],
])
@php
    $kindMap = [
        'criado' => ['plus', 'var(--text-muted)'],
        'pagamento' => ['banknote', 'var(--green-600)'],
        'activado' => ['circle-check', 'var(--green-600)'],
        'lembrete' => ['mail', 'var(--accent)'],
        'vencido' => ['circle-alert', 'var(--red-600)'],
        'suspenso' => ['pause', 'var(--ink-800)'],
        'alterado' => ['pencil', 'var(--text-muted)'],
        'cancelado' => ['circle-slash', 'var(--text-faint)'],
    ];
    $count = count($items);
@endphp
<ol {{ $attributes->class(['timeline']) }}>
    @foreach($items as $i => $it)
        @php
            $kind = is_object($it) ? ($it->kind ?? 'alterado') : ($it['kind'] ?? 'alterado');
            $kind = is_object($kind) && method_exists($kind, 'value') ? $kind->value : $kind;
            [$icon, $color] = $kindMap[$kind] ?? $kindMap['alterado'];
            $last = $i === $count - 1;
            $date = is_object($it) ? ($it->date ?? null) : ($it['date'] ?? null);
            $title = is_object($it) ? ($it->title ?? null) : ($it['title'] ?? null);
            $description = is_object($it) ? ($it->description ?? null) : ($it['description'] ?? null);
        @endphp
        <li class="timeline__item {{ $last ? 'timeline__item--last' : '' }}">
            <div class="timeline__rail">
                <span class="timeline__dot"><x-ui.icon :name="$icon" size="13" :color="$color" /></span>
                @if(!$last)<span class="timeline__line"></span>@endif
            </div>
            <div class="timeline__content">
                <span class="timeline__date">{{ $date }}</span>
                <span class="timeline__title">{{ $title }}</span>
                @if($description)<span class="timeline__desc">{{ $description }}</span>@endif
            </div>
        </li>
    @endforeach
</ol>
