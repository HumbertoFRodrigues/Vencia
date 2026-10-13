@props([
    'categoria' => null,
    'size' => 'md',
    'label' => false,
])
@php
    [$bg, $fg] = $categoria ? $categoria->tinta() : \App\Models\CategoriaPessoal::CORES['cinza'];
    $icone = $categoria?->icone ?? 'package';
@endphp
@if($label)
    <span {{ $attributes->class(['cat-label']) }}>
        <span class="cat-chip cat-chip--{{ $size }}" style="background:{{ $bg }};color:{{ $fg }}" aria-hidden="true"><x-ui.icon :name="$icone" :size="$size === 'sm' ? 12 : 15" /></span>
        <span class="cat-label__text">{{ $categoria?->nome ?? 'Sem categoria' }}</span>
    </span>
@else
    <span {{ $attributes->class(['cat-chip', 'cat-chip--'.$size]) }} style="background:{{ $bg }};color:{{ $fg }}" title="{{ $categoria?->nome }}" aria-hidden="true">
        <x-ui.icon :name="$icone" :size="$size === 'sm' ? 12 : 15" />
    </span>
@endif
