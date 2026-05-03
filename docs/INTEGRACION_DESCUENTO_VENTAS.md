# Integración del descuento por edad en el flujo de venta

Este archivo explica cómo integrar el `DescuentoPorEdad` en el proceso de venta (responsable: Estudiante 4).

Resumen rápido
- La lógica de descuento ya existe en: `app/Support/DescuentoPorEdad.php` y `app/Traits/CalculaDescuentoPorEdad.php`.
- Los modelos `User` y `Pasajero` ya usan el trait y exponen métodos convenientes como `montoDescuentoPorEdad()` y `precioFinalConDescuentoPorEdad()`.

Dónde aplicar el descuento
- Punto recomendado: en el controlador o servicio que crea la venta antes de persistir (`VentaController::store` o un servicio `VentaService`).
- No modificar la validación del formulario: seguir validando `precio_final` si la UI lo envía, pero preferible calcularlo en servidor para evitar manipulaciones por cliente.

Ejemplo de integración (extracto para `VentaController::store`):

```php
use App\Models\Pasajero;
use App\Models\Boleto;
use App\Models\Venta;
use App\Models\Pago;
use Illuminate\Support\Str;
use Illuminate\Support\DB;

// $validated proviene de la validación del request
$pasajero = Pasajero::findOrFail($validated['pasajero_id']);
$frecuencia = Frecuencia::with('ruta')->findOrFail($validated['frecuencia_id']);

// Precio base desde la frecuencia/ruta
$precioBase = $frecuencia->ruta->precio_base;

// Calcular precio final y monto descuento usando los helpers del modelo Pasajero
$montoDescuento = $pasajero->montoDescuentoPorEdad($precioBase);
$precioFinal = $pasajero->precioFinalConDescuentoPorEdad($precioBase);

// Opcional: si el request trae precio_final, comparar y tomar el servidor como fuente de verdad
// if ((float) $validated['precio_final'] !== $precioFinal) { /* rechazar o sobrescribir */ }

DB::transaction(function () use ($validated, $precioFinal, $montoDescuento) {
    $venta = Venta::create([
        'user_id' => auth()->id(),
        'total' => $precioFinal,
    ]);

    Boleto::create([
        'id' => (string) Str::uuid(),
        'venta_id' => $venta->id,
        'pasajero_id' => $validated['pasajero_id'],
        'frecuencia_id' => $validated['frecuencia_id'],
        'numero_asiento' => (string) $validated['numero_asiento'],
        'precio_final' => $precioFinal,
    ]);

    Pago::create([
        'venta_id' => $venta->id,
        'monto' => $precioFinal,
        'fecha' => now(),
        'metodo_pago' => $validated['metodo_pago'] ?? 'efectivo',
        'referencia' => $validated['referencia'] ?? null,
        'observaciones' => 'Pago registrado. Descuento aplicado: ' . $montoDescuento,
    ]);
});
```

Notas y recomendaciones
- La UI actual ya muestra `precio_final` como input; dejarlo por compatibilidad pero validar y preferir el cálculo servidor-side.
- Guardar también el `montoDescuento` en la base (por ejemplo en la tabla `boletos` o `pagos`) si se desea auditar descuentos.
- Para descuentos por discapacidad o reglas especiales, extienda `DescuentoPorEdad` o añada nuevos traits/servicios.

Contacto
- Autor de la implementación base: Estudiante 2 (Manuel Cusme). Para dudas, abrir un issue o comentarlo en el PR.
