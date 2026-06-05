# Sistema de Venta de Pasajes - Cooperativa Ambato

Plataforma web para la gestión operativa y venta de pasajes de la Cooperativa
de Transportes Ambato (Ecuador). El sistema cubre catálogos de flota, rutas,
frecuencias, ventas en ventanilla, ventas web y validación de boletos mediante
QR para choferes.

## Alcance funcional

- **Catálogos:** categorías de bus, buses y cuentas de usuarios.
- **Operativa:** rutas, frecuencias y hoja de ruta (programación de viajes).
- **Ventanilla:** venta de pasajes, historial, cierres de turno y reembolsos.
- **Web:** carrito de compra, pago web, historial de viajes del cliente.
- **Chofer:** panel principal y validación de boletos por QR.

## Stack tecnológico

- **Backend:** Laravel 11.
- **Frontend:** Livewire 4 + Alpine.js + Tailwind CSS.
- **Build frontend:** Vite.
- **Base de datos:** PostgreSQL.
- **Permisos:** Spatie Laravel Permission.

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 18+ y npm
- PostgreSQL
- (Opcional) Docker para despliegue en la versión 1.0.0

## Instalación local (sin Docker)

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
7. Levanta los assets:
   ```bash
   npm run dev
   ```

## Comandos clave

- `composer dev` - Arranca servidor, colas y Vite en paralelo.
- `php artisan migrate --seed` - Base de datos lista con datos de prueba.
- `npm run dev` - Compila assets en modo desarrollo.

## Docker (v1.0.0)

Según el plan de versión del proyecto, **la v1.0.0 debe ejecutarse con Docker**
(`docker-compose up`). Cuando se publique esa versión, se incluirán el
`Dockerfile` y `docker-compose.yml` correspondientes para levantar app y base
de datos.

## Documentación adicional

- [Guía de uso del sistema](docs/USO_SISTEMA.md)
- [Flujo de QR](docs/FLUJO_QR.md)
- [Integración de descuentos y ventas](docs/INTEGRACION_DESCUENTO_VENTAS.md)
- [Reglas del proyecto](intrucciones.md)
- [Guía de contribución](CONTRIBUTING.md)

## Calidad y pruebas

```bash
php artisan pint
php artisan test
npm run build
```
