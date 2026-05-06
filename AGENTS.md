# AGENTS.md

Guía corta para agentes de código en este repositorio.

## Antes de tocar código

- Lee [intrucciones.md](intrucciones.md) para reglas de arquitectura, sprints, colores y negocio.
- Lee [CONTRIBUTING.md](CONTRIBUTING.md) para GitFlow, commits atómicos y reglas de trabajo.
- Mantén los cambios mínimos y enlaza documentación existente en lugar de duplicarla.

## Stack y comandos útiles

- Backend: Laravel 11.
- Frontend: Livewire 4, Tailwind CSS, Alpine.js.
- Base de datos: PostgreSQL.
- Scripts reales del proyecto: `composer dev`, `php artisan test`, `php artisan pint`, `npm run dev`, `npm run build`, `php artisan migrate`, `php artisan db:seed`, `php artisan tinker`.

## Convenciones del proyecto

- Modelos en singular PascalCase y tablas en plural snake_case.
- Migraciones con nombres descriptivos y sello temporal.
- No inventes rutas, modelos o flujos si no existen en el código o en [intrucciones.md](intrucciones.md).
- Sigue la estructura existente de `app/Models`, `app/Livewire`, `app/Http`, `database/migrations` y `resources/views`.

## Reglas que no se deben romper

- `boletos` usa UUID como llave primaria.
- `SoftDeletes` es obligatorio en entidades principales como buses, rutas, frecuencias y usuarios.
- `User` usa `HasRoles` de Spatie cuando intervienen permisos o middleware de acceso.
- Toda operación de venta o cobro debe usar `DB::transaction()`.

## Forma de trabajar

- Usa ramas `feature/` o `fix/` derivadas de `develop`; no trabajes directo sobre `main`.
- Mantén commits atómicos y alineados con una única intención lógica.
- Si falta evidencia en el código, detente y consulta la documentación o pregunta antes de asumir.
- Si tocas una zona ya existente, conserva su estilo y patrones; no refactorices fuera del alcance.

## Referencias de verdad

- Reglas de sprint, arquitectura y UI: [intrucciones.md](intrucciones.md)
- Flujo GitFlow y convenciones de commits: [CONTRIBUTING.md](CONTRIBUTING.md)