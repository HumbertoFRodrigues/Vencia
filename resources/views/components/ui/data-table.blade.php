@props([
    'columns' => [],
    'dense' => false,
    'empty' => false,
])
<div class="data-table-wrap">
    <table {{ $attributes->class(['data-table', $dense ? 'data-table--dense' : null]) }}>
        <thead>
            <tr>
                @foreach($columns as $c)
                    @php
                        $align = $c['align'] ?? 'left';
                        $alignClass = $align === 'right' ? 'u-text-right' : ($align === 'center' ? 'u-text-center' : '');
                    @endphp
                    <th class="{{ $alignClass }}">
                        <span class="data-table__th-label">
                            {{ $c['header'] }}
                            @if(!empty($c['sorted']))
                                <x-ui.icon :name="$c['sorted'] === 'desc' ? 'arrow-down' : 'arrow-up'" size="11" />
                            @endif
                        </span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if($empty)
                <tr><td colspan="{{ max(count($columns), 1) }}" style="padding:0">{{ $emptyState ?? '' }}</td></tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
