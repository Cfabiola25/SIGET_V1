#!/usr/bin/env bash
# =============================================================================
# SIGET - Análisis estático de calidad (Guía Práctica N.º 11)
# Uso:   bash scripts/analisis-calidad.sh <etiqueta>     (ej.: antes | despues)
# Salida: docs/evidencias/<etiqueta>/
#
# Herramientas requeridas (Ubuntu 24.04):
#   sudo apt install cloc phpmd phpcpd php-codesniffer pdepend
#   pip install lizard
#   Laravel Pint ya viene en src/vendor/bin/pint (composer install)
#
# Alcance: solo código escrito por el equipo (src/app y src/routes).
# Se excluyen vendor/, node_modules/, public/build/, storage/, bootstrap/cache,
# vistas Blade compiladas y archivos generados automáticamente.
# =============================================================================
set -u

ETIQUETA="${1:-antes}"
RAIZ="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$RAIZ/src"
OUT="$RAIZ/docs/evidencias/$ETIQUETA"
mkdir -p "$OUT"
cd "$SRC" || exit 1

echo "==> [1/8] cloc: líneas de código y archivos"
cloc app routes --quiet --exclude-dir=vendor,node_modules > "$OUT/01-cloc.txt"
cloc app routes --quiet --by-file --include-lang=PHP --csv > "$OUT/01-cloc-por-archivo.csv"

echo "==> [2/8] lizard: complejidad ciclomática (CCN) por método"
lizard app -l php -w -C 10 -L 60 > "$OUT/02-lizard-advertencias.txt"
lizard app -l php --csv > "$OUT/02-lizard-metodos.csv"
lizard app -l php | tail -n 4 > "$OUT/02-lizard-resumen.txt"

echo "==> [3/8] phpcpd: duplicidad de código (mín. 5 líneas / 50 tokens)"
phpcpd --min-lines=5 --min-tokens=50 app > "$OUT/03-phpcpd.txt" 2>&1

echo "==> [4/8] phpmd: problemas de diseño (codesize, design, unusedcode, cleancode, naming)"
phpmd app text codesize,design,unusedcode > "$OUT/04-phpmd-diseno.txt" 2>&1
phpmd app text cleancode,naming > "$OUT/04-phpmd-calidad.txt" 2>&1

echo "==> [5/8] phpcs: incumplimiento del estándar PSR-12"
phpcs --standard=PSR12 --report=summary app routes > "$OUT/05-phpcs-psr12.txt" 2>&1
phpcs --standard=PSR12 --report=source app routes > "$OUT/05-phpcs-psr12-reglas.txt" 2>&1

echo "==> [6/8] pint: estilo Laravel (modo verificación, no modifica archivos)"
vendor/bin/pint --test app routes > "$OUT/06-pint.txt" 2>&1

echo "==> [7/8] pdepend: acoplamiento, cohesión y abstracción por paquete"
pdepend --summary-xml="$OUT/07-pdepend-summary.xml" app > /dev/null 2>&1

echo "==> [8/8] seguridad: patrones de riesgo + npm audit"
{
  echo "## Salida sin escapar en Blade ({!! !!})"
  grep -rn '{!!' resources/views | grep -v 'json_encode\|@json' || true
  echo; echo "## SQL crudo (DB::raw / whereRaw / selectRaw / orderByRaw)"
  grep -rnE 'DB::raw|whereRaw|selectRaw|orderByRaw|havingRaw|DB::statement' app || true
  echo; echo "## Modelos sin protección de asignación masiva (\$guarded = [])"
  grep -rn 'guarded = \[\]' app || true
  echo; echo "## Secretos codificados en el código"
  grep -rniE "(api_key|secret|password)\s*=\s*['\"][^'\"]{6,}" app config || true
} > "$OUT/08-seguridad-patrones.txt"
npm audit --omit=optional > "$OUT/08-npm-audit.txt" 2>&1 || true

# Rutas relativas al repositorio para que las evidencias sean portables
sed -i "s#$RAIZ/##g" "$OUT"/*.txt "$OUT"/*.csv "$OUT"/*.xml 2>/dev/null

echo "Listo. Evidencias en: docs/evidencias/$ETIQUETA/"
