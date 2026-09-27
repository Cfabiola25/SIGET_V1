@props(['title' => null, 'eyebrow' => null])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md transition-colors hover:border-slate-700']) }}>
    @if ($eyebrow || $title)
        <header class="mb-5">
            @if ($eyebrow)<p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">{{ $eyebrow }}</p>@endif
            @if ($title)<h2 class="mt-1 text-xl font-bold tracking-tight text-white">{{ $title }}</h2>@endif
        </header>
    @endif
    {{ $slot }}
</section>
