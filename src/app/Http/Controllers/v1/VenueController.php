<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(): View
    {
        $venues = Venue::withCount('matches')->latest()->paginate(15);

        return view('v1.venues.index', compact('venues'));
    }

    public function create(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['super_admin', 'admin'], true), 403);

        return view('v1.venues.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'admin'], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'field_count' => ['required', 'integer', 'min:1', 'max:50'],
            'surface_type' => ['required', 'in:grass,synthetic,dirt,hybrid,indoor'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $venue = Venue::create([
            ...$validated,
            'created_by_user_id' => $request->user()->id,
            'is_active' => true,
        ]);

        return redirect()->route('venues.index')->with('status', "Sede '{$venue->name}' registrada exitosamente.");
    }

    public function show(Venue $venue): View
    {
        return view('v1.venues.show', [
            'venue' => $venue->load(['matches.homeTeam', 'matches.awayTeam', 'matches.tournament']),
        ]);
    }

    public function edit(Venue $venue): View
    {
        abort_unless(in_array(auth()->user()?->role, ['super_admin', 'admin'], true), 403);

        return view('v1.venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'admin'], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'field_count' => ['required', 'integer', 'min:1', 'max:50'],
            'surface_type' => ['required', 'in:grass,synthetic,dirt,hybrid,indoor'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $venue->update($validated);

        return redirect()->route('venues.show', $venue)->with('status', 'Sede actualizada exitosamente.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['super_admin', 'admin'], true), 403);

        if ($venue->matches()->exists()) {
            return back()->withErrors(['venue' => 'No se puede eliminar una sede que tiene partidos asignados.']);
        }

        $venue->delete();

        return redirect()->route('venues.index')->with('status', 'Sede eliminada correctamente.');
    }
}
