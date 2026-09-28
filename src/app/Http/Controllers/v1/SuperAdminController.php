<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreTournamentRequest;
use App\Models\User;
use App\Models\v1\Sport;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function index(): View
    {
        return view('v1.super-admin.dashboard', [
            'tournaments' => Tournament::with('admin')->latest()->get(),
            'admins' => User::where('role', 'admin')->withCount('managedTournaments')->orderBy('name')->get(),
            'activeAdmins' => User::where('role', 'admin')->where('is_active', true)->orderBy('name')->get(),
            'sports' => Sport::orderBy('name')->get(),
        ]);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        return redirect()->route('super-admin.dashboard')->with('status', 'Administrador creado correctamente.');
    }

    public function updateAdminStatus(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $admin->update(['is_active' => $data['is_active']]);

        return redirect()->route('super-admin.dashboard')->with('status', 'Estado del administrador actualizado.');
    }

    public function updateAdmin(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
        ]);

        $admin->update($data);

        return redirect()->route('super-admin.dashboard')->with('status', 'Administrador actualizado correctamente.');
    }

    public function resetAdminPassword(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('super-admin.dashboard')->with('status', 'Contraseña restablecida correctamente.');
    }

    public function storeTournament(StoreTournamentRequest $request): RedirectResponse
    {
        $assignment = Validator::make($request->all(), [
            'admin_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'admin')->where('is_active', true)),
            ],
        ])->validate();

        Tournament::create(array_merge($request->validated(), [
            'super_admin_id' => $request->user()->id,
            'admin_id' => $assignment['admin_id'],
        ]));

        return redirect()->route('super-admin.dashboard')->with('status', 'Torneo creado correctamente.');
    }

    public function storeSport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sports,name'],
        ]);

        Sport::create($data);

        return redirect()->route('super-admin.dashboard')->with('status', 'Deporte agregado correctamente.');
    }

    public function destroySport(Sport $sport): RedirectResponse
    {
        if (Tournament::where('sport_type', $sport->name)->exists()) {
            return redirect()->route('super-admin.dashboard')
                ->withErrors(['sport' => 'No se puede quitar un deporte que ya está asignado a torneos.']);
        }

        $sport->delete();

        return redirect()->route('super-admin.dashboard')->with('status', 'Deporte quitado correctamente.');
    }
}
