# Guía de uso del sistema

Esta guía resume cómo operar los módulos principales del sistema de venta de
pasajes y qué roles intervienen en cada flujo.

## Roles disponibles

El seeder `TestUsersSeeder` crea usuarios de prueba con los siguientes roles:

- **Admin:** `admin@cooperativa.test` / `Admin12345!`
- **Oficinista:** `oficinista@cooperativa.test` / `Oficina12345!`
- **Chofer:** `chofer@cooperativa.test` / `Chofer12345!`

> Puedes cambiar estas credenciales en el seeder o crear nuevos usuarios desde
> el módulo de cuentas.

## Flujo operativo recomendado

1. **Catálogos (Admin / Oficinista)**
   - Registra categorías y buses en `/catalogos/categorias-bus` y
     `/catalogos/buses`.
   - Crea cuentas de usuarios en `/catalogos/cuentas` (solo Admin).

2. **Operativa (Admin / Oficinista)**
   - Define rutas y frecuencias.
   - Construye la hoja de ruta en `/hoja-ruta` para programar viajes.

3. **Ventanilla (Oficinista)**
   - Registra ventas en `/ventanilla/ventas`.
   - Consulta historial en `/ventanilla/historial`.
   - Ejecuta cierres de turno y exporta reportes desde `/ventanilla/cierre`.
   - Gestiona reembolsos desde `/admin/gestion-reembolsos` si tienes permiso.

4. **Web (Cliente autenticado)**
   - Reserva asientos con el carrito en `/carrito/{viajeId}`.
   - Completa el pago en `/pago/{ventaId}`.
   - Revisa viajes en `/mis-viajes`.

5. **Chofer**
   - Ingresa al panel en `/chofer/dashboard`.
   - Valida boletos con QR mediante la acción `/validar-boleto`.

## Reportes y documentos

- **Resumen y cierre de turno:** accesible desde ventanilla.
- **PDF del boleto:** se descarga desde el módulo de ventas.
- **Reportes administrativos:** disponibles en `/admin/reportes`.

## Referencias útiles

- [Flujo de QR](FLUJO_QR.md)
- [Integración de descuentos y ventas](INTEGRACION_DESCUENTO_VENTAS.md)
