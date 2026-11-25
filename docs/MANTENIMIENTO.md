# MANTENIMIENTO DEL SOFTWARE – SIGET

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

## 1. Estado actual del software

| Aspecto | Estado tras la Guía 11 |
|---|---|
| **Funcionalidad** | Portal público, backoffice de super administrador y administrador, consola de partido (Match Day), portal del DT, *scouting*, llaves eliminatorias y cierre de actas, todos operativos. La tabla de posiciones aplica ahora el reglamento de cada torneo. |
| **Pruebas** | 107 pruebas automatizadas, 618 aserciones, 100 % aprobadas. La suite ya no depende de los assets compilados de Vite. |
| **Complejidad** | CCN promedio 2,51; CCN máxima 15; 12 métodos con complejidad alta (11–20) y ninguno con complejidad muy alta (> 20). |
| **Duplicidad** | 0 % (PHPCPD, mínimo 5 líneas / 50 tokens). |
| **Estándares** | 100 % conforme a Laravel Pint (estándar del proyecto). PSR-12: 129 errores / 164 advertencias, la mayoría por la convención de concatenación de Laravel y por longitud de línea. |
| **Seguridad** | 0 vulnerabilidades conocidas en dependencias npm. Corregidos *open redirect* (CWE-601) y credencial predeterminada (CWE-1393). Patrones de riesgo revisados ([EVALUACION_SOFTWARE.md](EVALUACION_SOFTWARE.md#patrones-de-seguridad-revisados-y-descartados-falsos-positivos)). |
| **Integración continua** | El workflow usa PHP 8.5, alineado con Docker y `composer.lock`. |
| **Deuda técnica estimada** | 37,1 horas (índice 1,08 %), frente a 50,9 horas al inicio. |

En conjunto, SIGET está en un estado **estable y mantenible**. La deuda que queda se concentra en un grupo pequeño de métodos largos con lógica de negocio compleja, que se detalla a continuación.

## 2. Deuda técnica pendiente

| ID | Deuda | Ubicación | Evidencia (herramienta) | Impacto |
|---|---|---|---|---|
| DT-01 | Método largo y muy ramificado: generación del fixture | `app/Services/v1/FixtureGeneratorService.php` – `preview()` | CCN 15, NPath 1 728, 124 líneas (PHPMD, Lizard) | Alto: cualquier regla nueva de calendario es difícil de agregar sin romper otra |
| DT-02 | Validación de restricciones de programación concentrada en un método | `app/Services/v1/ScheduleService.php` – `validateMatchConstraints()` | CCN 15 | Alto: núcleo de la programación de partidos |
| DT-03 | Generación de crónica con muchas combinaciones de condiciones | `app/Services/v1/SportsChronicleService.php` – `generateAlgorithmicChronicle()` | CCN 15, NPath 2 560 | Medio: funcionalidad de valor agregado, no crítica |
| DT-04 | Listado de jugadores con filtros y alcance por rol en el controlador | `app/Http/Controllers/v1/PlayerController.php` – `index()` y `store()` | CCN 14 y 11, NPath 1 152 y 216 | Medio-alto |
| DT-05 | Cierre de acta con varias responsabilidades (bloqueo, sanciones, MVP, crónica, posiciones) | `app/Services/v1/PostMatchClosureService.php` – `closeMatch()` | CCN 13, 125 líneas | Alto: proceso oficial e irreversible |
| DT-06 | Página de inicio pública con consultas en el controlador | `app/Http/Controllers/v1/PublicController.php` – `home()` | 124 líneas | Medio: rendimiento y legibilidad |
| DT-07 | Importación de nómina CSV con validación anidada | `app/Http/Controllers/v1/TeamPortalController.php` – `importRoster()` | CCN 13, NPath 268 | Medio |
| DT-08 | Programación manual de partidos con valores de respaldo "mágicos" | `app/Http/Controllers/v1/MatchController.php` – `storeSchedule()` | CCN 10; `?? Tournament::value('id') ?? 1` y elige la primera sede activa si no se indica | Medio: puede asignar un torneo o una sede inesperados |
| DT-09 | Controladores y modelos grandes | `MatchController` (13 métodos públicos), `ScoutingController` (11), `MatchGame` (19, incluye los dos *scopes* nuevos), `Player` (15) | PHPMD `TooManyPublicMethods`, `CouplingBetweenObjects` | Bajo-medio |
| DT-10 | Verificación de roles con literales en vistas y middleware | `CheckRole` y las vistas Blade siguen usando cadenas `'admin'`, `'super_admin'` | Revisión manual | Medio: la Mejora 3 cubrió solo los controladores |
| DT-11 | Diferencias con PSR-12 | 164 advertencias de longitud de línea; 120 de espaciado de concatenación | PHPCS | Bajo: estilo |
| DT-12 | Auditoría de dependencias PHP no ejecutada | `composer.lock` | `composer audit` necesita acceso a Packagist, que no estuvo disponible en el entorno de análisis | Desconocido hasta ejecutarlo |
| DT-13 | Sin análisis de tipos estático (PHPStan/Larastan) | Proyecto completo | No hay herramienta configurada en el repositorio | Medio: errores de tipos solo se detectan en ejecución |
| DT-14 | Sin medición de cobertura de pruebas | `phpunit.xml` / CI (`coverage: none`) | – | Medio: no se sabe qué porcentaje del código ejecutan las pruebas |

## 3. Problemas que requieren atención futura

1. **Cierre de actas (DT-05).** Es irreversible (`is_locked`) y hace cinco cosas en una sola transacción. Un error en la generación de la crónica o del MVP no debería impedir el cierre oficial. Conviene separar lo obligatorio (bloqueo, sanciones, posiciones) de lo complementario (MVP, crónica), y hacer lo complementario con eventos o *jobs*.
2. **Valores de respaldo en la programación manual (DT-08).** Si un equipo no tiene torneo, el partido se crea en el primer torneo de la base de datos o en el id `1`. Debe fallar con un error de validación en lugar de adivinar.
3. **Autorización dispersa en vistas (DT-10).** Las vistas deciden qué botones mostrar comparando cadenas de rol. Conviene pasar a *Policies/Gates* de Laravel (`@can`), que además permitirían eliminar las verificaciones manuales de los controladores.
4. **Seguridad de dependencias PHP (DT-12).** Ejecutar `composer audit` en un equipo con acceso a Packagist e incluirlo en el pipeline de CI.
5. **Rendimiento del portal público (DT-06).** La página de inicio arma varias consultas en el controlador. Con más torneos activos convendrá cachear los bloques públicos (tabla de posiciones, goleadores, próximos partidos).

## 4. Actividades de mantenimiento propuestas

| # | Actividad | Tipo de mantenimiento | Prioridad | Esfuerzo estimado | Deuda que atiende |
|---|---|---|---|---|---|
| M-01 | Ejecutar `composer audit` y agregarlo como paso del workflow de CI | Preventivo | **Alta** | 1 h | DT-12 |
| M-02 | Quitar los valores de respaldo de `storeSchedule()` y validar `venue_id` y `referee_id` con `exists`/`in:auto` | Correctivo | **Alta** | 2 h | DT-08 |
| M-03 | Dividir `PostMatchClosureService::closeMatch()`: bloqueo + sanciones + posiciones en la transacción; MVP y crónica como *listeners* de un evento `MatchClosed` | Perfectivo | **Alta** | 6 h | DT-05 |
| M-04 | Extraer las reglas de `ScheduleService::validateMatchConstraints()` a clases de regla independientes (patrón *Specification* / *Chain of Responsibility*) | Perfectivo | **Alta** | 6 h | DT-02 |
| M-05 | Reemplazar las verificaciones de rol de vistas y controladores por *Policies* de Laravel (`TournamentPolicy`, `TeamPolicy`, `MatchPolicy`) | Preventivo | Media | 8 h | DT-10, DT-09 |
| M-06 | Instalar Larastan (nivel 5) y agregarlo a CI | Preventivo | Media | 3 h + correcciones | DT-13 |
| M-07 | Activar cobertura (`pcov`) en CI y fijar un mínimo del 60 % para servicios | Preventivo | Media | 2 h | DT-14 |
| M-08 | Llevar los filtros de `PlayerController::index()` a un *query scope* (como `MatchGame::scopeFilter`) y extraer `store()` a un servicio | Perfectivo | Media | 4 h | DT-04 |
| M-09 | Dividir `FixtureGeneratorService::preview()` en generación (algoritmo de Berger) y asignación de sedes/fechas | Perfectivo | Media | 6 h | DT-01 |
| M-10 | Cachear los bloques del portal público con invalidación al cerrar un acta | Perfectivo | Media | 4 h | DT-06 |
| M-11 | Pasar `importRoster()` a una clase de importación con validación por fila y reporte de errores | Perfectivo | Baja | 4 h | DT-07 |
| M-12 | Reemplazar `generateAlgorithmicChronicle()` por plantillas seleccionadas desde una tabla de casos | Perfectivo | Baja | 3 h | DT-03 |
| M-13 | Actualizar Laravel, Symfony y paquetes npm de forma periódica (mensual), con la suite como red de seguridad | Adaptativo | Media (recurrente) | 2 h/mes | – |
| M-14 | Ejecutar `scripts/analisis-calidad.sh` en cada *pull request* y comparar con la línea base de `docs/evidencias/despues/` | Preventivo | Media | 2 h | Todas |
| M-15 | Ajustar las líneas de más de 120 caracteres en los archivos que se modifiquen (regla del *boy scout*) | Preventivo | Baja | Continuo | DT-11 |

### Actividades de mantenimiento ya ejecutadas en esta guía

| Actividad | Tipo | Commit |
|---|---|---|
| Puntos de la tabla según el reglamento del torneo | Correctivo | `9be5fbc` |
| Corrección de *open redirect* y clave predeterminada | Correctivo | `b6f2b7e` |
| Actualización de dependencias npm vulnerables | Correctivo / Adaptativo | `c10287e` |
| PHP 8.5 en CI, alineado con Docker y `composer.lock` | Adaptativo | `1c5a109` |
| Extracción de métodos, servicios y *scopes*; eliminación de duplicidad | Perfectivo | `00af358`, `e3dec29`, `c67f374`, `b6f2b7e` |
| Pruebas de regresión, suite independiente de Vite, estilo Pint y script de análisis | Preventivo | `a037744`, `3b1d2be`, `db4b9d4`, `002e25f` |

## 5. Priorización

La prioridad se asignó según **impacto en la operación del torneo × probabilidad de fallo**, y luego según el esfuerzo:

```text
              IMPACTO ALTO                      IMPACTO MEDIO / BAJO
           ┌─────────────────────────────┬─────────────────────────────┐
 ESFUERZO  │ M-01 audit composer         │ M-07 cobertura              │
   BAJO    │ M-02 valores de respaldo    │ M-15 longitud de línea      │
           │  → HACER PRIMERO            │  → HACER CUANDO SE PUEDA    │
           ├─────────────────────────────┼─────────────────────────────┤
 ESFUERZO  │ M-03 cierre de actas        │ M-08..M-12 refactorizaciones│
   ALTO    │ M-04 restricciones calendario│ M-05 Policies, M-06 Larastan│
           │  → PLANIFICAR EN EL SPRINT  │  → BACKLOG                  │
           └─────────────────────────────┴─────────────────────────────┘
```

**Orden recomendado:** M-01 → M-02 → M-03 → M-04 → M-06 → M-05 → M-07 → M-08 → M-09 → M-10 → M-11 → M-12. M-13 y M-14 son recurrentes desde ahora.

## 6. Recomendaciones para la evolución del sistema

1. **Mantener la red de seguridad.** Toda refactorización futura debe empezar con una prueba que fije el comportamiento actual, como se hizo en esta guía. La regresión ya demostró su valor al detectar un cambio involuntario en el registro de equipos.
2. **Usar las métricas como guía.** Tomar `docs/evidencias/despues/` como línea base y no aceptar cambios que suban la duplicidad por encima de 0 % ni que agreguen métodos con CCN > 10 sin justificarlo.
3. **Seguir el patrón aplicado.** Controladores delgados que orquestan; servicios en `App\Services\v1` con una sola responsabilidad; consultas reutilizables como *query scopes* del modelo; reglas de rol en el modelo o en *Policies*.
4. **Pasar de reglas fijas a reglas configurables.** Como ocurrió con la puntuación, otras reglas del reglamento (límite de amarillas, sustituciones, minutos de bloqueo de alineación) deben leerse siempre desde `tournament_rules` y tener pruebas que lo verifiquen.
5. **Escalabilidad.** Al crecer el número de torneos simultáneos, mover a colas (*queues*) las tareas posteriores al cierre de actas (notificaciones de WhatsApp, crónica, MVP) y cachear las vistas públicas de alto tráfico.
6. **Seguridad continua.** Agregar `composer audit`, `npm audit` y Larastan al pipeline para que las vulnerabilidades y los errores de tipo se detecten en el *pull request* y no en producción.
7. **Documentación viva.** Actualizar estos cinco documentos y las evidencias cada vez que se repita el ciclo de evaluación → refactorización → reevaluación.
