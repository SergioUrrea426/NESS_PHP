# Instalacion del proyecto

Esta guia cubre macOS y Windows. Ejecuta los comandos desde la carpeta del
proyecto, donde se encuentran `composer.json` y `package.json`.

## Requisitos

- Git.
- PHP 8.3 o superior y Composer 2.
- Node.js 22.18 o superior (o 24.11 o superior) y npm.
- Para SQLite: la extension `pdo_sqlite` de PHP.
- Para PostgreSQL: Docker Desktop y la extension `pdo_pgsql` de PHP.

## Obtener el codigo

Clona el repositorio y entra en su carpeta:

```bash
git clone <URL_DEL_REPOSITORIO>
cd <CARPETA_DEL_REPOSITORIO>
```

En Windows tambien puedes ejecutar estos comandos desde PowerShell.

## Configuracion comun

Instala las dependencias PHP y crea tu archivo de entorno local.

macOS:

```bash
composer install
cp .env.example .env
```

Windows (PowerShell):

```powershell
composer install
Copy-Item .env.example .env
```

No compartas ni subas `.env`; contiene configuracion local y puede incluir
secretos. `.env.example` es la plantilla que si debe versionarse.

## Opcion A: SQLite

SQLite es la opcion mas sencilla para iniciar. Verifica que `pdo_sqlite` este
habilitada en PHP y crea el archivo de base de datos si aun no existe.

macOS:

```bash
touch database/database.sqlite
```

Windows (PowerShell):

```powershell
if (-not (Test-Path database\database.sqlite)) {
    New-Item -ItemType File database\database.sqlite | Out-Null
}
```

En `.env`, deja `DB_CONNECTION=sqlite`. Luego genera la clave, ejecuta las
migraciones e instala las dependencias del frontend:

```bash
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

## Opcion B: PostgreSQL con Docker

Esta opcion usa PostgreSQL 15. El servicio de Docker Compose ejecuta los
scripts SQL del proyecto (esquema y datos iniciales) al crear la base de datos
por primera vez. Asegurate de tener `pdo_pgsql` habilitada en PHP.

Inicia la base de datos:

```bash
docker compose up -d db
```

En `.env`, configura la conexion local que corresponde a `docker-compose.yml`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ness_soft_php
DB_USERNAME=ness_soft_php
DB_PASSWORD=1234
```

Las credenciales incluidas en Compose son solo para desarrollo local; no las
uses en produccion. Despues ejecuta:

```bash
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

Los scripts SQL de inicializacion solo se ejecutan cuando PostgreSQL crea un
directorio de datos nuevo. Los datos locales se guardan en `data/` y no se
suben al repositorio.

## Iniciar la aplicacion

Abre dos terminales en la carpeta del proyecto. En la primera:

```bash
php artisan serve
```

En la segunda:

```bash
npm run dev
```

Abre <http://127.0.0.1:8000> en el navegador. Para ejecutar las pruebas:

```bash
php artisan test
```
