# Instalación y configuración de SIGET-SF con Docker, WSL 2 y Laravel 13

## 1. Instalación de Componentes y Configuración de WSL 2

- Se descargó **Docker Desktop para Windows** utilizando la arquitectura tradicional de 64 bits (`AMD64/x86_64`), compatible con el procesador AMD Ryzen.
- Se activaron los componentes requeridos por Windows mediante **PowerShell**, ejecutado con permisos de administrador:

```powershell
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
```

- Se instaló el subsistema e inicializó la distribución de Ubuntu mediante el comando oficial:

```powershell
wsl --install
```

- Se configuró la cuenta de usuario de Linux (`cfabiola25`) y su respectiva contraseña para finalizar la integración de **WSL 2** con **Docker Desktop**.

---

## 2. Creación de la Estructura de Carpetas del Proyecto

Se creó el directorio raíz del proyecto y las subcarpetas necesarias para separar el código fuente (`src`) de los servicios de infraestructura (`docker/nginx` y `docker/php`):

```dos
mkdir siget-sf
cd siget-sf
mkdir src
mkdir docker
mkdir docker\nginx
mkdir docker\php
```

La estructura inicial quedó organizada de la siguiente manera:

```text
siget-sf/
├── src/
└── docker/
    ├── nginx/
    └── php/
```

---

## 3. Creación de Archivos de Configuración y Orquestación

### `docker-compose.yml`

En la raíz del proyecto se definió la arquitectura física mediante tres contenedores principales:

- **`web`**: servidor web Nginx.
- **`app`**: aplicación con PHP 8.5-FPM.
- **`db`**: base de datos MySQL 8.0.

### `docker/php/Dockerfile`

Se configuró la imagen de PHP 8.5, instalando las extensiones necesarias para Laravel, entre ellas:

- `pdo_mysql`
- `mbstring`
- `gd`

También se configuró el gestor de dependencias **Composer**.

### `docker/nginx/default.conf`

Se establecieron las reglas de configuración y enrutamiento de **Nginx**, permitiendo dirigir las solicitudes web hacia el contenedor de PHP-FPM.

La estructura de configuración quedó organizada así:

```text
siget-sf/
├── docker-compose.yml
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
└── src/
```

---

## 4. Construcción e Inicio de la Infraestructura Física

Con **Docker Desktop** ejecutándose y mostrando el estado **Engine running**, se levantaron los contenedores en segundo plano y se construyeron las imágenes mediante:

```bash
docker-compose up -d --build
```

Este comando permite:

1. Construir las imágenes definidas en la configuración.
2. Crear los contenedores.
3. Crear la red interna entre los servicios.
4. Iniciar los servicios en segundo plano mediante la opción `-d`.

---

## 5. Instalación y Configuración del Framework Laravel 13

Se instaló el núcleo del framework **Laravel 13** dentro de la carpeta `src`, utilizando el contenedor PHP y Composer:

```bash
docker-compose exec app composer create-project --prefer-dist laravel/laravel .
```

Posteriormente, se configuraron las credenciales de conexión a la base de datos MySQL en el archivo `.env`.

La conexión apunta al nombre del servicio Docker `db` como host:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=siget_sf
DB_USERNAME=root
DB_PASSWORD=root
```

> **Nota:** Dentro de la red de Docker, `DB_HOST=db` permite que Laravel se comunique con el contenedor de MySQL utilizando el nombre del servicio definido en `docker-compose.yml`.

---

## 6. Corrección de Permisos y Ejecución de Migraciones

Para garantizar que Laravel pudiera escribir correctamente en sus directorios temporales y de caché, se otorgaron permisos de lectura y escritura:

```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

Posteriormente, se limpió la caché de vistas compiladas:

```bash
docker-compose exec app php artisan view:clear
```

Finalmente, se ejecutaron las migraciones de Laravel para crear las tablas correspondientes en MySQL:

```bash
docker-compose exec app php artisan migrate
```

Las migraciones permiten crear y mantener la estructura de la base de datos definida por Laravel, incluyendo las tablas necesarias para funcionalidades como usuarios, sesiones y caché, según la configuración y versión del proyecto.

---

## Resumen de la arquitectura

Al finalizar la configuración, la infraestructura del proyecto quedó organizada de la siguiente forma:

```text
                         ┌─────────────────────┐
                         │     Navegador       │
                         └──────────┬──────────┘
                                    │
                                    ▼
                         ┌─────────────────────┐
                         │   Nginx (web)       │
                         │     Contenedor      │
                         └──────────┬──────────┘
                                    │
                                    ▼
                         ┌─────────────────────┐
                         │ PHP 8.5-FPM (app)   │
                         │      Laravel 13     │
                         └──────────┬──────────┘
                                    │
                                    ▼
                         ┌─────────────────────┐
                         │    MySQL 8.0 (db)   │
                         │    siget_sf          │
                         └─────────────────────┘
```

### Estructura final

```text
siget-sf/
├── docker-compose.yml
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
└── src/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── artisan
    ├── composer.json
    └── .env
```

## Comandos principales utilizados

| Etapa | Comando |
|---|---|
| Activar WSL | `wsl --install` |
| Crear proyecto | `mkdir siget-sf` |
| Construir contenedores | `docker-compose up -d --build` |
| Instalar Laravel | `docker-compose exec app composer create-project --prefer-dist laravel/laravel .` |
| Corregir permisos | `docker-compose exec app chmod -R 777 storage bootstrap/cache` |
| Limpiar vistas | `docker-compose exec app php artisan view:clear` |
| Ejecutar migraciones | `docker-compose exec app php artisan migrate` |
