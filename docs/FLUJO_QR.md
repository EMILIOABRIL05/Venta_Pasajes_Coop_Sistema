# Flujo QR - Validacion de Boletos

## Objetivo
Registrar el check-in del pasajero cuando el chofer escanea el QR del boleto.

## Componentes Clave
- QR en PDF: el QR contiene el UUID del boleto.
- Endpoint de validacion: `POST /validar-boleto`.
- Registro de check-in: tabla `boleto_validaciones`.

## Seguridad
- Requiere `auth`.
- Requiere rol `chofer|admin`.
- Requiere permiso `scan_qr`.

## Flujo Paso a Paso
1. El cliente descarga su boleto en PDF con QR.
2. El chofer abre el lector QR en la app.
3. El lector envia `uuid` al endpoint `/validar-boleto`.
4. El servidor valida:
   - UUID valido y existente en `boletos`.
   - No existe validacion previa para el mismo boleto.
5. Se crea el registro en `boleto_validaciones`.
6. Se retorna `200 OK` con mensaje de exito.

## Estructura de Registro
Campos usados al crear un check-in:
- `boleto_id` (UUID del boleto)
- `usuario_id` (chofer autenticado)
- `fecha_validacion` (timestamp)
- `estado` (por defecto: `validado`)
- `observaciones` (opcional)

## Respuestas Esperadas
- 200: `Boleto validado exitosamente`
- 409: `Este boleto ya fue utilizado`
- 422: `UUID invalido o boleto no existe`
