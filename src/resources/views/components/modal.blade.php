@props(['id', 'title'])

<dialog id="{{ $id }}" class="w-[min(92vw,36rem)] rounded-xl border border-slate-800 bg-slate-900 p-0 text-slate-100 shadow-2xl backdrop:bg-slate-950/70">
    <div class="border-b border-siget-ink/10 px-6 py-5">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-siget-ink">{{ $title }}</h2>
            <button type="button" aria-label="Cerrar" onclick="document.getElementById('{{ $id }}').close()" class="grid size-8 place-items-center rounded-full text-xl text-siget-muted hover:bg-siget-mint">&times;</button>
        </div>
    </div>
    <div class="p-6">{{ $slot }}</div>
</dialog>
