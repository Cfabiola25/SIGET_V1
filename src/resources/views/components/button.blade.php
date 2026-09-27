@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])

@php
    $styles = match ($variant) {
        'secondary' => 'border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700',
        'coral' => 'bg-emerald-500 text-slate-950 shadow-sm hover:bg-emerald-400 hover:shadow-emerald-500/25',
        'ghost' => 'text-slate-400 hover:bg-slate-900 hover:text-slate-200',
        default => 'bg-emerald-500 text-slate-950 shadow-sm hover:bg-emerald-400 hover:shadow-emerald-500/25',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold transition active:scale-95 {$styles}"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold transition active:scale-95 {$styles}"]) }}>{{ $slot }}</button>
@endif
