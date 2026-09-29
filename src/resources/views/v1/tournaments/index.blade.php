@extends('v1.layouts.app')

@section('title', 'Tournaments Management')
@section('header_title', 'Tournaments')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
                Active & Upcoming Tournaments
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                Manage competitions, fixtures, group stages, and tournament rules.
            </p>
        </div>

        <a href="{{ route('tournaments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs md:text-sm font-bold shadow-xs transition">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>+ New Tournament</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
        <form method="GET" action="{{ route('tournaments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            
            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Search Tournament</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name..." 
                       class="w-full text-xs md:text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sport / Discipline</label>
                <select name="sport_type" class="w-full text-xs md:text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">All Sports</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->name }}" @selected(($filters['sport_type'] ?? '') === $sport->name)>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
                <select name="status" class="w-full text-xs md:text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                    <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-3 bg-sky-100 hover:bg-sky-200 text-sky-900 text-xs md:text-sm font-bold rounded-lg transition text-center cursor-pointer shadow-2xs">
                    Apply Filters
                </button>
                @if (!empty($filters['search']) || !empty($filters['sport_type']) || !empty($filters['status']))
                    <a href="{{ route('tournaments.index') }}" class="p-2 text-slate-400 hover:text-slate-700" title="Limpiar">&times;</a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tournaments Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($tournaments as $tournament)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 flex flex-col justify-between hover:border-slate-300 transition-all group">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        @if ($tournament->status === 'active')
                            <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-0.5 rounded-full uppercase tracking-wider">
                                ACTIVE
                            </span>
                        @elseif ($tournament->status === 'completed')
                            <span class="inline-block bg-slate-100 text-slate-700 text-[10px] font-extrabold px-3 py-0.5 rounded-full uppercase tracking-wider">
                                COMPLETED
                            </span>
                        @else
                            <span class="inline-block bg-[#fef3c7] text-[#92400e] text-[10px] font-extrabold px-3 py-0.5 rounded-full uppercase tracking-wider">
                                PENDING
                            </span>
                        @endif

                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">
                            {{ $tournament->sport_type }}
                        </span>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-slate-900 group-hover:text-emerald-700 transition">
                        <a href="{{ route('tournaments.show', $tournament) }}">{{ $tournament->name }}</a>
                    </h3>

                    <p class="mt-2 text-xs text-slate-500 font-medium">
                        {{ $tournament->start_date ? $tournament->start_date->format('d M Y') : 'Octubre 2024' }} — {{ $tournament->end_date ? $tournament->end_date->format('d M Y') : 'Diciembre 2024' }}
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-3 border-y border-slate-100 py-3 text-xs text-slate-500">
                        <div>
                            <strong class="block text-base font-black text-slate-900">{{ $tournament->teams()->count() }}</strong>
                            <span>Teams</span>
                        </div>
                        <div>
                            <strong class="block text-base font-black text-slate-900">{{ $tournament->matches()->count() }}</strong>
                            <span>Matches</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-2 flex items-center justify-between">
                    <a href="{{ route('tournaments.show', $tournament) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1 transition">
                        <span>Manage Tournament</span>
                        <span>&rarr;</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('tournaments.brackets', $tournament) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition" title="Llaves & Brackets">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h4v4H4V6zm12 0h4v4h-4V6zm-6 7h4v4h-4v-4z"/></svg>
                        </a>
                        <a href="{{ route('tournaments.disciplinary', $tournament) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition" title="Tribunal de Disciplina">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center text-slate-500">
                No tournaments found matching the filters.
            </div>
        @endforelse
    </div>

    @if ($tournaments instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $tournaments->links('pagination::tailwind') }}
        </div>
    @endif

</div>
@endsection
