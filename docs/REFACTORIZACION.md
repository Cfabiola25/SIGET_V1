# REFACTORIZACIÓN DEL CÓDIGO FUENTE – SIGET

**GUÍA PRÁCTICA N.º 11 – EVALUACIÓN, MÉTRICAS, CALIDAD Y MANTENIMIENTO DEL SOFTWARE**
**SIGET-SF – Sistema Integral de Gestión de Torneos Deportivos**

| | |
|---|---|
| **Integrantes** | Néstor Iván Granados Valenzuela · Nelly Fabiola Cano Oviedo |
| **Institución** | Fundación de Estudios Superiores Comfanorte (FESC) |
| **Programa** | Ingeniería de Software – V Semestre |
| **Asignatura** | Arquitectura y Diseño del Software – Unidad 3: Métricas del Software |
| **Docente** | Ing. Robinson Damián Gómez Sánchez |

Este documento describe las cinco mejoras implementadas directamente en el código de SIGET. Cada mejora tiene su propio commit (ver la sección 7) y se validó con las pruebas de [PRUEBAS_SOFTWARE.md](PRUEBAS_SOFTWARE.md). Los problemas se describen en detalle en [EVALUACION_SOFTWARE.md](EVALUACION_SOFTWARE.md#6-problemas-identificados).

| # | Mejora | Técnica principal | Commit |
|---|---|---|---|
| 1 | Tabla de posiciones según el reglamento del torneo | Extracción de métodos, reemplazo de números mágicos | `9be5fbc`, `54e7f99` |
| 2 | Eliminación de código muerto | Eliminación de código innecesario (YAGNI) | `00af358` |
| 3 | Verificación de roles centralizada | Mover método al modelo / eliminar duplicidad (DRY) | `e3dec29` |
| 4 | Eliminación de los 4 clones de código | Extracción de método, *query scope*, método fábrica | `c67f374` |
| 5 | Registro de equipos seguro y con responsabilidad única | Extracción de clase (servicio), simplificación de condicionales | `b6f2b7e` |

---

## Mejora 1 – La tabla de posiciones aplica el reglamento del torneo

**Descripción del problema.** `StandingService::recalculate()` usaba puntos fijos (3 por victoria, 1 por empate) aunque cada torneo guarda su propia puntuación en `tournament_rules`, que el administrador edita en *Reglamento*. Con un torneo configurado a 2 puntos por victoria, la tabla oficial mostraba 3. El método tenía 55 NLOC y mezclaba inicialización, acumulación de resultados y persistencia. Además, la clase estaba en `App\Services` y no en `App\Services\v1`.

**Ruta del archivo intervenido.** `src/app/Services/StandingService.php` → `src/app/Services/v1/StandingService.php` (también se actualizaron los imports en `MatchController`, `PostMatchClosureService` y `TournamentCompleteSeeder`).

**Antes:**

```php
// src/app/Services/StandingService.php
namespace App\Services;

class StandingService
{
    public function recalculate(Tournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament): Collection {
            $stats = $tournament->teams()->get()->mapWithKeys(fn ($team) => [$team->id => [
                'matches_played' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0,
                'goals_for' => 0, 'goals_against' => 0, 'points' => 0,
            ]]);

            $tournament->matches()->where('status', 'played')->get()
                ->each(function ($match) use ($stats): void {
                    $home = $stats->get($match->home_team_id);
                    $away = $stats->get($match->away_team_id);
                    // ...
                    if ($match->home_score > $match->away_score) {
                        $home['wins']++;
                        $home['points'] += 3;          // número mágico: ignora points_for_win
                        $away['losses']++;
                    } elseif ($match->home_score < $match->away_score) {
                        $away['wins']++;
                        $away['points'] += 3;
                        $home['losses']++;
                    } else {
                        $home['draws']++;
                        $away['draws']++;
                        $home['points']++;             // número mágico: ignora points_for_draw
                        $away['points']++;
                    }
                    $stats->put($match->home_team_id, $home);
                    $stats->put($match->away_team_id, $away);
                });
            // ... updateOrCreate por equipo
        });
    }
}
```

**Después:**

```php
// src/app/Services/v1/StandingService.php
namespace App\Services\v1;

class StandingService
{
    /** Puntuación por defecto cuando el torneo no tiene reglamento registrado. */
    private const DEFAULT_POINTS = ['win' => 3, 'draw' => 1, 'loss' => 0];

    public function recalculate(Tournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament): Collection {
            $points = $this->pointsFor($tournament);
            $table = $tournament->teams()->pluck('id')
                ->mapWithKeys(fn (int $teamId): array => [$teamId => $this->emptyRow()]);

            $tournament->matches()->where('status', 'played')->get()
                ->each(fn (MatchGame $match) => $this->applyMatch($table, $match, $points));

            $this->persist($tournament, $table);

            return $tournament->standings()->with('team')->orderByDesc('points')->get();
        });
    }

    private function pointsFor(Tournament $tournament): array
    {
        $rules = $tournament->rules;
        if (! $rules instanceof TournamentRule) {
            return self::DEFAULT_POINTS;
        }

        return [
            'win' => $rules->points_for_win ?? self::DEFAULT_POINTS['win'],
            'draw' => $rules->points_for_draw ?? self::DEFAULT_POINTS['draw'],
            'loss' => $rules->points_for_loss ?? self::DEFAULT_POINTS['loss'],
        ];
    }

    private function addResult(array $row, int $scored, int $conceded, array $points): array
    {
        $outcome = match (true) {
            $scored > $conceded => ['key' => 'wins', 'points' => $points['win']],
            $scored < $conceded => ['key' => 'losses', 'points' => $points['loss']],
            default => ['key' => 'draws', 'points' => $points['draw']],
        };

        $row['matches_played']++;
        $row['goals_for'] += $scored;
        $row['goals_against'] += $conceded;
        $row[$outcome['key']]++;
        $row['points'] += $outcome['points'];

        return $row;
    }
    // applyMatch(), emptyRow() y persist() completan el flujo
}
```

**Técnica de refactorización aplicada.** Extracción de métodos, reemplazo de números mágicos por una constante con nombre y por datos de configuración, simplificación de condicionales (`if/elseif/else` duplicado para local y visitante → un único `match` reutilizado) y reorganización de componentes (namespace `v1`).

**Principios y buenas prácticas.** SRP (cada método hace una sola cosa), DRY (la lógica de resultado se escribe una vez para ambos equipos), OCP (cambiar la puntuación no requiere tocar código), nombres descriptivos, KISS.

**Justificación técnica.** El reglamento ya existía en la base de datos y en la interfaz, pero el servicio no lo leía: era un defecto funcional escondido detrás de un número mágico. Separar el cálculo de un resultado (`addResult`) permite razonar sobre local y visitante con la misma función y evita el código espejo.

**Beneficios obtenidos.** La tabla respeta la puntuación de cada torneo (prueba CP-03, que fallaba antes, ahora aprueba). Complejidad máxima del servicio: 5 → 3. El cálculo se puede leer y probar por partes.

---

## Mejora 2 – Eliminación de código muerto

**Descripción del problema.** PHPMD (`unusedcode`) reportó variables y parámetros sin uso. En la revisión apareció además `StandingsService`, una clase que solo devolvía un arreglo vacío y que nadie usaba, con un nombre casi igual al servicio real `StandingService`.

**Rutas de los archivos intervenidos.**
`src/app/Services/v1/StandingsService.php` (eliminado) · `src/app/Http/Controllers/v1/PlayerProfileController.php` · `src/app/Services/v1/SportsChronicleService.php` · `src/app/Http/Controllers/v1/AuditController.php` · `src/app/Http/Controllers/v1/ScoutingController.php` · `src/app/Http/Controllers/v1/StandingsController.php`

**Antes:**

```php
// src/app/Services/v1/StandingsService.php  (clase completa, sin referencias)
class StandingsService
{
    public function calculate(): array
    {
        return [];
    }
}

// PlayerProfileController::updateMedical()
$medical = $player->medicalRecord()->updateOrCreate([...]);   // $medical nunca se usa

// SportsChronicleService::generateAlgorithmicChronicle()
} elseif ($lateGoal) {
    $scorer = $lateGoal->player?->name ?? 'el artillero';      // $scorer nunca se usa
    $title = "¡Agonía y Gloria en el minuto {$lateGoal->minute}! ...";

// AuditController / StandingsController / ScoutingController
public function index(Request $request)                        // $request nunca se usa
public function adminIndex(Request $request): View|RedirectResponse
public function recruitmentRequests(Request $request): View|RedirectResponse
```

**Después:**

```php
// StandingsService.php eliminado del repositorio

// PlayerProfileController::updateMedical()
$player->medicalRecord()->updateOrCreate([...]);

// SportsChronicleService::generateAlgorithmicChronicle()
} elseif ($lateGoal) {
    $title = "¡Agonía y Gloria en el minuto {$lateGoal->minute}! ...";

// Firmas sin parámetros innecesarios (y sin el import de Request que sobraba en AuditController)
public function index()
public function adminIndex(): View|RedirectResponse
public function recruitmentRequests(): View|RedirectResponse
```

**Técnica de refactorización aplicada.** Eliminación de código innecesario.

**Principios y buenas prácticas.** YAGNI, KISS, nombres sin ambigüedad.

**Justificación técnica.** El código sin uso no aporta comportamiento pero sí costo: confunde (dos servicios de "posiciones"), hay que leerlo y mantenerlo, y hace creer que existe una funcionalidad que en realidad no existe.

**Beneficios obtenidos.** Las 5 alertas `UnusedFormalParameter`/`UnusedLocalVariable` de PHPMD bajaron a 0 y desapareció la ambigüedad entre `StandingService` y `StandingsService`.

---

## Mejora 3 – Verificación de roles centralizada en el modelo `User`

**Descripción del problema.** La regla "solo el super administrador y el administrador entran al backoffice" estaba copiada 23 veces en 8 controladores, y había 4 variantes para DT/capitán. En el modelo, `isCaptain()`, `isCoach()` e `isDT()` tenían el mismo cuerpo repetido.

**Rutas de los archivos intervenidos.** `src/app/Models/User.php` y los controladores `AuditController`, `MatchController`, `PlayerController`, `RefereeController`, `StandingsController`, `TeamController`, `TournamentController` y `VenueController` (carpeta `src/app/Http/Controllers/v1/`).

**Antes:**

```php
// VenueController (7 veces en el mismo archivo), MatchController, RefereeController, ...
abort_unless(in_array(auth()->user()?->role, ['super_admin', 'admin'], true), 403);
abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);
abort_unless(in_array($request->user()->role, ['super_admin', 'admin'], true), 403);

// TeamController
abort_unless(in_array($request->user()->role, ['super_admin', 'admin', 'captain'], true), 403);
'status' => in_array($user->role, ['super_admin', 'admin'], true) ? 'approved' : 'pending',

// User.php
public function isCaptain(): bool { return in_array($this->role, ['captain', 'coach'], true); }
public function isCoach(): bool   { return in_array($this->role, ['captain', 'coach'], true); }
public function isDT(): bool      { return in_array($this->role, ['captain', 'coach'], true); }
```

**Después:**

```php
// src/app/Models/User.php
public const ROLE_SUPER_ADMIN = 'super_admin';
public const ROLE_ADMIN = 'admin';
public const ROLE_CAPTAIN = 'captain';
public const ROLE_COACH = 'coach';

/** Roles con acceso al backoffice administrativo. */
public const STAFF_ROLES = [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN];

/** Roles que actúan como Director Técnico de un equipo. */
public const DT_ROLES = [self::ROLE_CAPTAIN, self::ROLE_COACH];

public function isStaff(): bool
{
    return in_array($this->role, self::STAFF_ROLES, true);
}

public function canRegisterTeams(): bool
{
    return $this->isStaff() || $this->role === self::ROLE_CAPTAIN;
}

public function isCaptain(): bool { return $this->isDT(); }
public function isCoach(): bool   { return $this->isDT(); }
public function isDT(): bool      { return in_array($this->role, self::DT_ROLES, true); }

// Controladores
abort_unless(auth()->user()?->isStaff(), 403);
abort_unless($request->user()->isStaff(), 403);
abort_unless($user->canRegisterTeams(), 403);
'status' => $user->isStaff() ? 'approved' : 'pending',
```

**Técnica de refactorización aplicada.** Mover método (la regla pasa al objeto que tiene los datos), eliminación de código duplicado, reemplazo de literales por constantes con nombre.

**Principios y buenas prácticas.** DRY, *Tell, Don't Ask* / evitar *Feature Envy* (los controladores ya no leen `role` para decidir), alta cohesión del modelo `User`, bajo acoplamiento de los controladores con cadenas literales.

**Justificación técnica.** El control de acceso es un requisito de seguridad. Si la regla vive en 27 lugares, cualquier cambio (por ejemplo, un rol nuevo de "coordinador") obliga a encontrarlos todos, y uno olvidado deja una pantalla abierta o bloqueada. Con un solo punto de definición el cambio se hace una vez y se prueba una vez.

**Beneficios obtenidos.** 27 comparaciones → 0 en controladores. Las 12 pruebas de acceso (CP-10 y CP-11, sobre 6 pantallas) aprueban antes y después, lo que confirma que no cambió el comportamiento. La prueba unitaria CP-13 valida la matriz de roles completa sin base de datos.

---

## Mejora 4 – Eliminación de los cuatro bloques duplicados

**Descripción del problema.** PHPCPD encontró 4 clones (63 líneas): los filtros de partidos copiados entre la vista pública y la administrativa, el estado del partido copiado entre el feed JSON y el stream SSE, la consulta de equipos disponibles copiada entre dos pantallas de *scouting*, y la creación de partidos de llave repetida en `BracketService`, cuyo método principal tenía 178 líneas.

**Rutas de los archivos intervenidos.** `src/app/Models/v1/MatchGame.php`, `src/app/Http/Controllers/v1/MatchController.php`, `src/app/Http/Controllers/v1/MatchDayController.php`, `src/app/Http/Controllers/v1/ScoutingController.php`, `src/app/Services/v1/BracketService.php`.

### 4.a Filtros de partidos (`MatchController` → `MatchGame::scopeFilter`)

**Antes** (repetido en `index()` y `adminIndex()`):

```php
$query
    ->when($filters['search'] ?? null, function ($query, $search): void {
        $query->where(function ($query) use ($search): void {
            $query->whereHas('tournament', fn ($t) => $t->where('name', 'like', "%{$search}%"))
                ->orWhereHas('homeTeam', fn ($t) => $t->where('name', 'like', "%{$search}%"))
                ->orWhereHas('awayTeam', fn ($t) => $t->where('name', 'like', "%{$search}%"));
        });
    })
    ->when($filters['sport_type'] ?? null, fn ($query, $sport) => /* ... */)
    ->when($filters['status'] ?? null, /* ... */)
    ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('match_date', '>=', $date))
    ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('match_date', '<=', $date));
```

**Después:**

```php
// MatchController::index() y MatchController::adminIndex()
$query->filter($filters);
$liveMatchesCount = (clone $baseCountQuery)->live()->count();

// src/app/Models/v1/MatchGame.php
public function scopeFilter(Builder $query, array $filters): Builder
{
    $byName = fn (string $search) => fn (Builder $related) => $related->where('name', 'like', "%{$search}%");

    return $query
        ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(
            fn (Builder $query) => $query->whereHas('tournament', $byName($search))
                ->orWhereHas('homeTeam', $byName($search))
                ->orWhereHas('awayTeam', $byName($search))
        ))
        // ... modalidad, estado (incluye 'live' mediante scopeLive) y rango de fechas
}

public function scopeLive(Builder $query): Builder
{
    return $query->where(fn (Builder $q) => $q->where('is_timer_running', true)->orWhere('status', 'live'));
}
```

### 4.b Estado del partido en vivo (`MatchDayController::matchStatePayload`)

**Antes** (mismo arreglo de 18 líneas en `liveFeed()` y en `stream()`):

```php
return response()->json([
    'match_id' => $match->id,
    'home_team' => $match->homeTeam->name,
    'away_team' => $match->awayTeam->name,
    'home_score' => $match->home_score,
    // ... 6 claves más y el map de eventos de 11 líneas
]);
// ...y en stream():
$data = [ 'match_id' => $match->id, 'home_score' => $match->home_score, /* ...copia... */ ];
```

**Después:**

```php
public function liveFeed(MatchGame $match): JsonResponse
{
    // ...
    return response()->json([
        ...$this->matchStatePayload($match),
        'home_team' => $match->homeTeam->name,
        'away_team' => $match->awayTeam->name,
    ]);
}

// en stream():
$data = $this->matchStatePayload($match);

private function matchStatePayload(MatchGame $match): array { /* única definición */ }
```

### 4.c Equipos disponibles y reglas del radar (`ScoutingController`)

**Antes** (en `adminIndex()` y `adminShow()`; las reglas de validación también estaban copiadas en `index()` y `adminIndex()`):

```php
$user = auth()->user();
$availableTeams = collect();
if ($user) {
    if ($user->isSuperAdmin()) {
        $availableTeams = Team::orderBy('name')->get();
    } elseif ($user->isAdmin()) {
        $adminTournamentIds = $user->managedTournaments()->pluck('id');
        $availableTeams = Team::whereIn('tournament_id', $adminTournamentIds)->orderBy('name')->get();
    } elseif ($user->isCoach() || $user->isCaptain()) {
        $availableTeams = $user->managedTeams()->get();
    }
}
```

**Después:**

```php
private const FILTER_RULES = [ /* position, preferred_foot, min_rating, search, sort_by */ ];

$filters = $request->validate(self::FILTER_RULES);
$availableTeams = $this->availableTeamsFor(auth()->user());

private function availableTeamsFor(?User $user): Collection
{
    return match (true) {
        $user === null => collect(),
        $user->isSuperAdmin() => Team::orderBy('name')->get(),
        $user->isAdmin() => Team::whereIn('tournament_id', $user->managedTournaments()->pluck('id'))
            ->orderBy('name')
            ->get(),
        $user->isDT() => $user->managedTeams()->get(),
        default => collect(),
    };
}
```

### 4.d Llaves eliminatorias (`BracketService`)

**Antes** (7 bloques casi iguales de `MatchGame::create([...])` en un método de 178 líneas):

```php
$sf1 = MatchGame::create([
    'tournament_id' => $tournament->id,
    'stage' => 'semifinals',
    'bracket_position' => 'SF1',
    'match_date' => $semisDate,
    'status' => 'scheduled',
    'next_match_id' => $final->id,
    'next_match_slot' => 'home',
    'home_team_id' => null,
    'away_team_id' => null,
]);
$sf2 = MatchGame::create([ /* mismo bloque con 'SF2' y 'away' */ ]);
$qf1 = MatchGame::create([ /* ... */ 'home_team_id' => $qualifiedTeams[0]->id, 'away_team_id' => $qualifiedTeams[7]->id ]);
// ... qf2, qf3, qf4 y la rama de 4 equipos repite final/sf1/sf2
```

**Después:**

```php
$createdMatches = $bracketSize >= 8
    ? $this->createQuarterfinalBracket($tournament, $qualifiedTeams, $startDate, $daysBetweenStages)
    : $this->createSemifinalBracket($tournament, $qualifiedTeams, $startDate, $daysBetweenStages);

private function createQuarterfinalBracket(/* ... */): array
{
    $finalDate = $startDate->copy()->addDays($daysBetweenStages * 2);
    $final = $this->createKnockoutMatch($tournament, 'final', 'FINAL', $finalDate);
    $semisDate = $startDate->copy()->addDays($daysBetweenStages);
    $sf1 = $this->createKnockoutMatch($tournament, 'semifinals', 'SF1', $semisDate, [$final, 'home']);
    $sf2 = $this->createKnockoutMatch($tournament, 'semifinals', 'SF2', $semisDate, [$final, 'away']);

    $quarters = collect([
        ['QF1', $startDate->copy(), [$sf1, 'home'], [$teams[0], $teams[7]]],
        ['QF2', $startDate->copy()->addHours(2), [$sf1, 'away'], [$teams[3], $teams[4]]],
        ['QF3', $startDate->copy()->addDays(1), [$sf2, 'home'], [$teams[1], $teams[6]]],
        ['QF4', $startDate->copy()->addDays(1)->addHours(2), [$sf2, 'away'], [$teams[2], $teams[5]]],
    ])->map(fn (array $qf) => $this->createKnockoutMatch($tournament, 'quarterfinals', ...$qf));
    // ...
}

private function createKnockoutMatch(
    Tournament $tournament, string $stage, string $position, Carbon $date,
    ?array $advancesTo = null, ?array $teams = null,
): MatchGame { /* único MatchGame::create */ }
```

**Técnica de refactorización aplicada.** Extracción de métodos, *query scope* de Eloquent (reutilización en el modelo), método fábrica (`createKnockoutMatch`), datos en lugar de código repetido (tabla de emparejamientos), simplificación de condicionales (`if/elseif` → `match`).

**Principios y buenas prácticas.** DRY, SRP, alta cohesión (los filtros de partidos viven en el modelo `MatchGame`), KISS.

**Justificación técnica.** En los cuatro casos las copias tenían que evolucionar juntas: si se corrige el filtro de fechas, el formato del feed o una regla de emparejamiento solo en una copia, el sistema se comporta distinto según la pantalla. Con una única definición, cualquier cambio queda aplicado en ambos lados.

**Beneficios obtenidos.** Duplicidad 0,74 % → 0 %. `BracketService::transitionFromStandings()` 178 → 43 líneas (ELOC de la clase 162 → 87). `MatchController` −31 LOC. Las pruebas CP-12, CP-14, CP-15 y CP-16, más las pruebas existentes de llaves (4 y 8 equipos), aprueban antes y después.

---

## Mejora 5 – Registro de equipos con responsabilidad única y sin vulnerabilidades

**Descripción del problema.** `TeamController::store()` era el método más complejo del sistema (CCN 18, NPath 448, 100 líneas): validaba, autorizaba, creaba el equipo, buscaba o creaba la cuenta del DT, cambiaba su rol, emitía invitaciones y redirigía. En ese código había dos vulnerabilidades:
- **Open Redirect (CWE-601):** `redirect($data['redirect_to'])` aceptaba cualquier URL, incluidas externas. El mismo patrón estaba en `PlayerProfileController` (2 veces).
- **Credencial predecible (CWE-1393):** sin clave, la cuenta del DT quedaba con `'password'`, y tres formularios traían ese valor ya escrito.

**Rutas de los archivos intervenidos.**
`src/app/Http/Controllers/v1/TeamController.php` · `src/app/Services/v1/TeamDirectorAssignmentService.php` (nuevo) · `src/app/Http/Controllers/Controller.php` · `src/app/Http/Controllers/v1/PlayerProfileController.php` · `src/resources/views/v1/teams/create.blade.php` · `src/resources/views/v1/teams/index.blade.php` · `src/resources/views/v1/tournaments/show.blade.php`

**Antes:**

```php
// TeamController::store()  (fragmento; 100 líneas en total)
'coach_password' => ['nullable', 'string', 'min:4', 'max:100'],
'redirect_to' => ['nullable', 'string'],
// ...
if (($data['dt_mode'] ?? 'new') === 'existing' && ! empty($data['existing_dt_id'])) {
    $dtUser = User::find($data['existing_dt_id']);
    if ($dtUser) { /* asignar */ }
} elseif (($data['dt_mode'] ?? null) === 'new' && ! empty($data['coach_name']) && ! empty($data['coach_email'])) {
    $coachEmail = strtolower(trim($data['coach_email']));
    $dtUser = User::where('email', $coachEmail)->first();
    $rawPassword = ! empty($data['coach_password']) ? $data['coach_password'] : 'password';   // CWE-1393
    if (! $dtUser) {
        $dtUser = User::create([ /* ... */ 'password' => Hash::make($rawPassword), 'role' => 'captain' ]);
        // ...
    } else {
        if (! in_array($dtUser->role, ['super_admin', 'admin'], true)) { $dtUser->update(['role' => 'captain']); }
    }
    // ... TeamInvitation::createForTeam(...)
} elseif (! empty($data['coach_name']) || ! empty($data['coach_email'])) {
    // ... TeamInvitation::createForTeam(...)
}

if (! empty($data['redirect_to'])) {
    return redirect($data['redirect_to'])->with('status', $successMsg);                       // CWE-601
}
```

```html
<!-- teams/create.blade.php, teams/index.blade.php, tournaments/show.blade.php -->
<input type="text" name="coach_password" value="password" placeholder="Mínimo 6 caracteres" ...>
<span class="text-[10px] text-slate-500">Predeterminada: password</span>
```

**Después:**

```php
// TeamController::store()  (CCN 4)
public function store(Request $request, TeamDirectorAssignmentService $directors): RedirectResponse
{
    $user = $request->user();
    abort_unless($user->canRegisterTeams(), 403);
    abort_if($user->isAdmin() && ! $user->hasManagedTournaments(), 403, 'No tienes torneos asignados para registrar equipos.');

    $data = $request->validate([
        // ...
        'coach_password' => ['nullable', 'string', 'min:8', 'max:100'],
        'redirect_to' => ['nullable', 'string', 'max:2048'],
    ]);

    $tournament = Tournament::findOrFail($data['tournament_id']);
    abort_if($user->isAdmin() && $tournament->admin_id !== $user->id, 403, 'No tienes autorización para inscribir equipos en este torneo.');

    $team = Team::create([ /* ... */ 'status' => $user->isStaff() ? 'approved' : 'pending' ]);
    $dtDetails = $directors->assign($team, $user, $data);

    return $this->redirectToInternalOr($data['redirect_to'] ?? null, route('teams.show', $team))
        ->with('status', "Equipo '{$team->name}' inscrito exitosamente en {$tournament->name}.{$dtDetails}");
}
```

```php
// src/app/Services/v1/TeamDirectorAssignmentService.php
public function assign(Team $team, User $invitedBy, array $data): string
{
    // Sin dt_mode explícito solo se emite la invitación (comportamiento original del formulario).
    $mode = $data['dt_mode'] ?? null;

    if ($mode === 'existing' && ! empty($data['existing_dt_id'])) {
        return $this->assignExisting($team, (int) $data['existing_dt_id']);
    }
    if ($mode === 'new' && ! empty($data['coach_name']) && ! empty($data['coach_email'])) {
        return $this->assignNew($team, $invitedBy, $data);
    }
    if (! empty($data['coach_name']) || ! empty($data['coach_email'])) {
        $this->invite(/* ... */);
    }

    return '';
}

// en assignNew(): clave aleatoria en lugar de 'password'
$password = ($data['coach_password'] ?? null)
    ?: Str::password(self::GENERATED_PASSWORD_LENGTH, symbols: false);
```

```php
// src/app/Http/Controllers/Controller.php  (reutilizado por TeamController y PlayerProfileController)
protected function redirectToInternalOr(?string $target, string $fallback): RedirectResponse
{
    return redirect($this->isInternalUrl($target) ? $target : $fallback);
}

private function isInternalUrl(?string $target): bool
{
    if (blank($target) || str_starts_with($target, '//') || str_contains($target, '\\')) {
        return false;
    }
    if (str_starts_with($target, '/')) {
        return true;
    }
    $appHost = parse_url(url('/'), PHP_URL_HOST);

    return in_array(parse_url($target, PHP_URL_SCHEME), ['http', 'https'], true)
        && parse_url($target, PHP_URL_HOST) === $appHost;
}
```

```html
<input type="text" name="coach_password" value="" placeholder="Mínimo 8 caracteres" autocomplete="new-password" ...>
<span class="text-[10px] text-slate-500">Si se deja vacía se genera una clave aleatoria segura</span>
```

**Técnica de refactorización aplicada.** Extracción de clase (servicio de dominio inyectado por el contenedor de Laravel), extracción de método en la clase base (`redirectToInternalOr`), simplificación de condicionales anidados con cláusulas de guarda y `abort_if`.

**Principios y buenas prácticas.** SRP (el controlador orquesta la petición HTTP; el servicio decide cómo vincular al DT), inyección de dependencias (bajo acoplamiento, servicio sustituible en pruebas), DRY (la redirección segura se escribe una vez y la usan tres acciones), seguridad por defecto (validación de lista blanca del host, sin credenciales predeterminadas).

**Justificación técnica.** El método mezclaba responsabilidades de distinto nivel, y precisamente esa mezcla escondía las dos vulnerabilidades. Al separar el flujo del DT en un servicio, la regla de la clave quedó en un solo lugar y se pudo probar de forma aislada. Para la redirección se eligió validar el host contra el de la aplicación en lugar de eliminar `redirect_to`, porque es una función legítima de la interfaz (volver a la nómina o al torneo de origen).

**Beneficios obtenidos.**
- `TeamController::store()`: CCN 18 → 4, 100 → 46 líneas; WMC de `TeamController` 49 → 34.
- Vulnerabilidades corregidas: CP-06, CP-07 y CP-09 fallaban antes y ahora aprueban; CP-08 confirma que las redirecciones internas siguen funcionando.
- Las pruebas de regresión existentes detectaron durante la refactorización un cambio de comportamiento involuntario (al omitir `dt_mode` se creaba la cuenta del DT en lugar de solo invitar), que se corrigió antes del commit.

---

## 6. Mejoras complementarias (mantenimiento preventivo y adaptativo)

| Commit | Cambio | Archivo |
|---|---|---|
| `db4b9d4` | Estilo Laravel Pint aplicado (7 archivos) | `src/app`, `src/routes` |
| `3b1d2be` | `withoutVite()` en la clase base de pruebas: la suite ya no depende de `public/build` | `src/tests/TestCase.php` |
| `c10287e` | `npm audit fix`: 4 vulnerabilidades → 0 (build de Vite verificado) | `src/package-lock.json` |
| `864bfda` | Formato PSR-12 del código refactorizado | `src/app` |
| `1c5a109` | CI con PHP 8.5 (igual que Docker) y `pdo_sqlite` | `.github/workflows/ci.yml` |


## 7. Registro en Git

```text
002e25f chore(calidad): agregar script de analisis estatico y evidencias de la evaluacion inicial
a037744 test(calidad): agregar pruebas de regresion para posiciones, registro de equipos y acceso administrativo
9be5fbc refactor(standings): aplicar reglamento del torneo y extraer metodos en StandingService
54e7f99 fix(seeders): actualizar import de StandingService al namespace v1
00af358 refactor(cleanup): eliminar codigo muerto detectado por PHPMD
e3dec29 refactor(auth): centralizar verificacion de roles en el modelo User
c67f374 refactor(dry): eliminar los 4 bloques de codigo duplicado detectados por PHPCPD
b6f2b7e refactor(teams): extraer TeamDirectorAssignmentService y corregir vulnerabilidades del registro
db4b9d4 style(pint): aplicar el estandar de codigo Laravel Pint en app y routes
3b1d2be test(ci): desacoplar la suite de pruebas del build de Vite
c10287e fix(deps): actualizar dependencias npm vulnerables (vite, shell-quote, source-map-js)
864bfda style(psr12): ajustar formato del codigo refactorizado a PSR-12
1c5a109 ci: alinear la version de PHP del pipeline con Docker y composer.lock
```

El historial completo, con los commits de documentación, está en [`docs/evidencias/10-git-log.txt`](evidencias/10-git-log.txt).
