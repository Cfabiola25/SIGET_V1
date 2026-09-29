@props(['id', 'title'])

<dialog id="{{ $id }}" class="w-[min(92vw,36rem)] rounded-2xl border border-slate-200 bg-white p-0 text-slate-800 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-xs">
    <div class="border-b border-slate-100 px-6 py-4.5">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
            <button type="button" aria-label="Cerrar" onclick="document.getElementById('{{ $id }}').close()" class="grid size-8 place-items-center rounded-lg text-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer">&times;</button>
        </div>
    </div>
    <div class="p-6">{{ $slot }}</div>
</dialog>

