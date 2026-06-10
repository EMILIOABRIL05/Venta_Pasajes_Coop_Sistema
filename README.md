# Sistema de Venta de Pasajes - Cooperativa Ambato

Plataforma web para la gestión operativa y venta de pasajes de la Cooperativa
de Transportes Ambato, Ecuador. El sistema centraliza catálogos de flota, rutas,
frecuencias, ventas en ventanilla, ventas web, cierres de turno, reembolsos y
validación de boletos mediante códigos QR para choferes.

## Propósito

Este repositorio sirve como base de trabajo para el equipo de desarrollo del
sistema de venta de pasajes. El proyecto no es multi-tenant: toda la aplicación
está pensada para una sola cooperativa y sus procesos internos.

## Alcance funcional

- Catálogos: categorías de asiento, buses y cuentas de usuarios.
- Operativa: rutas, paradas, frecuencias y programación de viajes.
- Ventanilla: venta de pasajes, historial, cierres de turno y reportes.
- Web: búsqueda pública, carrito de compra, pago web e historial del cliente.
- Chofer: panel móvil y validación de boletos por QR.
- Soporte: solicitudes de cambio, reembolsos y reportes técnicos.

## Stack tecnológico

- Backend: Laravel 11.
- Frontend: Livewire 4, Alpine.js y Tailwind CSS.
- Build frontend: Vite.
- Base de datos: PostgreSQL.
- Permisos: Spatie Laravel Permission.
- PDF, QR y reportes: DomPDF, Simple QrCode y Laravel Excel.
- Infraestructura: Docker y Docker Compose.

## Requisitos

- PHP 8.2 o superior.
- Composer.
- Node.js 18 o superior y npm.
- PostgreSQL.
- Docker y Docker Compose para ejecución contenerizada.

## Instalación local

1. Copia el archivo de entorno:

   ```bash
   cp .env.example .env
   ```

2. Configura la conexión a PostgreSQL en `.env`.

3. Instala dependencias de PHP:

   ```bash
   composer install
   ```

4. Genera la llave de la aplicación:

   ```bash
   php artisan key:generate
   ```

5. Ejecuta migraciones y seeders:

   ```bash
   php artisan migrate --seed
   ```

6. Instala dependencias de frontend:

   ```bash
   npm install
   ```

7. Levanta el entorno de desarrollo:

   ```bash
   composer dev
   ```

## Docker

El repositorio incluye `Dockerfile` y `docker-compose.yml` para ejecutar la
aplicación y la base de datos en contenedores.

```bash
docker-compose up --build
```

Verifica que las variables de entorno de `.env` coincidan con los servicios
definidos en `docker-compose.yml` antes de ejecutar migraciones o seeders.

## Comandos útiles

```bash
composer dev
php artisan test
php artisan pint
npm run build
php artisan migrate
php artisan db:seed
php artisan tinker
```

## Reglas clave del proyecto

- Los modelos usan singular PascalCase y las tablas plural snake_case.
- La tabla `boletos` usa UUID como llave primaria.
- Las entidades principales como buses, rutas, frecuencias y usuarios usan
  `SoftDeletes`.
- Toda operación de venta o cobro debe ejecutarse dentro de `DB::transaction()`.
- Los permisos y roles se gestionan con Spatie Laravel Permission.

## Documentación

- [Reglas del proyecto](intrucciones.md)
- [Guía de contribución](CONTRIBUTING.md)
- [Guía de uso del sistema](docs/USO_SISTEMA.md)
- [Flujo de QR](docs/FLUJO_QR.md)
- [Integración de descuentos y ventas](docs/INTEGRACION_DESCUENTO_VENTAS.md)
- [Política de seguridad](SECURITY.md)
- [Código de conducta](CODE_OF_CONDUCT.md)
- [Historial de cambios](CHANGELOG.md)

## Calidad

Antes de abrir un Pull Request, ejecuta al menos:

```bash
php artisan pint
php artisan test
npm run build
```

## Licencia

Este proyecto está disponible bajo la licencia MIT. Consulta [LICENSE](LICENSE)
para más información.
