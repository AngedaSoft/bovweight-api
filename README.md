# BovWeight CR — API REST

API backend del proyecto **BovWeight CR**, sistema móvil de estimación de peso bovino mediante visión por computadora para ganaderos de la región Chorotega de Costa Rica.

> Curso IF-7100 · Ingeniería del Software · I Ciclo 2026
> Universidad de Costa Rica · Sede de Guanacaste

## Stack

| Capa | Tecnología |
|---|---|
| Lenguaje | PHP 8.2+ |
| Framework | Laravel 11 |
| Base de datos | MySQL 8 |
| Autenticación | Laravel Sanctum (tokens Bearer) |
| Almacenamiento de archivos | DigitalOcean Spaces (S3-compatible) |
| Servicio de IA | Microservicio Flask + YOLOv8n-Pose (separado, ver repo `bovweight-ai`) |

## Requisitos previos

- PHP 8.2 o superior con extensiones: `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `bcmath`, `fileinfo`
- Composer 2.x
- MySQL 8 (recomendado: XAMPP o Laragon en Windows)
- Git
- Opcional: GitHub CLI (`gh`) para crear milestones e issues automáticamente

## Setup local

```bash
git clone https://github.com/AngedaSoft/bovweight-api.git
cd bovweight-api
composer install
cp .env.example .env
php artisan key:generate
```

Crear la base de datos vacía en MySQL local:

```sql
CREATE DATABASE bovweight_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Editar `.env` con tus credenciales locales y correr:

```bash
php artisan migrate
php artisan serve
```

Verificar que todo funciona:

```bash
curl http://localhost:8000/api/health
```

Respuesta esperada:

```json
{
  "status": "ok",
  "app": "bovweight-api",
  "environment": "local",
  "database": "ok",
  "timestamp": "2026-04-28T...",
  "version": "0.1.0"
}
```

## Estructura del proyecto

```
app/
├── Enums/                    Enums tipados (estados, roles, etc.)
├── Http/
│   ├── Controllers/Api/      Controllers REST por recurso
│   ├── Requests/             Form Requests (validación)
│   └── Resources/            API Resources (serialización)
├── Models/                   Eloquent models del modelo de dominio
└── Services/                 Lógica de negocio (inyección por DI)

database/
└── migrations/               Migraciones de las 13 tablas del dominio

routes/
├── api.php                   Endpoints REST
└── web.php                   (no usado, es API pura)

config/
└── cors.php                  Orígenes permitidos para la app móvil
```

## Endpoints disponibles

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| GET | `/api/health` | — | Health-check del servidor y BD |
| POST | `/api/auth/register` | — | Registro de propietario / veterinario |
| POST | `/api/auth/login` | — | Login con `correo` y `contrasena` |
| POST | `/api/auth/logout` | Bearer | Revoca el token actual |
| GET | `/api/auth/me` | Bearer | Devuelve el usuario autenticado |
| GET/POST | `/api/fincas` | Bearer | Listar / crear fincas del usuario |
| GET/PUT/DELETE | `/api/fincas/{id}` | Bearer | Ver / actualizar / eliminar finca |
| GET/POST | `/api/animales` | Bearer | Listar (filtros: `finca_id`, `estado`, `arete`) / crear animal |
| GET/PUT/DELETE | `/api/animales/{id}` | Bearer | Ver / actualizar / eliminar animal |
| GET | `/api/razas` | Bearer | Catalogo de razas |
| GET | `/api/animales/{id}/pesajes` | Bearer | Historial de pesajes del animal |
| GET | `/api/pesajes/{id}` | Bearer | Detalle de un pesaje |
| PATCH | `/api/pesajes/{id}/correccion` | Bearer | Corregir peso manualmente |
| POST | `/api/estimaciones` | Bearer | Crear pesaje IA con foto o medidas manuales (`multipart/form-data`) |

## Arquitectura por capas (SOLID)

```
Http/Controllers/Api/   <-- transporte HTTP delgado
Http/Requests/          <-- validacion declarativa (FormRequest)
Http/Resources/         <-- serializacion JSON
Services/               <-- logica de negocio (SRP por dominio)
  Auth/                 <-- AuthService (registro/login/tokens)
  Finca/                <-- FincaService
  Animal/               <-- AnimalService
  Pesaje/               <-- PesajeService (correccion + historial)
  Estimacion/           <-- EstimacionService (orquesta llamada al ML)
  Ml/                   <-- MlEstimacionClient (interface) + HttpMlEstimacionClient
Policies/               <-- autorizacion granular por modelo
Models/                 <-- Eloquent (entidades del dominio)
```

- **SRP**: cada servicio tiene una sola responsabilidad de negocio.
- **OCP**: agregar un nuevo cliente del ML solo requiere implementar `MlEstimacionClient`.
- **LSP**: cualquier `MlEstimacionClient` (real, fake en tests, stub) es sustituible.
- **ISP**: la interface `MlEstimacionClient` tiene un solo metodo `estimar`.
- **DIP**: `EstimacionService` recibe la interface, no la implementacion HTTP concreta
  (ver `AppServiceProvider::register`).

## Cuentas demo (creadas por el seeder)

| Rol           | Correo                       | Contrasena |
|---------------|------------------------------|------------|
| Administrador | admin@bovweight.local        | admin12345 |
| Propietario   | ganadero@bovweight.local     | ganadero1  |

## Levantar con Docker

Este repo trae `Dockerfile` con Nginx + php-fpm + supervisord. El orquestador
unico para la pila completa (API + MySQL + ML + web + mobile) vive en
[`../bovweight-deploy`](../bovweight-deploy). Para correr solo este servicio:

```bash
docker build -t bovweight-api .
docker run --rm -p 8000:80 --env-file .env.docker bovweight-api
```

## Testing

```bash
./vendor/bin/phpunit            # 19 tests cubren auth, fincas, animales, pesajes y estimacion
```

Los tests usan SQLite en memoria; ver `phpunit.xml`. El cliente del ML se
sustituye por un fake via `$this->app->instance(MlEstimacionClient::class, ...)`.

## Git Flow y convenciones

Trabajamos con **Git Flow simplificado**:

- `main` — solo recibe releases estables, mediante PR desde `test`
- `test` — rama de QA, recibe features de `dev` para validación
- `dev` — rama de integración, todos los features se mergean acá

Las **features** se desarrollan en ramas con prefijo:

```
feature/<scope>-<descripcion-corta>
fix/<scope>-<descripcion-corta>
chore/<scope>-<descripcion-corta>
```

Ejemplo: `feature/auth-sanctum-login`, `fix/pesajes-calculo-corregido`.

### Conventional Commits

Todos los commits deben seguir el formato:

```
<type>(<scope>): <subject>

[body opcional]

[footer opcional]
```

Tipos: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`, `ci`, `revert`.

El template está en `.gitmessage`. Para usarlo en este repo:

```bash
git config commit.template .gitmessage
```

## Comandos útiles

```bash
# Servidor de desarrollo
php artisan serve

# Migraciones
php artisan migrate                # Aplicar migraciones pendientes
php artisan migrate:fresh          # Borrar todo y volver a migrar
php artisan migrate:status         # Ver estado de migraciones

# Cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Generar artefactos
php artisan make:model Nombre -mfsr   # Modelo + migration + factory + seeder + resource
php artisan make:controller Api/NombreController --api
php artisan make:request NombreRequest

# Testing
php artisan test
```

## Variables de entorno sensibles

**Nunca commitear `.env`.** El archivo `.env.example` contiene placeholders. Para variables de producción, usar el panel del proveedor de hosting o un gestor de secretos.

Si una credencial fue expuesta accidentalmente (en chat, logs, screenshots), rotarla inmediatamente en el proveedor.

## Roadmap

- **Sprint 1** — Setup, autenticación, modelo de dominio, CRUDs básicos
- **Sprint 2** — Integración con servicio de IA, captura y procesamiento de fotos
- **Sprint 3** — Colaboración (acceso compartido, transferencias), reportes, notificaciones, despliegue

Detalle en `docs/milestones/`.

## Equipo

| Rol | Nombre |
|---|---|
| Scrum Master / IA | Rafael David Hernández González |
| Product Owner | Gerald |
| Frontend (Ionic) | Ángeles |
| Backend (Laravel) | Steven |
| Stakeholder ganadero | Don Iván Chavarría |
| Stakeholder veterinario | Dr. Caleb Chavarría |

## Licencia

Proyecto académico - Universidad de Costa Rica · Sede de Guanacaste · 2026.
