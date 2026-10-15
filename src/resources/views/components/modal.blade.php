@props(['id', 'title', 'maxWidth' => 'max-w-xl'])

<dialog id="{{ $id }}" class="fixed inset-0 m-auto {{ $maxWidth }} w-[min(94vw,42rem)] max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/90 bg-white p-0 text-slate-800 shadow-2xl backdrop:bg-slate-950/70 backdrop:backdrop-blur-sm">
    <div class="sticky top-0 z-20 border-b border-slate-100 bg-white/95 backdrop-blur-xs px-6 py-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $title }}</h2>
            <button type="button" aria-label="Cerrar" onclick="document.getElementById('{{ $id }}').close()" class="grid size-8 place-items-center rounded-lg text-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer">&times;</button>
        </div>
    </div>
    <div class="p-6">{{ $slot }}</div>
</dialog>


