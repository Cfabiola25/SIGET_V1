<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\DashboardController;
use App\Http\Controllers\v1\MatchController;
use App\Http\Controllers\v1\PlayerController;
use App\Http\Controllers\v1\PublicController;
use App\Http\Controllers\v1\RefereeController;
use App\Http\Controllers\v1\StandingsController;
use App\Http\Controllers\v1\StatisticsController;
use App\Http\Controllers\v1\TeamController;
use App\Http\Controllers\v1\TournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home']);
Route::get('/tournament', [PublicController::class, 'tournament']);
Route::get('/login', [AuthController::class, 'login']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/tournaments', [TournamentController::class, 'index']);
Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
Route::get('/teams', [TeamController::class, 'index']);
Route::get('/players', [PlayerController::class, 'index']);
Route::get('/matches', [MatchController::class, 'index']);
Route::get('/matches/live', [MatchController::class, 'live']);
Route::get('/standings', [StandingsController::class, 'index']);
Route::get('/statistics', [StatisticsController::class, 'show']);
Route::get('/referees', [RefereeController::class, 'index']);
