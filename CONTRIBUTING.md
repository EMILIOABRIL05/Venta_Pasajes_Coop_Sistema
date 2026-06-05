# Guía de contribución

Gracias por contribuir al Sistema de Venta de Pasajes de la Cooperativa Ambato.
Este documento resume el flujo de trabajo, las convenciones técnicas y las
reglas mínimas para mantener el repositorio ordenado.

Antes de escribir código, lee también [intrucciones.md](intrucciones.md), donde
están las reglas de arquitectura, negocio, UI y sprints del proyecto.

## Flujo de ramas

El proyecto usa GitFlow:

- `main`: rama de producción. No recibe commits directos.
- `develop`: rama de integración. Todo cambio debe entrar mediante Pull Request.
- `feature/nombre-de-la-tarea`: nuevas funcionalidades.
- `fix/nombre-del-error`: correcciones de errores.

Siempre crea tu rama desde `develop`:

```bash
git checkout develop
git pull origin develop
git checkout -b feature/mi-tarea
```

## Commits atómicos

Cada commit debe representar una única intención lógica. Evita mezclar
migraciones, modelos, vistas y cambios de configuración en un solo commit si
pueden revisarse por separado.

Ejemplo:

```bash
git add database/migrations/xxxx_create_buses_table.php
git commit -m "feat: crear migración de buses"

git add app/Models/Bus.php
git commit -m "feat: crear modelo Bus"

git add resources/views/livewire/catalogos/buses-crud.blade.php
git commit -m "ui: maquetar formulario de buses"
```

## Prefijos de commit

Usa mensajes claros, en español y con uno de estos prefijos:

- `feat:` nueva funcionalidad.
- `ui:` cambios visuales o de interfaz.
- `fix:` corrección de errores.
- `refactor:` mejora interna sin cambiar comportamiento esperado.
- `docs:` documentación.
- `config:` configuración, rutas, Docker o herramientas.

## Pull Requests

Antes de abrir un Pull Request:

1. Verifica que tu rama venga de `develop`.
2. Ejecuta las pruebas y herramientas de calidad aplicables.
3. Confirma que el PR tenga como base `develop`.
4. Describe el cambio, el motivo y las pruebas realizadas.
5. Espera revisión del líder o de otro integrante; no te autoapruebes.

Comandos recomendados:

```bash
php artisan pint
php artisan test
npm run build
```

## Convenciones técnicas

- Modelos en singular PascalCase: `Boleto`, `HojaRuta`, `Frecuencia`.
- Tablas en plural snake_case: `boletos`, `hojas_ruta`, `frecuencias`.
- La tabla `boletos` debe usar UUID como llave primaria.
- Entidades principales como buses, rutas, frecuencias y usuarios deben usar
  `SoftDeletes`.
- Cuando intervienen permisos o middleware de acceso, `User` debe usar
  `HasRoles` de Spatie.
- Toda operación de venta, cobro o cambio financiero debe ejecutarse dentro de
  `DB::transaction()`.

Ejemplo:

```php
DB::transaction(function () {
    // Guardar venta, boletos y pagos relacionados.
});
```

## Seguridad y datos sensibles

- No subas archivos `.env`, credenciales, tokens ni respaldos con datos reales.
- Usa `.env.example` para documentar variables necesarias.
- Reporta vulnerabilidades siguiendo [SECURITY.md](SECURITY.md).

## Dudas

Si falta evidencia en el código o en la documentación, detente y consulta al
líder del proyecto antes de asumir un flujo nuevo.
