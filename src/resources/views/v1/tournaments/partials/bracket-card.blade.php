@php
    $homeWinner = $match->home_score > $match->away_score && $match->status === 'played';
    $awayWinner = $match->away_score > $match->home_score && $match->status === 'played';
@endphp

<div class="relative overflow-hidden rounded-2xl border {{ !empty($isFinal) ? 'border-amber-500/50 bg-slate-950' : 'border-slate-800 bg-slate-900/90' }} p-4 shadow-lg backdrop-blur-sm transition hover:border-slate-700 space-y-3">
    <!-- Header of Match Card -->
    <div class="flex items-center justify-between text-[11px] text-slate-400 border-b border-slate-800/80 pb-2">
        <span class="font-mono font-bold px-2 py-0.5 rounded {{ !empty($isFinal) ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-300' }}">
            {{ $match->bracket_position ?? strtoupper($match->stage) }}
        </span>
        <div class="flex items-center gap-1.5 font-mono">
            @if ($match->isLocked())
                <span class="text-emerald-400 font-bold">Acta Cerrada</span>
            @elseif ($match->status === 'played')
                <span class="text-slate-300">Finalizado</span>
            @elseif ($match->is_timer_running)
                <span class="text-emerald-400 animate-pulse font-bold">EN VIVO {{ $match->formatted_clock }}</span>
            @else
                <span>{{ $match->match_date->format('d/m H:i') }}</span>
            @endif
        </div>
    </div>

    <!-- Teams and Scores Box -->
    <div class="space-y-1.5 text-xs font-semibold">
        <!-- Home Team -->
        <div class="flex items-center justify-between p-2 rounded-xl {{ $homeWinner ? 'bg-emerald-500/10 border border-emerald-500/30 text-white' : 'bg-slate-950/60 text-slate-300' }}">
            <div class="flex items-center gap-2 min-w-0">
                <span class="size-2 rounded-full {{ $homeWinner ? 'bg-emerald-400 shadow-md shadow-emerald-400' : 'bg-slate-700' }}"></span>
                <span class="truncate font-bold {{ $homeWinner ? 'text-emerald-300' : '' }}">
                    {{ $match->homeTeam?->name ?? 'Por definir (TBD)' }}
                </span>
            </div>
            <span class="font-mono text-sm font-black {{ $homeWinner ? 'text-emerald-400' : 'text-slate-400' }}">
                {{ $match->status === 'played' || $match->is_timer_running ? $match->home_score : '-' }}
            </span>
        </div>

        <!-- Away Team -->
        <div class="flex items-center justify-between p-2 rounded-xl {{ $awayWinner ? 'bg-emerald-500/10 border border-emerald-500/30 text-white' : 'bg-slate-950/60 text-slate-300' }}">
            <div class="flex items-center gap-2 min-w-0">
                <span class="size-2 rounded-full {{ $awayWinner ? 'bg-emerald-400 shadow-md shadow-emerald-400' : 'bg-slate-700' }}"></span>
                <span class="truncate font-bold {{ $awayWinner ? 'text-emerald-300' : '' }}">
                    {{ $match->awayTeam?->name ?? 'Por definir (TBD)' }}
                </span>
            </div>
            <span class="font-mono text-sm font-black {{ $awayWinner ? 'text-emerald-400' : 'text-slate-400' }}">
                {{ $match->status === 'played' || $match->is_timer_running ? $match->away_score : '-' }}
            </span>
        </div>
    </div>

    <!-- Footer Actions and Progresion Note -->
    <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
        @if ($match->nextMatch)
            <span class="text-slate-500 truncate" title="El ganador clasifica a {{ $match->nextMatch->bracket_position ?? 'Siguiente Ronda' }}">
                &rarr; Avanza a <strong class="text-slate-300">{{ $match->nextMatch->bracket_position ?? 'Siguiente' }}</strong>
            </span>
        @else
            <span class="text-amber-400 font-bold">&star; Campeón de Torneo</span>
        @endif

        <div class="flex items-center gap-2">
            <a href="{{ route('matches.show', $match) }}" class="font-bold text-emerald-400 hover:underline">
                Ver Partido &rarr;
            </a>
        </div>
    </div>
</div>
