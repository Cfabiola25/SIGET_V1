<?php

use App\Http\Controllers\v1\AdminController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\BracketController;
use App\Http\Controllers\v1\DashboardController;
use App\Http\Controllers\v1\DisciplinaryController;
use App\Http\Controllers\v1\FixtureGeneratorController;
use App\Http\Controllers\v1\MatchController;
use App\Http\Controllers\v1\MatchDayController;
use App\Http\Controllers\v1\MatchLineupController;
use App\Http\Controllers\v1\MatchMvpController;
use App\Http\Controllers\v1\PlayerController;
use App\Http\Controllers\v1\PlayerProfileController;
use App\Http\Controllers\v1\PostMatchController;
use App\Http\Controllers\v1\PublicController;
use App\Http\Controllers\v1\RefereeController;
use App\Http\Controllers\v1\RefereeEvaluationController;
use App\Http\Controllers\v1\RefereeQrController;
use App\Http\Controllers\v1\ScoutingController;
use App\Http\Controllers\v1\SocialMediaCardController;
use App\Http\Controllers\v1\StandingsController;
use App\Http\Controllers\v1\StatisticsController;
use App\Http\Controllers\v1\SuperAdminController;
use App\Http\Controllers\v1\TeamController;
use App\Http\Controllers\v1\TeamInvitationController;
use App\Http\Controllers\v1\TeamPortalController;
use App\Http\Controllers\v1\TournamentController;
use App\Http\Controllers\v1\TournamentRuleController;
use App\Http\Controllers\v1\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home']);
Route::get('/tournament', [PublicController::class, 'tournament'])->name('public.tournament');
Route::get('/teams/invitations/{token}', [TeamInvitationController::class, 'showClaim'])->name('teams.invitations.claim');
Route::post('/teams/invitations/{token}/claim', [TeamInvitationController::class, 'claim'])->name('teams.invitations.accept');
Route::get('/players/{player}/cromo', [PlayerProfileController::class, 'showCromo'])->name('players.cromo');
Route::get('/players/{player}/carnet', [PlayerProfileController::class, 'showCarnet'])->name('players.carnet');
Route::get('/matches/{match}/live-feed', [MatchDayController::class, 'liveFeed'])->name('matches.live.feed');

// Social Media Engine & Fan Engagement (Público)
Route::get('/matches/{match}/social-card', [SocialMediaCardController::class, 'preview'])->name('matches.social_card.preview');
Route::get('/matches/{match}/social-card/download', [SocialMediaCardController::class, 'download'])->name('matches.social_card.download');
Route::get('/matches/{match}/social-card/raw', [SocialMediaCardController::class, 'rawSvg'])->name('matches.social_card.raw');

// Votación MVP en Vivo
Route::post('/matches/{match}/mvp/vote', [MatchMvpController::class, 'vote'])->name('matches.mvp.vote');
Route::get('/matches/{match}/mvp/live-stats', [MatchMvpController::class, 'liveStats'])->name('matches.mvp.stats');

// Scouting & Radar de Talentos (Público para consulta)
Route::get('/scouting', [ScoutingController::class, 'index'])->name('scouting.index');
Route::get('/scouting/{player}', [ScoutingController::class, 'show'])->name('scouting.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        return match ($user->role) {
            'super_admin' => redirect()->route('super-admin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'captain', 'coach' => ($team = $user->managedTeams()->first())
                ? redirect()->route('dt.dashboard', $team)
                : app(DashboardController::class)->index(request()),
            default => app(DashboardController::class)->index(request()),
        };
    })->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('tournaments', TournamentController::class)->except(['create', 'store']);
    Route::get('/tournaments/{tournament}/rules', [TournamentRuleController::class, 'edit'])->name('tournaments.rules.edit');
    Route::put('/tournaments/{tournament}/rules', [TournamentRuleController::class, 'update'])->name('tournaments.rules.update');
    Route::resource('venues', VenueController::class);
    Route::resource('teams', TeamController::class);
    Route::post('/teams/{team}/invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');

    // Portal del Director Técnico (DT)
    Route::prefix('dt/teams/{team}')->name('dt.')->group(function (): void {
        Route::get('/dashboard', [TeamPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/roster', [TeamPortalController::class, 'roster'])->name('roster');
        Route::post('/players', [TeamPortalController::class, 'storePlayer'])->name('players.store');
        Route::post('/roster/import', [TeamPortalController::class, 'importRoster'])->name('roster.import');
        Route::delete('/players/{player}', [TeamPortalController::class, 'destroyPlayer'])->name('players.destroy');
    });

    // Ficha Deportiva & Ficha Médica
    Route::get('/players/{player}/medical', [PlayerProfileController::class, 'editMedical'])->name('players.medical.edit');
    Route::put('/players/{player}/medical', [PlayerProfileController::class, 'updateMedical'])->name('players.medical.update');
    Route::get('/players/{player}/edit-profile', [PlayerProfileController::class, 'editProfile'])->name('players.profile.edit');
    Route::put('/players/{player}/edit-profile', [PlayerProfileController::class, 'updateProfile'])->name('players.profile.update');

    // Alineaciones Digitales Oficiales (DT)
    Route::get('/matches/{match}/teams/{team}/lineup', [MatchLineupController::class, 'edit'])->name('matches.lineup.edit');
    Route::post('/matches/{match}/teams/{team}/lineup', [MatchLineupController::class, 'store'])->name('matches.lineup.store');

    // Control de Acceso QR en Cancha (Árbitro / Mesa de Control)
    Route::get('/matches/{match}/scanner', [RefereeQrController::class, 'scanConsole'])->name('referees.matches.scan.console');
    Route::post('/matches/{match}/scanner/verify', [RefereeQrController::class, 'verifyScan'])->name('referees.matches.scan.verify');

    // Consola Match Day (Árbitro / Mesa de Control)
    Route::get('/matches/{match}/console', [MatchDayController::class, 'console'])->name('matches.console');
    Route::post('/matches/{match}/timer', [MatchDayController::class, 'updateTimer'])->name('matches.timer.update');
    Route::post('/matches/{match}/events', [MatchDayController::class, 'recordEvent'])->name('matches.events.store');

    // Cierre Post-Partido, Firmas Digitales y Acta Oficial (Fase 4)
    Route::get('/matches/{match}/closure', [PostMatchController::class, 'showClosure'])->name('matches.closure');
    Route::post('/matches/{match}/closure', [PostMatchController::class, 'saveSignaturesAndClose'])->name('matches.closure.submit');
    Route::get('/matches/{match}/report', [PostMatchController::class, 'officialReport'])->name('matches.report');

    // Tribunal de Penas y Disciplina Deportiva (Fase 4)
    Route::get('/tournaments/{tournament}/disciplinary', [DisciplinaryController::class, 'index'])->name('tournaments.disciplinary');
    Route::post('/tournaments/{tournament}/disciplinary/{sanction}/pardon', [DisciplinaryController::class, 'pardon'])->name('tournaments.disciplinary.pardon');

    // Generador de Fixtures y Fase de Brackets (Fase 5)
    Route::get('/tournaments/{tournament}/fixtures/generate', [FixtureGeneratorController::class, 'show'])->name('tournaments.fixtures.generate');
    Route::post('/tournaments/{tournament}/fixtures/generate', [FixtureGeneratorController::class, 'generate'])->name('tournaments.fixtures.generate.submit');
    Route::get('/tournaments/{tournament}/brackets', [BracketController::class, 'index'])->name('tournaments.brackets');
    Route::post('/tournaments/{tournament}/brackets/transition', [BracketController::class, 'generateBrackets'])->name('tournaments.brackets.generate');

    // MVP Oficial y Coronación
    Route::post('/matches/{match}/mvp/crown', [MatchMvpController::class, 'crown'])->name('matches.mvp.crown');

    // Scouting y Reclutamiento de Jugadores (DT / Admin)
    Route::post('/scouting/{player}/recruit', [ScoutingController::class, 'recruit'])->name('scouting.recruit');
    Route::post('/scouting/{player}/release', [ScoutingController::class, 'release'])->name('scouting.release');

    // Profesionalización del Arbitraje: Evaluaciones Post-Partido, Asignación Algorítmica y Conflictos
    Route::get('/referees/evaluations', [RefereeEvaluationController::class, 'index'])->name('referees.evaluations.index');
    Route::post('/matches/{match}/referee/evaluate', [RefereeEvaluationController::class, 'evaluate'])->name('matches.referee.evaluate');
    Route::post('/matches/{match}/referee/auto-assign', [RefereeEvaluationController::class, 'autoAssignMatch'])->name('matches.referee.auto_assign');
    Route::post('/tournaments/referees/bulk-assign', [RefereeEvaluationController::class, 'bulkAssignRound'])->name('tournaments.referees.bulk_assign');
    Route::post('/referees/conflicts', [RefereeEvaluationController::class, 'registerConflict'])->name('referees.conflicts.store');

    Route::get('/matches/live', [MatchController::class, 'live'])->name('matches.live');
    Route::resource('matches', MatchController::class);
    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('/standings', [StandingsController::class, 'index'])->name('standings.index');
    Route::get('/statistics', [StatisticsController::class, 'show'])->name('statistics.show');
    Route::get('/referees', [RefereeController::class, 'index'])->name('referees.index');
});

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'active', 'role:super_admin'])->group(function (): void {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');
    Route::post('/admins', [SuperAdminController::class, 'storeAdmin'])->name('admins.store');
    Route::put('/admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('admins.update');
    Route::patch('/admins/{admin}/status', [SuperAdminController::class, 'updateAdminStatus'])->name('admins.status');
    Route::put('/admins/{admin}/password', [SuperAdminController::class, 'resetAdminPassword'])->name('admins.password');
    Route::post('/tournaments', [SuperAdminController::class, 'storeTournament'])->name('tournaments.store');
    Route::post('/sports', [SuperAdminController::class, 'storeSport'])->name('sports.store');
    Route::delete('/sports/{sport}', [SuperAdminController::class, 'destroySport'])->name('sports.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
});
