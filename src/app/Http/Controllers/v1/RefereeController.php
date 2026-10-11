<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\v1\Referee;
use App\Models\v1\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RefereeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);

        $query = Referee::withCount('matches');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $referees = $query->orderByDesc('rating_average')->paginate(12)->withQueryString();

        $totalReferees = Referee::count();
        $activeReferees = Referee::where('is_active', true)->count();
        $avgRating = Referee::where('is_active', true)->avg('rating_average') ?? 4.8;
        $totalMatches = Referee::sum('total_matches_officiated');

        if (auth()->user()->isAdmin()) {
            $tournaments = Tournament::where('admin_id', auth()->id())->whereIn('status', ['active', 'pending'])->get();
        } else {
            $tournaments = Tournament::whereIn('status', ['active', 'pending'])->get();
        }

        return view('v1.referees.index', compact(
            'referees',
            'totalReferees',
            'activeReferees',
            'avgRating',
            'totalMatches',
            'tournaments'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:50', 'unique:referees,license_number'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = null;
        if (! empty($validated['email'])) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'password'),
                'role' => 'referee',
                'is_active' => true,
            ]);
        }

        Referee::create([
            'user_id' => $user?->id,
            'name' => $validated['name'],
            'license_number' => $validated['license_number'],
            'is_active' => $request->boolean('is_active', true),
            'rating_average' => 5.0,
            'total_matches_officiated' => 0,
        ]);

        return redirect()->route('referees.index')->with('status', 'Colegiado arbitral registrado exitosamente.');
    }

    public function destroy(Referee $referee)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);

        $referee->delete();

        return redirect()->route('referees.index')->with('status', 'Colegiado eliminado del padrón.');
    }
}
