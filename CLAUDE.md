# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 10 API-only backend for a kiosk / point-of-sale system ("quiosco"). PHP 8.1+, MySQL, Sanctum tokens. No Blade UI — the frontend is a separate SPA that consumes `routes/api.php`. Domain language is Spanish: `Categoria`, `Producto`, `Pedido` (order), `Promocion`, `ContenedorOpcione` (option group / container), `Opcione` (single option).

## Common commands

```bash
# Dependencies
composer install
npm install

# Local dev (without Sail)
php artisan serve                 # API on http://localhost:8000
npm run dev                       # Vite dev server (asset compilation only)

# Local dev (with Sail / Docker)
./vendor/bin/sail up -d           # boots app + mysql + redis + meilisearch + mailpit + selenium
./vendor/bin/sail artisan ...     # run artisan inside the container

# Database
php artisan migrate
php artisan migrate:fresh --seed  # full reset; DatabaseSeeder runs all domain seeders in order

# Tests (PHPUnit 10)
php artisan test                                  # all suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --filter=SomeTestName            # single test/method

# Formatting
./vendor/bin/pint                 # Laravel Pint (PHP-CS-Fixer wrapper)
```

`.env` must include `CLOUDINARY_URL` for any product/promo image flow to work — image uploads are not optional in `ProductoController::store`.

## Architecture

### Two parallel authentication systems

There are **two distinct authenticatable models**, each with its own table, login endpoint, and Sanctum token issuer:

- **`User`** (`users` table) — customers and back-office staff. Role is resolved via `user_roles` pivot to the `Role` model. Endpoints: `/api/registro`, `/api/login`, `/api/user`.
- **`Employee`** (`employees` table, added 2026-05-28) — staff with employment metadata (salary, position, department, hire_date, username). Defaults to `rol = 'admin'`. Endpoints: `/api/employee/register`, `/api/employee/login`, `/api/employees/session`.

Both use `HasApiTokens`. When adding endpoints, decide which guard/model is the source of truth before reading `$request->user()` — the returned model depends on which token was issued.

Legacy note: `PedidoController::index` still branches on `$usuario->admin`, but the `admin` column was dropped (`2024_06_01_131621_drop_admin_column_from_users_table.php`) in favor of the roles pivot. Treat that branch as stale — admin status now comes from `User->roles()`.

### Order (Pedido) lifecycle

- `numero_pedido` follows a custom sequence `A-000` … `Z-999`, generated in `PedidoController::store`. Letter rolls forward on overflow; sequence wraps `Z → A`. The store method also re-checks for collisions in a `while` loop, so don't replace it with a naive autoincrement.
- `estado` is a tinyint state machine driven by `PedidoController::update` via an `identificador` field in the request body:
  - `0` "por preparar" → set to `1`
  - `1` "por entregar" → set to `2`
  - `2` "entregado" → set to `3` (completed; counted in revenue dashboards)
- `eliminado` (boolean) is a soft-delete flag used across `pedidos`, `categorias`, `productos`, and `users`. Queries filter `where('eliminado', 0)` — do not use Laravel's `SoftDeletes` trait, that's not what this project uses.

### Order composition

A `Pedido` has many `PedidoProducto` (line items), each of which has many `DetallesProductoPedido` (the chosen options for that line — e.g. "size: large", "topping: cheese", with `nombre_contenedor` / `tipo_contenedor` / `opcion` / `precio_opcion` / `cantidad` denormalized onto the row). When eager-loading, the canonical chain is:

```php
Pedido::with('user')
    ->with('productos.promocion')
    ->with('pedidoProductos.detallesProductoPedido')
```

### Product options model

Products attach to **option containers** (`ContenedorOpcione`) via a many-to-many pivot (`contenedor_opcione_producto`), and each container has many `Opcione` rows. `ProductoController::store` is the authoritative example: it looks up an existing container by `nombre`, reuses its id if found, otherwise creates the container and its `Opcione` children, then calls `$producto->contenedorOpciones()->sync($contenedoresIds)`. Duplicate this lookup-or-create pattern when adding container-related endpoints — there is no unique constraint enforcing it at the DB level.

### Image uploads (Cloudinary)

`cloudinary-labs/cloudinary-laravel` handles all product/promo images. The pattern in `ProductoController` is:

1. `Cloudinary::destroy($producto->public_id)` before replacing.
2. `Cloudinary::upload($file->getRealPath(), ['folder' => 'productos', 'format' => 'avif'])`.
3. Persist both `imagen` (secure URL) and `public_id` on the model.

Forgetting to persist `public_id` means future updates can't clean up the old asset.

### Re-deleting / restoring products

`ProductoController::store` has a non-obvious branch: if a product with the same `nombre` exists but `eliminado === 1`, it **restores and overwrites** that row rather than creating a duplicate. New products only insert when no row with that name exists. Preserve this behavior — duplicate `nombre` after a soft-delete is intentional.

### Route conventions

- All authenticated endpoints are inside the `auth:sanctum` group at the top of `routes/api.php`.
- The codebase deliberately mixes `apiResource` with hand-rolled verb-specific routes (`/categorias/create`, `/productos/actualizar/{producto}`, `/pedidos/nuevo`). Match the existing style for the resource you're touching — don't refactor a controller to pure REST in passing.
- `POST` is used for updates that include file uploads (`/categorias/update/{categoria}`, `/productos/actualizar/{producto}`) because multipart + Laravel's method-spoofing on `PUT` was avoided.

### Seeders

`DatabaseSeeder` runs in dependency order: roles → categorias → promociones → productos → contenedores → contenedor↔producto pivot → users → employees → pedidos. When adding a new seeder, insert it at the correct point in the chain in `DatabaseSeeder::run` or `migrate:fresh --seed` will fail on FK constraints.

## CORS

`config/cors.php` is wide open (`allowed_origins_patterns` matches everything) — recent commits intentionally relaxed it for the SPA. Don't tighten it without checking with the user first.
