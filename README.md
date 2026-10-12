# SIGET - Sistema Integral de Gestión de Torneos Deportivos

[![Versión](https://img.shields.io/badge/version-1.0.0-emerald.svg)](https://github.com/Cfabiola25/SIGET_V1)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](https://docker.com)

**SIGET (Sistema Integral de Gestión de Torneos)** es una plataforma SaaS moderna, robusta y de alto rendimiento diseñada para la administración integral, programación inteligente, control arbitral en tiempo real y difusión pública de competiciones y torneos de fútbol en sus diversas modalidades federativas.

---

## 🚀 Características Principales

### 🌐 Portal Público Interactivo
- **Selector de Torneo Principal:** Visualización preferente del torneo destacado configurado por el Super Administrador.
- **Fixture y Resultados:** Calendario interactivo con estados de partidos (programado, en vivo y finalizado).
- **Tabla de Posiciones Oficial:** Clasificación dinámica con criterios de desempate (Puntos, DG, GF, Victorias).
- **Líderes de Goleo:** Tabla estadística de artilleros en tiempo real.
- **IA Match Reporter:** Generación autónoma de crónicas periodísticas deportivas al certificar el cierre de actas.
- **Votación MVP en Vivo:** Participación de aficionados en la elección de la figura del encuentro.
- **Widgets Embebibles y API Pública:** Integración de tablas y calendarios en portales de clubes mediante `iframe` o endpoints JSON.

### 👑 Panel Super Administrador (`super_admin`)
- **Gestión Multi-Tenant:** Creación y activación/desactivación de administradores y directores de torneo.
- **Catálogo Oficial de Disciplinas:** Gestión de las 4 modalidades federativas de fútbol:
  - Fútbol 11
  - Fútbol 8 (Canchas sintéticas)
  - Fútbol 5 (Canchas sintéticas)
  - Fútbol de Salón (Microfútbol)
- **Fijación de Torneo Principal:** Designación del torneo protagonista visible en el portal público.

### 🛡️ Backoffice & Gestión Administrativa (`admin`)
- **Planificador de Torneos y Sedes:** Registro de canchas, horarios y fases de competencia.
- **Generador de Fixtures y Brackets:** Sorteo automatizado de calendarios y llaves eliminatorias de playoffs.
- **Gestión de Clubes y Jugadores:** Control de inscripciones, estados de pago y nóminas oficiales.
- **Tribunal de Penas y Disciplina:** Registro de sanciones automáticas por acumulación de tarjetas y resoluciones federativas.
- **Auditoría Integral:** Registro inmutable de acciones administrativas y de seguridad.

### 📋 Mesa de Control & Consola Match Day (`referee`)
- **Validación Digital QR:** Escaneo y verificación de identidad de jugadores en campo.
- **Consola en Vivo:** Cronómetro oficial sincronizado y registro de eventos minuto a minuto (goles, tarjetas, sustituciones).
- **Transmisión en Vivo (SSE):** Actualizaciones en tiempo real para espectadores mediante Server-Sent Events.
- **Cierre y Firmas Digitales:** Planilla oficial con firma digital del árbitro y delegados, generación de acta oficial imprimible.

### ⚽ Portal del Director Técnico (`coach` / `captain`)
- **Gestión de Plantilla:** Carga masiva de jugadores mediante CSV y fichas individuales.
- **Carnets y Cromos Digitales:** Ficha médica, dorsal y carnet federativo digital.
- **Alineaciones Oficiales:** Selección táctica de titulares y suplentes previo al silbatazo inicial.
- **Radar de Scouting:** Búsqueda de agentes libres y solicitudes de fichaje.

---

## 🏗️ Arquitectura de Infraestructura con Docker

La aplicación está completamente dockerizada mediante una arquitectura multicapa aislada y optimizada:

| Servicio | Contenedor | Imagen | Puerto | Descripción |
|---|---|---|---|---|
| **web** | `siget_web` | `nginx:alpine` | `80:80` | Proxy inverso, compresión Gzip, streaming SSE y caché estático |
| **app** | `siget_app` | PHP 8.5-FPM (Custom) | `9000` | Entorno Laravel 13 con extensiones `pdo_mysql`, `intl`, `gd`, `zip`, `opcache` |
| **db** | `siget_db` | `mysql:8.0` | `3306:3306` | Base de datos relacional MySQL con volumen persistente `db_data` |

---

## ⚡ Instalación y Puesta en Marcha (Docker)

### Prerrequisitos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado con soporte WSL2 (en Windows) o Docker Engine en Linux/macOS.
- Git instalado.

### 1. Clonar el repositorio
```bash
git clone https://github.com/Cfabiola25/SIGET_V1.git
cd siget
```

### 2. Configurar variables de entorno
Copie la plantilla de variables de entorno oficial preconfigurada para Docker:
```bash
cp src/.env.example src/.env
```

### 3. Construir y levantar los contenedores
```bash
docker compose up -d --build
```

### 4. Inicializar la Base de Datos Oficial (Limpia)
Ejecute las migraciones y el seeder oficial para dejar el sistema listo para producción:
```bash
docker compose exec -T app php artisan key:generate
docker compose exec -T app php artisan migrate:fresh --seed
```

### 5. Acceder a la plataforma
- **Portal Público:** [http://localhost](http://localhost)
- **Inicio de Sesión:** [http://localhost/login](http://localhost/login)

---

## 🔑 Credenciales Oficiales de Acceso

El sistema se entrega en su versión oficial limpia, configurada únicamente con la cuenta del **Super Administrador**:

- **Correo electrónico:** `superadmin@siget.com`
- **Contraseña:** `password`
- **Rol:** `super_admin`

> 💡 **Nota de Seguridad:** Se recomienda cambiar la contraseña predeterminada desde el panel administrativo al desplegar en entornos productivos.

---

## 🛠️ Comandos de Mantenimiento Frecuentes

### Ver estado de los contenedores
```bash
docker compose ps
```

### Ver registros en tiempo real (logs)
```bash
docker compose logs -f
```

### Ejecutar pruebas automatizadas de Laravel
```bash
docker compose exec -T app php artisan test
```

### Limpiar y optimizar cachés de Laravel
```bash
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize
```

### Detener los contenedores
```bash
docker compose down
```

---

## 📄 Licencia

Este proyecto es propiedad del equipo de desarrollo de SIGET bajo licencia comercial / privada. Todos los derechos reservados © 2026.
