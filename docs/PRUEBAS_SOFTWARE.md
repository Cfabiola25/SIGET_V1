# PRUEBAS DEL SOFTWARE – SIGET

**GUÍA PRÁCTICA N.º 11 – EVALUACIÓN, MÉTRICAS, CALIDAD Y MANTENIMIENTO DEL SOFTWARE**
**SIGET-SF – Sistema Integral de Gestión de Torneos Deportivos**

| | |
|---|---|
| **Integrantes** | Néstor Iván Granados Valenzuela · Nelly Fabiola Cano Oviedo |
| **Institución** | Fundación de Estudios Superiores Comfanorte (FESC) |
| **Programa** | Ingeniería de Software – V Semestre |
| **Asignatura** | Arquitectura y Diseño del Software – Unidad 3: Métricas del Software |
| **Docente** | Ing. Robinson Damián Gómez Sánchez |

---

## 1. Herramientas de pruebas utilizadas

| Herramienta | Uso |
|---|---|
| **PHPUnit 12.5** | Motor de pruebas unitarias y de integración (`src/phpunit.xml`) |
| **Laravel Testing** (`Illuminate\Foundation\Testing`) | Peticiones HTTP simuladas (`get`, `post`, `put`, `getJson`), autenticación (`actingAs`), aserciones de respuesta, de vista y de base de datos |
| **RefreshDatabase + SQLite en memoria** | Base de datos limpia y aislada en cada prueba (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) |
| **Model Factories** (`database/factories`) | Datos de prueba: usuarios por rol, torneos, equipos, jugadores y partidos |
| **Data Providers** (`#[DataProvider]`) | Un mismo caso ejecutado sobre varias pantallas o roles |
| **JUnit XML / TestDox** | Reportes de ejecución guardados como evidencia |

## 2. Ubicación de las pruebas dentro del repositorio

```text
src/tests/
├── TestCase.php                                   ← clase base (withoutVite, mejora A1)
├── Unit/
│   ├── ReiconTest.php                             (existente)
│   └── UserRoleTest.php                           ← NUEVA – Mejora 3
└── Feature/
    ├── Calidad/                                   ← NUEVAS – Guía 11
    │   ├── StandingRecalculationTest.php          ← Mejora 1
    │   ├── TeamRegistrationSecurityTest.php       ← Mejora 5
    │   ├── StaffAuthorizationTest.php             ← Mejoras 3 y 4
    │   └── MatchLiveFeedAndScoutingTest.php       ← Mejora 4
    └── ... 9 archivos de pruebas existentes (76 pruebas)
```

## 3. Comandos de ejecución

```bash
cd src

# Suite completa
php artisan test
# o directamente con PHPUnit
vendor/bin/phpunit

# Solo las pruebas de la Guía 11
vendor/bin/phpunit tests/Feature/Calidad tests/Unit/UserRoleTest.php --testdox

# Evidencias (TestDox + JUnit XML)
vendor/bin/phpunit --testdox --log-junit ../docs/evidencias/despues/09-phpunit-junit.xml

# Ejecución sin assets compilados (como en CI)
mv public/build /tmp/build && vendor/bin/phpunit; mv /tmp/build public/build
```

## 4. Casos de prueba implementados

Se implementaron **16 casos de prueba** (CP-01 a CP-16) en 5 clases nuevas. Con los *data providers* suman **31 ejecuciones**. Todos están relacionados con los componentes intervenidos.

| ID | Prueba (clase :: método) | Componente / mejora | Qué se verifica | Resultado esperado |
|---|---|---|---|---|
| CP-01 | `StandingRecalculationTest::test_home_win_assigns_standard_points_and_goals` | `StandingService` (M1) | Funcionalidad + almacenamiento: una victoria local actualiza PJ, PG, PP, GF, GC y puntos | Local 3 pts, visitante 0 pts, goles 2-1 guardados en `standings` |
| CP-02 | `StandingRecalculationTest::test_draw_assigns_one_point_to_each_team` | `StandingService` (M1) | Regla de negocio de empate | 1 punto y 1 PE para cada equipo |
| CP-03 | `StandingRecalculationTest::test_recalculation_uses_points_configured_in_tournament_rules` | `StandingService` (M1) | **Regla de negocio**: puntuación del reglamento (2-1-1) | Ganador 2 pts, perdedor 1 pt |
| CP-04 | `StandingRecalculationTest::test_only_played_matches_are_counted` | `StandingService` (M1) | Validación: los partidos `scheduled` no cuentan; todos los equipos quedan en la tabla | El equipo sin partidos jugados queda en 0 |
| CP-05 | `TeamRegistrationSecurityTest::test_registering_team_with_new_coach_creates_linked_coach_account` | `TeamController`, `TeamDirectorAssignmentService` (M5) | Funcionalidad + almacenamiento: equipo aprobado, DT creado con rol `captain`, correo normalizado, clave dada, invitación emitida | Todos los registros creados y vinculados |
| CP-06 | `TeamRegistrationSecurityTest::test_new_coach_without_password_does_not_receive_predictable_password` | `TeamDirectorAssignmentService` (M5) | **Seguridad (CWE-1393)** | La clave del DT **no** es `password` |
| CP-07 | `TeamRegistrationSecurityTest::test_external_redirect_target_is_ignored_when_registering_team` | `Controller::redirectToInternalOr` (M5) | **Seguridad (CWE-601)**: `redirect_to=https://sitio-malicioso.example/...` | Redirige a `teams.show`, no al sitio externo |
| CP-08 | `TeamRegistrationSecurityTest::test_internal_redirect_target_is_preserved` | `Controller::redirectToInternalOr` (M5) | Regresión: las URL internas siguen funcionando | Redirige a la URL interna enviada |
| CP-09 | `TeamRegistrationSecurityTest::test_player_profile_update_ignores_external_redirect_target` | `PlayerProfileController` (M5) | **Seguridad (CWE-601)** con URL relativa al protocolo `//sitio-malicioso.example` | Redirige al cromo del jugador |
| CP-10 | `StaffAuthorizationTest::test_non_staff_user_is_forbidden_from_backoffice` (×6 pantallas) | `User::isStaff()` (M3) | Manejo de errores / control de acceso: un DT entra a equipos, partidos, posiciones, auditoría, sedes y árbitros | HTTP 403 en las 6 pantallas |
| CP-11 | `StaffAuthorizationTest::test_super_admin_can_access_backoffice` (×6 pantallas) | `User::isStaff()` (M3) | Funcionalidad: el super administrador entra a las mismas 6 pantallas | HTTP 200 en las 6 pantallas |
| CP-12 | `StaffAuthorizationTest::test_admin_match_list_applies_filters_within_own_tournaments` | `MatchGame::scopeFilter()` (M4) | Recuperación de datos: búsqueda "Tigres" + estado `played`, solo en torneos del administrador | Exactamente 1 partido (excluye el de otro torneo y el programado) |
| CP-13 | `UserRoleTest::test_role_permissions_are_resolved_from_a_single_source` (×6 roles) | `User` (M3) – prueba unitaria sin BD | Matriz de permisos `isStaff`, `canRegisterTeams`, `isDT`, `isCaptain`, `isCoach` | Valores esperados para super_admin, admin, captain, coach, referee y player |
| CP-14 | `MatchLiveFeedAndScoutingTest::test_live_feed_returns_match_state_with_events` | `MatchDayController::matchStatePayload()` (M4) | Integración: estructura del feed JSON en vivo | `match_id`, equipos, marcador 1-0 y evento `goal` con el nombre del jugador |
| CP-15 | `MatchLiveFeedAndScoutingTest::test_sse_stream_emits_same_match_state` | `MatchDayController::matchStatePayload()` (M4) | Integración: stream SSE (`text/event-stream`) | Emite `event: match_state` con el mismo estado del partido |
| CP-16 | `MatchLiveFeedAndScoutingTest::test_admin_scouting_lists_only_teams_from_managed_tournaments` | `ScoutingController::availableTeamsFor()` (M4) | Recuperación de datos con alcance por rol | Incluye el equipo propio y excluye el ajeno |

Además se ejecutaron en cada etapa las **76 pruebas existentes** del proyecto, como pruebas de regresión. Entre ellas están las que cubren las llaves de 4 y 8 equipos (`PhaseFiveFixturesAndBracketsDisruptiveTest`), el cierre de actas con recálculo de posiciones (`PhaseFourPostMatchDisruptiveTest`) y la creación de equipos con invitación (`PhaseOneSoccerDisruptiveTest`).

## 5. Resultados esperados frente a resultados obtenidos

| ID | Antes de la refactorización | Después de la refactorización | Observación |
|---|---|---|---|
| CP-01 | ✔ Aprobada | ✔ Aprobada | Comportamiento conservado |
| CP-02 | ✔ Aprobada | ✔ Aprobada | Comportamiento conservado |
| CP-03 | ✘ **Fallida** – se obtuvieron 3 pts (valor fijo) en lugar de 2 | ✔ Aprobada | Defecto de regla de negocio corregido (M1) |
| CP-04 | ✔ Aprobada | ✔ Aprobada | Comportamiento conservado |
| CP-05 | ✔ Aprobada | ✔ Aprobada | Comportamiento conservado tras extraer el servicio |
| CP-06 | ✘ **Fallida** – `Hash::check('password', …)` era verdadero | ✔ Aprobada | Vulnerabilidad CWE-1393 corregida (M5) |
| CP-07 | ✘ **Fallida** – redirigía a `https://sitio-malicioso.example/phishing` | ✔ Aprobada | Vulnerabilidad CWE-601 corregida (M5) |
| CP-08 | ✔ Aprobada | ✔ Aprobada | La función legítima `redirect_to` se mantiene |
| CP-09 | ✘ **Fallida** – redirigía a `//sitio-malicioso.example` | ✔ Aprobada | Vulnerabilidad CWE-601 corregida (M5) |
| CP-10 (×6) | ✔ 6/6 | ✔ 6/6 | Refactorización de roles sin cambio de comportamiento (M3) |
| CP-11 (×6) | ✔ 6/6 | ✔ 6/6 | Ídem |
| CP-12 | ✔ Aprobada | ✔ Aprobada | Filtros conservados tras moverlos al *scope* (M4) |
| CP-13 (×6) | N/A – `isStaff()` y `canRegisterTeams()` no existían | ✔ 6/6 | Prueba unitaria de la API creada en M3 |
| CP-14 | ✔ Aprobada* | ✔ Aprobada | *Ejecutada sobre el código anterior a M4 (`git stash`) |
| CP-15 | ✔ Aprobada* | ✔ Aprobada | Ídem |
| CP-16 | ✔ Aprobada* | ✔ Aprobada | Ídem |

> Las cuatro pruebas que fallaban antes se escribieron **a propósito** antes de corregir el código, para demostrar con evidencia que los defectos existían y que la refactorización los corrigió. Las demás aprueban antes y después, lo que confirma que la refactorización no cambió el comportamiento observable.

### Defecto detectado por la regresión durante la refactorización

Al extraer `TeamDirectorAssignmentService`, la prueba existente `PhaseOneSoccerDisruptiveTest::test_admin_creates_team_and_auto_generates_magic_link_invitation` **falló** (`Failed asserting that 3 is null`): el servicio tomaba `'new'` como modo por defecto y creaba la cuenta del DT cuando el formulario no enviaba `dt_mode`. El código original, en cambio, solo emitía la invitación. Se corrigió (`$mode = $data['dt_mode'] ?? null`) antes del commit, y la suite volvió a 107/107. Es justamente el caso para el que existen las pruebas de regresión.

## 6. Cantidad de pruebas ejecutadas, aprobadas y fallidas

| Ejecución | Commit | Pruebas | Aserciones | Aprobadas | Fallidas | Evidencia |
|---|---|---:|---:|---:|---:|---|
| Línea base – suite original (con assets compilados) | `f2c436f` | 76 | 527 | 76 | 0 | – |
| **Antes** – suite original + pruebas nuevas | `a037744` | **98** | 565 | **94** | **4** | [`antes/09-phpunit-testdox.txt`](evidencias/antes/09-phpunit-testdox.txt), [`antes/09-phpunit-junit.xml`](evidencias/antes/09-phpunit-junit.xml) |
| **Después** – suite completa | `main` | **107** | 618 | **107** | **0** | [`despues/09-phpunit-testdox.txt`](evidencias/despues/09-phpunit-testdox.txt), [`despues/09-phpunit-junit.xml`](evidencias/despues/09-phpunit-junit.xml) |

### Ejecución sin assets compilados (condiciones del pipeline de CI)

| Ejecución | Pruebas | Aprobadas | Fallidas | Causa | Evidencia |
|---|---:|---:|---:|---|---|
| `TestCase` original, sin `public/build` | 107 | 75 | 32 (24 originales + 8 nuevas) | `ViteManifestNotFoundException` al renderizar vistas | [`antes/09b-phpunit-sin-assets-vite.txt`](evidencias/antes/09b-phpunit-sin-assets-vite.txt) |
| `TestCase` con `withoutVite()`, sin `public/build` | 107 | 107 | 0 | – | [`despues/09b-phpunit-sin-assets-vite.txt`](evidencias/despues/09b-phpunit-sin-assets-vite.txt) |

## 7. Evidencias de ejecución

**Antes de la refactorización** (extracto de [`antes/09-phpunit-testdox.txt`](evidencias/antes/09-phpunit-testdox.txt)):

```text
Standing Recalculation (Tests\Feature\Calidad\StandingRecalculation)
 ✔ Home win assigns standard points and goals
 ✔ Draw assigns one point to each team
 ✘ Recalculation uses points configured in tournament rules
   │ Failed asserting that a row in the table [standings] matches the attributes {
   │     "points": 2 ...
   │ Found similar results: [ { ... "points": 3 } ...
 ✔ Only played matches are counted

Team Registration Security (Tests\Feature\Calidad\TeamRegistrationSecurity)
 ✔ Registering team with new coach creates linked coach account
 ✘ New coach without password does not receive predictable password
   │ Failed asserting that true is false.
 ✘ External redirect target is ignored when registering team
   │ -'http://localhost/teams/1'
   │ +'https://sitio-malicioso.example/phishing'
 ✔ Internal redirect target is preserved
 ✘ Player profile update ignores external redirect target
   │ -'http://localhost/players/1/cromo'
   │ +'//sitio-malicioso.example'

FAILURES!
Tests: 98, Assertions: 565, Failures: 4.
```

**Después de la refactorización** (extracto de [`despues/09-phpunit-testdox.txt`](evidencias/despues/09-phpunit-testdox.txt)):

```text
Match Live Feed And Scouting (Tests\Feature\Calidad\MatchLiveFeedAndScouting)
 ✔ Live feed returns match state with events
 ✔ Sse stream emits same match state
 ✔ Admin scouting lists only teams from managed tournaments
Staff Authorization (Tests\Feature\Calidad\StaffAuthorization)
 ✔ Non staff user is forbidden from backoffice with data set "equipos"   (… 6 pantallas)
 ✔ Super admin can access backoffice with data set "equipos"             (… 6 pantallas)
 ✔ Admin match list applies filters within own tournaments
Standing Recalculation (Tests\Feature\Calidad\StandingRecalculation)
 ✔ Home win assigns standard points and goals
 ✔ Draw assigns one point to each team
 ✔ Recalculation uses points configured in tournament rules
 ✔ Only played matches are counted
Team Registration Security (Tests\Feature\Calidad\TeamRegistrationSecurity)
 ✔ Registering team with new coach creates linked coach account
 ✔ New coach without password does not receive predictable password
 ✔ External redirect target is ignored when registering team
 ✔ Internal redirect target is preserved
 ✔ Player profile update ignores external redirect target
User Role (Tests\Unit\UserRole)
 ✔ Role permissions are resolved from a single source with data set "super_admin"  (… 6 roles)

OK (107 tests, 618 assertions)
```

**Ejecución por cada mejora.** Después de cada commit de refactorización se ejecutó la suite completa:

| Después del commit | Resultado | Pruebas que aún fallaban |
|---|---|---|
| `9be5fbc` Mejora 1 | 95/98 | CP-06, CP-07, CP-09 (pendientes de M5) |
| `00af358` Mejora 2 | 95/98 | Ídem |
| `e3dec29` Mejora 3 | 101/104 | Ídem (+6 de CP-13 aprobadas) |
| `c67f374` Mejora 4 | 104/107 | Ídem (+3 de CP-14 a CP-16 aprobadas) |
| `b6f2b7e` Mejora 5 | **107/107** | Ninguna |
