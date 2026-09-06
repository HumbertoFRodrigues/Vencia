@props([
    'paginator',
    /**
     * true (default): this partial supplies its own horizontal edge padding
     * and a tinted background — for use as the last child inside a
     * flush-padding ui.card, right after a ui.data-table or grid, mirroring
     * .dialog-panel__footer's bar-under-content look.
     * false: no horizontal padding, no background — for dropping straight
     * into a card body that already carries its own padding (e.g. below a
     * ui.timeline inside a default ui.card).
     */
    'flush' => true,
])
@if($paginator->total() > 0)
    <div {{ $attributes->class(['pagination', $flush ? 'pagination--flush' : null]) }}>
        <span class="pagination__summary">
            A mostrar {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </span>

        @if($paginator->hasPages())
            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $window = 1;
                $pages = [];
                for ($p = 1; $p <= $last; $p++) {
                    if ($p === 1 || $p === $last || ($p >= $current - $window && $p <= $current + $window)) {
                        $pages[] = $p;
                    }
                }
                $previous = null;
            @endphp
            <div class="pagination__nav">
                <button type="button" class="pagination__btn" wire:click="previousPage" @disabled($paginator->onFirstPage())>Anterior</button>

                @foreach($pages as $p)
                    @if($previous !== null && $p - $previous > 1)
                        <span class="pagination__ellipsis">&hellip;</span>
                    @endif
                    <button
                        type="button"
                        class="pagination__btn pagination__btn--page"
                        aria-current="{{ $p === $current ? 'true' : 'false' }}"
                        wire:click="gotoPage({{ $p }})"
                    >{{ $p }}</button>
                    @php $previous = $p; @endphp
                @endforeach

                <button type="button" class="pagination__btn" wire:click="nextPage" @disabled(!$paginator->hasMorePages())>Seguinte</button>
            </div>
        @endif
    </div>
@endif
