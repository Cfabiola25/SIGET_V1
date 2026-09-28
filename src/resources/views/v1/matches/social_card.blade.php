@extends('v1.layouts.app')

@section('title', 'Social Media Card Engine')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                    <span class="size-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    SOCIAL MEDIA ENGINE
                </span>
                <span class="text-xs text-slate-500">Instagram • TikTok • Facebook</span>
            </div>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Generador Visual de Assets para Redes</h1>
            <p class="text-sm text-slate-400">Genera instantáneamente piezas de diseño con la estética oficial de la Champions League para compartir en Instagram Stories y Feed.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('matches.show', $match) }}" class="rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                ← Volver al Partido
            </a>
            <button id="btnDownloadCanvas" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 px-5 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-400 hover:to-teal-300">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Descargar PNG HD
            </button>
        </div>
    </div>

    <!-- Controls Panel -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-xl">
        <form method="GET" action="{{ route('matches.social_card.preview', $match) }}" class="grid grid-cols-1 gap-5 md:grid-cols-4">
            <!-- Tipo de Asset -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Contenido de la Pieza</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="submit" name="type" value="final_score" class="rounded-xl border px-3 py-2.5 text-xs font-bold transition {{ $type === 'final_score' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-400' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700' }}">
                        Marcador
                    </button>
                    <button type="submit" name="type" value="lineup" class="rounded-xl border px-3 py-2.5 text-xs font-bold transition {{ $type === 'lineup' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-400' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700' }}">
                        Alineación
                    </button>
                    <button type="submit" name="type" value="mvp" class="rounded-xl border px-3 py-2.5 text-xs font-bold transition {{ $type === 'mvp' ? 'border-amber-500 bg-amber-500/10 text-amber-400' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700' }}">
                        MVP Oro
                    </button>
                </div>
            </div>

            <!-- Formato / Relación de Aspecto -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Formato de Red Social</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="submit" name="format" value="feed" class="rounded-xl border px-3 py-2.5 text-xs font-bold transition {{ $format === 'feed' ? 'border-cyan-500 bg-cyan-500/10 text-cyan-400' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700' }}">
                        Feed (1:1)
                    </button>
                    <button type="submit" name="format" value="story" class="rounded-xl border px-3 py-2.5 text-xs font-bold transition {{ $format === 'story' ? 'border-cyan-500 bg-cyan-500/10 text-cyan-400' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700' }}">
                        Story (9:16)
                    </button>
                </div>
            </div>

            <!-- Equipo (si es alineación) -->
            @if($type === 'lineup')
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Equipo a Renderizar</label>
                <select name="team_id" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-xs font-semibold text-slate-200 focus:border-emerald-500 focus:outline-none">
                    <option value="{{ $match->home_team_id }}" {{ $teamId == $match->home_team_id ? 'selected' : '' }}>{{ $match->homeTeam?->name }} (Local)</option>
                    <option value="{{ $match->away_team_id }}" {{ $teamId == $match->away_team_id ? 'selected' : '' }}>{{ $match->awayTeam?->name }} (Visitante)</option>
                </select>
            </div>
            @else
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Dimensiones Finales</label>
                <div class="rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-xs text-slate-300">
                    {{ $format === 'story' ? '1080 x 1920 px (Instagram Story)' : '1080 x 1080 px (Instagram Post)' }}
                </div>
            </div>
            @endif

            <!-- Enlace Directo / Copiar -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Acciones Rápidas</label>
                <div class="flex items-center gap-2">
                    <a href="{{ route('matches.social_card.download', [$match, 'type' => $type, 'format' => $format, 'team_id' => $teamId]) }}" class="flex-1 text-center rounded-xl border border-slate-700 bg-slate-800 px-3 py-2.5 text-xs font-bold text-slate-200 transition hover:bg-slate-700">
                        Descarga Servidor
                    </a>
                    <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Enlace de la tarjeta copiado al portapapeles');" class="rounded-xl border border-slate-800 bg-slate-950 p-2.5 text-slate-400 hover:text-emerald-400 transition" title="Copiar enlace">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Container -->
    <div class="flex flex-col items-center justify-center rounded-3xl border border-slate-800 bg-slate-950/80 p-6 sm:p-10 shadow-2xl backdrop-blur-2xl">
        <div id="cardWrapper" class="relative overflow-hidden rounded-2xl shadow-2xl border border-slate-800 transition-all duration-300 {{ $format === 'story' ? 'max-w-sm aspect-[9/16]' : 'max-w-xl aspect-square' }} w-full">
            {!! $svgContent !!}
        </div>
        <p class="mt-4 text-xs text-slate-500 font-medium text-center">Renderizado vectorial en alta fidelidad. Haz clic en "Descargar PNG HD" para obtener el archivo listo para publicar.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnDownloadCanvas');
    const svgElem = document.querySelector('#cardWrapper svg');

    if (!btn || !svgElem) return;

    btn.addEventListener('click', () => {
        btn.disabled = true;
        btn.innerText = 'Renderizando PNG...';

        const svgData = new XMLSerializer().serializeToString(svgElem);
        const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
        const URL = window.URL || window.webkitURL || window;
        const blobURL = URL.createObjectURL(svgBlob);

        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = {{ $format === 'story' ? 1080 : 1080 }};
            canvas.height = {{ $format === 'story' ? 1920 : 1080 }};
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);

            canvas.toBlob((blob) => {
                const downloadLink = document.createElement('a');
                downloadLink.download = "SIGET_{{ $type }}_{{ $match->id }}_{{ $format }}.png";
                downloadLink.href = URL.createObjectURL(blob);
                downloadLink.click();
                URL.revokeObjectURL(blobURL);
                btn.disabled = false;
                btn.innerHTML = `<svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Descargar PNG HD`;
            }, 'image/png');
        };
        img.src = blobURL;
    });
});
</script>
@endsection
