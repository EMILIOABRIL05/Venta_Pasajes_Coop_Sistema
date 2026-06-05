# Security Protections — Checklist y estado

Estado actual (aplicado)
- Seeder maestro de roles/permisos: `database/seeders/RolesAndPermissionsSeeder.php` (implementado y probado).
- Middleware aliases `role` / `permission` registrados en `bootstrap/app.php`.
- Ruta Livewire ejemplo `/admin` protegida con `->middleware(['auth','role:admin'])` en `routes/web.php`.
- Trait Livewire `App\Livewire\Traits\RequiresRole` disponible y usado por `App\Livewire\AdminPanel`.

Livewire components actuales y protección aplicada
- `App\Livewire\AdminPanel` — requiere rol `admin` (ruta protegida y `RequiresRole` en `mount`).
- `App\Livewire\BuscadorPasajes` — componente público (NO auth).

Rutas actuales relevantes
- `/admin` -> `AdminPanel` : `auth` + `role:admin`.
- `/dashboard`, `/profile`, auth routes: `auth` / `verified` según plantilla.

Checklist por Estudiante (Sprint 0.3.0) — qué proteger y cómo

- ✅ **Estudiante 2 (Catálogos: Buses) — COMPLETADO**
  - Implementado: `auth` + `role:admin|oficinista` en rutas y mount().
  - Delete: Protegido con validación `hasRole('admin')` en métodos delete().
  - Detalle: 
    - BusesCrud: `/catalogos/buses` → middleware `['auth', 'role:admin|oficinista']`, delete solo admin
  - Implementación: Trait RequiresRole en mount() + guards en save/delete métodos
  - PR/Commit: feat: estructura almacenamiento fotos buses y usuarios prueba + fix: protecciones de rol en delete y lectura para catalogos

- Estudiante 3 (Operativa: Frecuencias, Hoja de Ruta)
  - Protecciones: `permission:manage_frecuencias` o `role:admin` para CRUD.
  - Hoja de Ruta (asignar): `role:admin|oficinista`.

- Estudiante 4 (Ventanilla)
  - Panel oficinista: `auth` + `role:oficinista`.
  - Aprobar comprobantes: `permission:validate_comprobantes`.

- Estudiante 5 (Web)
  - Buscador público: NO auth.
  - Compra/historial/subida comprobante: `auth` + `verified` (asignar rol `cliente` al registrar).
  - Assign role in `RegisteredUserController::store()`: `$user->assignRole('cliente');`

- Estudiante 6 (Móvil/Post)
  - Chofer (scan QR, ventas onboard): `auth` + `role:chofer`.
  - Acciones puntuales: `permission:scan_qr`, `permission:sell_onboard`.

Buenas prácticas
- Proteger tanto en la ruta (cuando exista) como en el componente `mount()` (defensa en profundidad).
- Usar `permission` para acciones puntuales (aprobar/elim/descargar reportes).
- Documentar en la PR qué middleware/permiso se añade y por qué.

Próximos pasos recomendados
1. Cada estudiante añada la protección indicada en su Sprint antes de enviar PR.
2. Revisar `RegisteredUserController::store()` para asignar `cliente` por defecto.
3. Mantener este archivo actualizado cuando se creen nuevos componentes/rutas.

---
Archivo generado automáticamente por la tarea de seguridad (Estudiante 1).
