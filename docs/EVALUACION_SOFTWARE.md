# EVALUACIÓN DEL SOFTWARE – SIGET

**GUÍA PRÁCTICA N.º 11 – EVALUACIÓN, MÉTRICAS, CALIDAD Y MANTENIMIENTO DEL SOFTWARE**
**SIGET-SF – Sistema Integral de Gestión de Torneos Deportivos**

| | |
|---|---|
| **Integrantes** | Néstor Iván Granados Valenzuela · Nelly Fabiola Cano Oviedo |
| **Institución** | Fundación de Estudios Superiores Comfanorte (FESC) |
| **Programa** | Ingeniería de Software – V Semestre |
| **Asignatura** | Arquitectura y Diseño del Software – Unidad 3: Métricas del Software |
| **Docente** | Ing. Robinson Damián Gómez Sánchez |
| **Fecha** | Noviembre de 2026 |

> Documentos relacionados: [METRICAS_SOFTWARE.md](METRICAS_SOFTWARE.md) · [REFACTORIZACION.md](REFACTORIZACION.md) · [PRUEBAS_SOFTWARE.md](PRUEBAS_SOFTWARE.md) · [MANTENIMIENTO.md](MANTENIMIENTO.md)

---

## 1. Descripción del proceso de evaluación

La evaluación se realizó sobre el código fuente real de SIGET (Laravel 13, PHP), sin crear un proyecto independiente. Se siguió este proceso:

1. **Delimitación del alcance.** Se analizó solo el código escrito por el equipo: `src/app` (controladores, modelos, servicios, middleware, requests) y `src/routes`. Se excluyeron `vendor/`, `node_modules/`, `public/build/`, `storage/`, `bootstrap/cache/`, las vistas Blade compiladas y los archivos generados automáticamente.
2. **Línea base de pruebas.** Antes de tocar el código se ejecutó la suite existente (76 pruebas) para saber desde qué estado se partía.
3. **Análisis estático inicial.** Se ejecutaron las herramientas del numeral 2 con el script reproducible [`scripts/analisis-calidad.sh`](../scripts/analisis-calidad.sh). Las salidas quedaron en [`docs/evidencias/antes/`](evidencias/antes/).
4. **Revisión manual dirigida.** Se revisaron los archivos señalados por las herramientas (métodos con mayor complejidad, clones y avisos de PHPMD) y se buscaron patrones de riesgo de seguridad.
5. **Priorización.** Se eligieron cinco problemas reales por impacto (defectos de reglas de negocio y de seguridad primero) y por facilidad de verificación con pruebas.
6. **Pruebas de regresión antes de refactorizar.** Se escribieron pruebas automatizadas sobre los componentes que se iban a intervenir y se ejecutaron sobre el código original (commit `a037744`).
7. **Refactorización** (ver [REFACTORIZACION.md](REFACTORIZACION.md)) y **reevaluación** con las mismas herramientas, configuración y alcance ([`docs/evidencias/despues/`](evidencias/despues/)).

## 2. Herramientas utilizadas

PHPStan no está en los repositorios de Ubuntu y Packagist no fue accesible desde el entorno de análisis, así que se usaron herramientas equivalentes instalables con `apt`/`pip` que cubren todos los indicadores pedidos por la guía.

| Herramienta | Versión | Indicador que aporta |
|---|---|---|
| **cloc** | 1.98 | Líneas de código, comentarios, líneas en blanco y cantidad de archivos |
| **Lizard** | 1.26.1 | Complejidad ciclomática (CCN) y tamaño (NLOC) por método |
| **PHPCPD** | 6.0.3 | Bloques duplicados y porcentaje de duplicidad |
| **PHPMD** | 2.15.0 | Problemas de diseño (`codesize`, `design`, `unusedcode`) y de calidad (`cleancode`, `naming`) |
| **PHP_CodeSniffer** | 3.7.2 | Incumplimiento del estándar **PSR-12** |
| **Laravel Pint** | 1.27 (en `vendor/`) | Cumplimiento del estilo Laravel adoptado por el proyecto |
| **PDepend** | 2.16.2 | Acoplamiento (CBO, Ca, Ce), WMC y tamaño por clase |
| **npm audit** | npm 10 | Vulnerabilidades conocidas en dependencias JavaScript |
| **grep (patrones de seguridad)** | – | Salida sin escapar en Blade, SQL crudo, `$guarded = []`, secretos en el código |
| **PHPUnit** | 12.5 | Pruebas de regresión antes y después |

## 3. Configuración del análisis

| Parámetro | Valor |
|---|---|
| Alcance | `src/app` y `src/routes` (80 archivos PHP) |
| Umbral de complejidad (Lizard) | CCN > 10 o NLOC > 60 por método se reporta como advertencia (`-C 10 -L 60`) |
| Umbrales PHPMD | Valores por defecto: CCN 10, NPath 200, método 100 líneas, 10 métodos públicos, CBO 13 |
| Duplicidad (PHPCPD) | Mínimo 5 líneas y 50 tokens iguales (`--min-lines=5 --min-tokens=50`) |
| Estándar de codificación | PSR-12 (PHPCS) y preset Laravel (Pint, en modo verificación `--test`) |
| Entorno | Ubuntu 24.04, PHP 8.3 CLI, base de datos SQLite en memoria para las pruebas (`phpunit.xml`) |

## 4. Comandos ejecutados

```bash
# Instalación de herramientas (Ubuntu 24.04)
sudo apt install cloc phpmd phpcpd php-codesniffer pdepend
pip install lizard

# Evaluación inicial y final (mismo script, misma configuración)
bash scripts/analisis-calidad.sh antes
bash scripts/analisis-calidad.sh despues

# Comandos que ejecuta el script (desde src/)
cloc app routes --quiet
lizard app -l php -w -C 10 -L 60
phpcpd --min-lines=5 --min-tokens=50 app
phpmd app text codesize,design,unusedcode
phpmd app text cleancode,naming
phpcs --standard=PSR12 --report=summary app routes
vendor/bin/pint --test app routes
pdepend --summary-xml=docs/evidencias/<etapa>/07-pdepend-summary.xml app
npm audit --omit=optional

# Pruebas
cd src && vendor/bin/phpunit --testdox --log-junit ../docs/evidencias/<etapa>/09-phpunit-junit.xml
```

## 5. Resultados de la evaluación inicial

| Indicador | Resultado inicial | Herramienta / evidencia |
|---|---|---|
| Líneas de código (sin comentarios ni blancos) | **6 909** | cloc – [`01-cloc.txt`](evidencias/antes/01-cloc.txt) |
| Archivos de código fuente | **80** | cloc |
| Complejidad ciclomática promedio por método | **2,62** (380 métodos) | Lizard – [`02-lizard-metodos.csv`](evidencias/antes/02-lizard-metodos.csv) |
| Complejidad ciclomática máxima | **18** (`TeamController::store`) | Lizard |
| Métodos con complejidad alta (CCN 11–20) | **14** | Lizard |
| Métodos con advertencia (CCN > 10 o > 60 líneas) | **25** | [`02-lizard-advertencias.txt`](evidencias/antes/02-lizard-advertencias.txt) |
| Duplicidad | **0,74 %** – 4 clones, 63 líneas | PHPCPD – [`03-phpcpd.txt`](evidencias/antes/03-phpcpd.txt) |
| Problemas de diseño | **43** | PHPMD – [`04-phpmd-diseno.txt`](evidencias/antes/04-phpmd-diseno.txt) |
| Problemas de calidad (cleancode/naming) | **173** | PHPMD – [`04-phpmd-calidad.txt`](evidencias/antes/04-phpmd-calidad.txt) |
| Incumplimiento PSR-12 | **132 errores y 166 advertencias** en 43 archivos | PHPCS – [`05-phpcs-psr12.txt`](evidencias/antes/05-phpcs-psr12.txt) |
| Archivos fuera del estilo Pint | **7** | Pint – [`06-pint.txt`](evidencias/antes/06-pint.txt) |
| Vulnerabilidades en dependencias | **4** (2 críticas, 2 altas) | npm audit – [`08-npm-audit.txt`](evidencias/antes/08-npm-audit.txt) |
| Vulnerabilidades en código propio | **4 puntos**: 3 redirecciones abiertas y 1 clave predeterminada | Revisión manual + pruebas CP-06, CP-07 y CP-09 |
| Deuda técnica estimada | **≈ 50,9 horas** (índice 1,47 %) | Modelo de estimación – ver [METRICAS_SOFTWARE.md](METRICAS_SOFTWARE.md#5-deuda-técnica-estimada) |
| Pruebas (suite original) | 76/76 con assets compilados; **52/76 sin assets** (24 fallan por `ViteManifestNotFoundException`) | PHPUnit |

## 6. Problemas identificados

Se registraron cinco problemas principales, que se corrigieron, y tres hallazgos adicionales, que también se atendieron.

### Problema 1 – Reglas de negocio ignoradas y números mágicos en la tabla de posiciones

| Campo | Detalle |
|---|---|
| **Archivo / componente** | `src/app/Services/StandingService.php` – clase `StandingService` |
| **Ubicación** | Método `recalculate()`, líneas 12–73; valores fijos en las líneas 47, 51, 56 y 57 |
| **Tipo** | Defecto funcional + *Magic Numbers* + *Long Method* (55 NLOC) + namespace inconsistente |
| **Causa** | Los puntos por victoria/empate/derrota estaban fijos (`+= 3`, `++`) aunque el sistema guarda un reglamento por torneo (`tournament_rules.points_for_win/draw/loss`) que el administrador puede editar. Además, la clase estaba en `App\Services` y no en `App\Services\v1` como el resto. |
| **Impacto** | **Adecuación funcional y fiabilidad:** la tabla oficial queda mal cuando un torneo usa otra puntuación (la prueba CP-03 lo demostró: 3 puntos en lugar de 2). **Mantenibilidad:** un método largo que mezcla cálculo, acumulación y persistencia. |
| **Solución** | Leer la puntuación desde `TournamentRule` (con valores por defecto en una constante), extraer métodos (`pointsFor`, `emptyRow`, `applyMatch`, `addResult`, `persist`) y mover la clase a `App\Services\v1`. |

### Problema 2 – Código muerto

| Campo | Detalle |
|---|---|
| **Archivos / componentes** | `src/app/Services/v1/StandingsService.php` (clase completa); `PlayerProfileController.php` línea 64; `SportsChronicleService.php` línea 106; `AuditController.php` línea 13; `ScoutingController.php` línea 167; `StandingsController.php` línea 40 |
| **Tipo** | *Dead Code* (YAGNI) – reportado por PHPMD `unusedcode` |
| **Causa** | `StandingsService::calculate()` solo devolvía `[]` y nadie lo usaba, pero su nombre casi idéntico a `StandingService` confundía. Había variables asignadas sin uso (`$medical`, `$scorer`) y parámetros `$request` sin uso. |
| **Impacto** | **Mantenibilidad / comprensibilidad:** dos servicios de "posiciones" con nombres casi iguales llevan a usar el equivocado; el código sin uso aumenta el esfuerzo de lectura. |
| **Solución** | Eliminar la clase stub, las variables y los parámetros sin uso. |

### Problema 3 – Verificación de roles duplicada en controladores

| Campo | Detalle |
|---|---|
| **Archivos / componentes** | 8 controladores: `AuditController`, `MatchController`, `PlayerController`, `RefereeController`, `StandingsController`, `TeamController`, `TournamentController`, `VenueController` |
| **Ubicación** | 23 apariciones de `in_array(...->role, ['super_admin', 'admin'], true)` (p. ej. `VenueController.php` líneas 15, 28, 39, 68, 77, 84, 106) y 4 variantes con `captain/coach` |
| **Tipo** | *Duplicate Code* / *Shotgun Surgery* – violación de DRY |
| **Causa** | Cada acción repetía la lista de roles en lugar de preguntarle al modelo `User`. En `User`, `isCaptain()`, `isCoach()` e `isDT()` tenían el mismo cuerpo copiado. |
| **Impacto** | **Seguridad y mantenibilidad:** agregar o cambiar un rol obliga a editar 27 lugares, y olvidar uno abre o cierra el acceso por error. **Acoplamiento:** los controladores dependen de cadenas literales de rol. |
| **Solución** | Constantes de rol y métodos `User::isStaff()` y `User::canRegisterTeams()`; reemplazar las 27 comparaciones; `isCaptain()`/`isCoach()` delegan en `isDT()`. |

### Problema 4 – Código duplicado detectado por PHPCPD

| Campo | Detalle |
|---|---|
| **Archivos / ubicación** | `MatchController.php` 50–60 ≡ 116–126 (filtros de partidos) · `MatchDayController.php` 178–196 ≡ 217–235 (estado del partido para feed JSON y SSE) · `ScoutingController.php` 59–73 ≡ 103–117 (equipos disponibles) · `BracketService.php` 64–85 ≡ 157–178 (creación de partidos de llave) |
| **Tipo** | *Duplicate Code* + *Long Method* (`BracketService::transitionFromStandings`, 178 líneas) |
| **Causa** | Se copió y pegó lógica entre la versión pública y la administrativa de cada pantalla, y entre ramas de la generación de llaves. |
| **Impacto** | **Mantenibilidad y fiabilidad:** un cambio en un filtro o en el formato del feed en vivo debe repetirse a mano; si se olvida, la vista pública y la administrativa se comportan distinto. |
| **Solución** | `MatchGame::scopeFilter()` y `scopeLive()`; `MatchDayController::matchStatePayload()`; `ScoutingController::FILTER_RULES` y `availableTeamsFor()`; `BracketService::createKnockoutMatch()` con métodos por tipo de llave. |

### Problema 5 – Método "Dios" en el registro de equipos con dos vulnerabilidades

| Campo | Detalle |
|---|---|
| **Archivos / componentes** | `src/app/Http/Controllers/v1/TeamController.php` (`store()`), `PlayerProfileController.php` (`updateMedical()`, `updateProfile()`), vistas `teams/create`, `teams/index`, `tournaments/show` |
| **Ubicación** | `TeamController::store()` líneas 143–242; clave fija en la línea 195; redirección en las líneas 237–238; `PlayerProfileController` líneas 73–74 y 107–108 |
| **Tipo** | *Long Method / God Method* (CCN 18, NPath 448, 100 líneas) + **Open Redirect (CWE-601)** + **credencial predeterminada predecible (CWE-1393)** |
| **Causa** | Un solo método validaba, autorizaba, creaba el equipo, creaba o buscaba la cuenta del DT, emitía invitaciones y redirigía. El parámetro `redirect_to` se usaba sin validar (`redirect($data['redirect_to'])`). Si no se escribía clave, la cuenta del DT quedaba con `'password'`, y los formularios ya traían ese valor escrito. |
| **Impacto** | **Seguridad:** un enlace manipulado puede llevar al usuario autenticado a un sitio de *phishing*; las cuentas de DT nuevas quedaban con una clave que cualquiera puede adivinar. **Mantenibilidad:** el método más complejo del sistema. |
| **Solución** | Extraer `TeamDirectorAssignmentService` (SRP), agregar `Controller::redirectToInternalOr()` que solo acepta rutas del mismo sitio, generar una clave aleatoria de 12 caracteres cuando no se define una, exigir mínimo 8 caracteres y quitar la clave precargada de los formularios. |

### Hallazgos adicionales atendidos

| # | Hallazgo | Archivo | Tipo | Acción |
|---|---|---|---|---|
| A1 | La suite fallaba (24 de 76) sin `public/build/manifest.json`, y el workflow de CI no compila assets | `tests/TestCase.php` | Fiabilidad de pruebas | `withoutVite()` en `TestCase::setUp()` |
| A2 | CI usaba PHP 8.3, pero `composer.lock` exige PHP ≥ 8.4.1 (Symfony 8) y Docker usa 8.5: `composer install` fallaba en CI | `.github/workflows/ci.yml` | Compatibilidad | PHP 8.5 + extensión `pdo_sqlite` |
| A3 | 4 vulnerabilidades npm (2 críticas en `shell-quote`, 2 altas en `vite` y `source-map-js`) | `src/package-lock.json` | Seguridad | `npm audit fix` (sin cambios de versión mayor) |
| A4 | 7 archivos fuera del estilo Pint | `app/`, `routes/` | Estándares | `vendor/bin/pint app routes` |

### Patrones de seguridad revisados y descartados (falsos positivos)

| Patrón | Ubicación | Conclusión |
|---|---|---|
| `{!! nl2br(e($match->chronicle_body)) !!}` | `public/matches/show.blade.php:345` | Seguro: el texto se escapa con `e()` antes de `nl2br`. |
| `{!! $qrSvg !!}` | `players/carnet-qr.blade.php:41` | Seguro: el SVG lo genera `QrCarnetService` en el servidor, no lo escribe el usuario. |
| `{!! $svgContent !!}` | `components/reicon.blade.php:16` | Seguro: íconos SVG locales del repositorio. |
| `DB::raw`, `orderByRaw`, `selectRaw` | `MatchMvpService`, `BracketService`, `WidgetController`, `PublicController` | Seguro: expresiones fijas sin datos del usuario concatenados. |
| `$guarded = []` y secretos en el código | – | Sin coincidencias. |

## 7. Archivos y componentes afectados

| Componente | Problemas | Tipo de componente |
|---|---|---|
| `app/Services/v1/StandingService.php` (antes `app/Services/StandingService.php`) | P1 | Servicio de lógica de negocio |
| `app/Services/v1/StandingsService.php` (eliminado) | P2 | Servicio |
| `app/Models/User.php` | P3 | Modelo |
| `app/Models/v1/MatchGame.php` | P4 | Modelo |
| `app/Http/Controllers/Controller.php` | P5 | Controlador base |
| `app/Http/Controllers/v1/TeamController.php` | P3, P5 | Controlador |
| `app/Services/v1/TeamDirectorAssignmentService.php` (nuevo) | P5 | Servicio |
| `app/Http/Controllers/v1/PlayerProfileController.php` | P2, P5 | Controlador |
| `app/Http/Controllers/v1/MatchController.php` | P3, P4 | Controlador |
| `app/Http/Controllers/v1/MatchDayController.php` | P4 | Controlador |
| `app/Http/Controllers/v1/ScoutingController.php` | P2, P4 | Controlador |
| `app/Services/v1/BracketService.php` | P4 | Servicio |
| `app/Services/v1/SportsChronicleService.php`, `PostMatchClosureService.php` | P1, P2 | Servicios |
| `AuditController`, `StandingsController`, `RefereeController`, `VenueController`, `PlayerController`, `TournamentController` | P2, P3 | Controladores |
| `resources/views/v1/teams/create`, `teams/index`, `tournaments/show` | P5 | Vistas |
| `database/seeders/TournamentCompleteSeeder.php` | P1 | Seeder |

## 8. Evidencias obtenidas

Todas las evidencias son archivos de texto generados por las herramientas y versionados en el repositorio:

| Evidencia | Evaluación inicial | Evaluación final |
|---|---|---|
| Líneas de código | [`antes/01-cloc.txt`](evidencias/antes/01-cloc.txt) | [`despues/01-cloc.txt`](evidencias/despues/01-cloc.txt) |
| Complejidad por método | [`antes/02-lizard-metodos.csv`](evidencias/antes/02-lizard-metodos.csv) | [`despues/02-lizard-metodos.csv`](evidencias/despues/02-lizard-metodos.csv) |
| Advertencias de complejidad | [`antes/02-lizard-advertencias.txt`](evidencias/antes/02-lizard-advertencias.txt) | [`despues/02-lizard-advertencias.txt`](evidencias/despues/02-lizard-advertencias.txt) |
| Duplicidad | [`antes/03-phpcpd.txt`](evidencias/antes/03-phpcpd.txt) | [`despues/03-phpcpd.txt`](evidencias/despues/03-phpcpd.txt) |
| Diseño (PHPMD) | [`antes/04-phpmd-diseno.txt`](evidencias/antes/04-phpmd-diseno.txt) | [`despues/04-phpmd-diseno.txt`](evidencias/despues/04-phpmd-diseno.txt) |
| Calidad (PHPMD) | [`antes/04-phpmd-calidad.txt`](evidencias/antes/04-phpmd-calidad.txt) | [`despues/04-phpmd-calidad.txt`](evidencias/despues/04-phpmd-calidad.txt) |
| PSR-12 | [`antes/05-phpcs-psr12.txt`](evidencias/antes/05-phpcs-psr12.txt) | [`despues/05-phpcs-psr12.txt`](evidencias/despues/05-phpcs-psr12.txt) |
| Pint | [`antes/06-pint.txt`](evidencias/antes/06-pint.txt) | [`despues/06-pint.txt`](evidencias/despues/06-pint.txt) |
| Acoplamiento (PDepend) | [`antes/07-pdepend-summary.xml`](evidencias/antes/07-pdepend-summary.xml) | [`despues/07-pdepend-summary.xml`](evidencias/despues/07-pdepend-summary.xml) |
| Seguridad | [`antes/08-seguridad-patrones.txt`](evidencias/antes/08-seguridad-patrones.txt), [`antes/08-npm-audit.txt`](evidencias/antes/08-npm-audit.txt) | [`despues/08-seguridad-patrones.txt`](evidencias/despues/08-seguridad-patrones.txt), [`despues/08-npm-audit.txt`](evidencias/despues/08-npm-audit.txt) |
| Pruebas | [`antes/09-phpunit-testdox.txt`](evidencias/antes/09-phpunit-testdox.txt) | [`despues/09-phpunit-testdox.txt`](evidencias/despues/09-phpunit-testdox.txt) |
| Consolidado | [`comparativo-metricas.json`](evidencias/comparativo-metricas.json), [`deuda-tecnica.json`](evidencias/deuda-tecnica.json) | |

Para reproducir la evaluación:

```bash
git checkout 002e25f      # estado inicial + script
bash scripts/analisis-calidad.sh antes
git checkout main
bash scripts/analisis-calidad.sh despues
```
