@php
    $homeWinner = $match->home_score > $match->away_score && $match->status === 'played';
    $awayWinner = $match->away_score > $match->home_score && $match->status === 'played';
@endphp

<div class="relative overflow-hidden rounded-2xl border {{ !empty($isFinal) ? 'border-amber-400 bg-amber-50/20' : 'border-slate-200/90 bg-white' }} p-4 shadow-2xs transition hover:shadow-xs space-y-3">
    <!-- Header of Match Card -->
    <div class="flex items-center justify-between text-[11px] text-slate-500 border-b border-slate-100 pb-2">
        <span class="font-bold px-2 py-0.5 rounded {{ !empty($isFinal) ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-700' }}">
            {{ $match->bracket_position ?? strtoupper($match->stage) }}
        </span>
        <div class="flex items-center gap-1.5 font-medium">
            @if ($match->isLocked())
                <span class="text-emerald-800 font-bold">Acta Cerrada</span>
            @elseif ($match->status === 'played')
                <span class="text-slate-600">Finalizado</span>
            @elseif ($match->is_timer_running)
                <span class="text-emerald-700 animate-pulse font-bold">EN VIVO {{ $match->formatted_clock }}</span>
            @else
                <span>{{ $match->match_date->format('d/m H:i') }}</span>
            @endif
        </div>
    </div>

    <!-- Teams and Scores Box -->
    <div class="space-y-1.5 text-xs font-semibold">
        <!-- Home Team -->
        <div class="flex items-center justify-between p-2 rounded-xl {{ $homeWinner ? 'bg-emerald-50 border border-emerald-200 text-slate-900 font-black' : 'bg-slate-50 text-slate-700' }}">
            <div class="flex items-center gap-2 min-w-0">
                <span class="size-2 rounded-full {{ $homeWinner ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                <span class="truncate {{ $homeWinner ? 'text-emerald-900' : '' }}">
                    {{ $match->homeTeam?->name ?? 'Por definir (TBD)' }}
                </span>
            </div>
            <span class="text-sm font-black {{ $homeWinner ? 'text-emerald-800' : 'text-slate-400' }}">
                {{ $match->status === 'played' || $match->is_timer_running ? $match->home_score : '-' }}
            </span>
        </div>

        <!-- Away Team -->
        <div class="flex items-center justify-between p-2 rounded-xl {{ $awayWinner ? 'bg-emerald-50 border border-emerald-200 text-slate-900 font-black' : 'bg-slate-50 text-slate-700' }}">
            <div class="flex items-center gap-2 min-w-0">
                <span class="size-2 rounded-full {{ $awayWinner ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                <span class="truncate {{ $awayWinner ? 'text-emerald-900' : '' }}">
                    {{ $match->awayTeam?->name ?? 'Por definir (TBD)' }}
                </span>
            </div>
            <span class="text-sm font-black {{ $awayWinner ? 'text-emerald-800' : 'text-slate-400' }}">
                {{ $match->status === 'played' || $match->is_timer_running ? $match->away_score : '-' }}
            </span>
        </div>
    </div>

    <!-- Footer Actions and Progresion Note -->
    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
        @if ($match->nextMatch)
            <span class="text-slate-400 truncate">
                &rarr; Avanza a <strong class="text-slate-700">{{ $match->nextMatch->bracket_position ?? 'Siguiente' }}</strong>
            </span>
        @else
            <span class="text-amber-700 font-bold">🏆 Campeón</span>
        @endif

        <a href="{{ route('matches.show', $match) }}" class="text-xs font-bold text-emerald-800 hover:text-emerald-900">
            Ver &rarr;
        </a>
    </div>
</div>
