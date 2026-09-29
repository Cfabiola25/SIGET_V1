@props(['title' => null, 'eyebrow' => null])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200/90 bg-white p-6 shadow-xs transition-all hover:border-slate-300 text-slate-800']) }}>
    @if ($eyebrow || $title)
        <header class="mb-5">
            @if ($eyebrow)<p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ $eyebrow }}</p>@endif
            @if ($title)<h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>@endif
        </header>
    @endif
    {{ $slot }}
</section>

