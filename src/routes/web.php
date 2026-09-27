<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\AdminController;
use App\Http\Controllers\v1\DashboardController;
use App\Http\Controllers\v1\MatchController;
use App\Http\Controllers\v1\PlayerController;
use App\Http\Controllers\v1\PublicController;
use App\Http\Controllers\v1\RefereeController;
use App\Http\Controllers\v1\StandingsController;
use App\Http\Controllers\v1\StatisticsController;
use App\Http\Controllers\v1\SuperAdminController;
use App\Http\Controllers\v1\TeamController;
use App\Http\Controllers\v1\TournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home']);
Route::get('/tournament', [PublicController::class, 'tournament'])->name('public.tournament');

Route::middleware('guest')->group(function (): void {
	Route::get('/login', [AuthController::class, 'login'])->name('login');
	Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
	Route::get('/register', [AuthController::class, 'register'])->name('register');
	Route::post('/register', [AuthController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
	Route::get('/dashboard', function () {
		return match (auth()->user()->role) {
			'super_admin' => redirect()->route('super-admin.dashboard'),
			'admin' => redirect()->route('admin.dashboard'),
			default => app(DashboardController::class)->index(request()),
		};
	})->name('dashboard');
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
	Route::resource('tournaments', TournamentController::class)->except(['create', 'store']);
	Route::resource('teams', TeamController::class);
	Route::get('/matches/live', [MatchController::class, 'live'])->name('matches.live');
	Route::resource('matches', MatchController::class);
	Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
	Route::get('/standings', [StandingsController::class, 'index'])->name('standings.index');
	Route::get('/statistics', [StatisticsController::class, 'show'])->name('statistics.show');
	Route::get('/referees', [RefereeController::class, 'index'])->name('referees.index');
});

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'role:super_admin'])->group(function (): void {
	Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');
	Route::post('/admins', [SuperAdminController::class, 'storeAdmin'])->name('admins.store');
	Route::post('/tournaments', [SuperAdminController::class, 'storeTournament'])->name('tournaments.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function (): void {
	Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
});
