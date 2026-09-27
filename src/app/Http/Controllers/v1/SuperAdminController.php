<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreTournamentRequest;
use App\Models\User;
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
            'admins' => User::where('role', 'admin')->orderBy('name')->get(),
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

    public function storeTournament(StoreTournamentRequest $request): RedirectResponse
    {
        $assignment = Validator::make($request->all(), [
            'admin_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'admin')),
            ],
        ])->validate();

        Tournament::create(array_merge($request->validated(), [
            'super_admin_id' => $request->user()->id,
            'admin_id' => $assignment['admin_id'],
        ]));

        return redirect()->route('super-admin.dashboard')->with('status', 'Torneo creado correctamente.');
    }
}
