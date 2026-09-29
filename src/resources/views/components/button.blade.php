@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])

@php
    $styles = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-xs',
        'coral', 'primary' => 'bg-[#057a55] text-white shadow-xs hover:bg-[#046c4b] active:bg-[#03543a]',
        'danger' => 'bg-rose-600 text-white shadow-xs hover:bg-rose-700 active:bg-rose-800',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
        default => 'bg-[#057a55] text-white shadow-xs hover:bg-[#046c4b] active:bg-[#03543a]',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition active:scale-[0.98] {$styles}"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition active:scale-[0.98] cursor-pointer {$styles}"]) }}>{{ $slot }}</button>
@endif

