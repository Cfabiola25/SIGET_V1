@props([
    'name',
    'type' => 'outline',
    'variant' => null,
    'filled' => false,
    'class' => 'size-5',
])

@php
    $effectiveVariant = ($filled || $type === 'filled' || $variant === 'filled' || $variant === 'f') ? 'filled' : 'outline';
    $svgContent = \App\Support\Reicon::get($name, $effectiveVariant);
@endphp

@if($svgContent)
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" {{ $attributes->merge(['class' => 'reicon inline-block ' . $class]) }}>
        {!! $svgContent !!}
    </svg>
@endif
