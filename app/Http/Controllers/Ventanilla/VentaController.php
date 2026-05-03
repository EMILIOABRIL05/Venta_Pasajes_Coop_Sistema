<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VentaController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * Muestra el listado de rutas disponibles y el mapa de asientos.
     */
    public function index()
    {
        $rutas = Ruta::with(['origen', 'destino'])
            ->orderBy('precio_base')
            ->get();

        return view('ventanilla.index', compact('rutas'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    /**
     * Registra la cabecera de venta y sus boletos en una transacción atómica.
     *
     * Estrategia de resiliencia:
     *  - DB::transaction(..., 2)  → reintenta 2 veces ante deadlocks de InnoDB.
     *  - QueryException           → errores de DB conocidos (FK, constraint, etc.).
     *  - Throwable                → cualquier fallo inesperado (red, PHP fatal, etc.).
     *
     * En ambos casos la transacción hace ROLLBACK automático, garantizando
     * que nunca quede una Venta sin sus Boletos correspondientes.
     */
    public function store(Request $request)
    {
        // ── 1. Validación estricta de entrada ─────────────────────────────────
        $validated = $request->validate([
            'ruta_id'         => ['required', 'integer', 'exists:rutas,id'],
            'pasajero_id'     => ['required', 'integer', 'exists:pasajeros,id'],
            'asientos'        => ['required', 'array', 'min:1', 'max:40'],
            'asientos.*'      => ['required', 'integer', 'between:1,40'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01'],
        ]);

        // Calcular totales fuera de la transacción para evitar cómputo en el lock
        $asientos       = array_unique($validated['asientos']);
        $precioUnitario = (float) $validated['precio_unitario'];
        $total          = round($precioUnitario * count($asientos), 2);

        // ── 2. Transacción atómica ────────────────────────────────────────────
        try {
            /** @var \App\Models\Venta $venta */
            $venta = DB::transaction(function () use ($validated, $asientos, $precioUnitario, $total) {

                // 2a. Cabecera de la venta —————————————————————————————————————
                //     Vincula el cajero autenticado y el total calculado.
                $venta = Venta::create([
                    'user_id' => auth()->id(),  // Cajero/ventanilla autenticado
                    'total'   => $total,
                ]);

                // 2b. Boletos (líneas de venta) ————————————————————————————————
                //     Cada asiento seleccionado genera un Boleto independiente.
                //     El UUID lo genera automáticamente el observer en Boleto::booted().
                foreach ($asientos as $numeroAsiento) {
                    $venta->boletos()->create([
                        'pasajero_id'    => $validated['pasajero_id'],
                        'numero_asiento' => (string) $numeroAsiento,
                        'precio_final'   => $precioUnitario,
                    ]);
                }

                return $venta;

            }, 2); // ← Reintenta hasta 2 veces si InnoDB lanza un deadlock

        } catch (QueryException $e) {
            // Error de base de datos: FK violada, connection drop, timeout, etc.
            Log::error('[Ventanilla] store() — QueryException', [
                'user_id'  => auth()->id(),
                'ruta_id'  => $validated['ruta_id']  ?? null,
                'asientos' => $asientos,
                'total'    => $total,
                'sql'      => $e->getSql(),
                'bindings' => $e->getBindings(),
                'message'  => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error de base de datos al registrar la venta. Intente nuevamente.');

        } catch (Throwable $e) {
            // Fallo inesperado: corte de red, error eléctrico, PHP fatal, etc.
            // La transacción ya hizo ROLLBACK automático en este punto.
            Log::critical('[Ventanilla] store() — Fallo crítico inesperado', [
                'user_id'  => auth()->id(),
                'asientos' => $asientos ?? [],
                'total'    => $total    ?? 0,
                'message'  => $e->getMessage(),
                'file'     => $e->getFile().':'.$e->getLine(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error inesperado. Contacte al administrador del sistema.');
        }

        // ── 3. Respuesta de éxito ─────────────────────────────────────────────
        return redirect()
            ->route('ventanilla.ventas.show', $venta)
            ->with('success', sprintf(
                'Venta #%d registrada correctamente — %d asiento(s) — $%s USD.',
                $venta->id,
                count($asientos),
                number_format($total, 2)
            ));
    }

    // ─── Métodos pendientes de implementar ────────────────────────────────────

    public function create() {}

    public function show(Venta $venta)
    {
        $venta->load('boletos.pasajero');

        return view('ventanilla.ventas.show', compact('venta'));
    }

    public function edit(Venta $venta) {}

    public function update(Request $request, Venta $venta) {}

    public function destroy(Venta $venta) {}
}

