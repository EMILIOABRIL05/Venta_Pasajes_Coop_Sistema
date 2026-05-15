<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Models\Pasajero;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PasajeroController extends Controller
{


    // ─── Store ────────────────────────────────────────────────────────────────

    /**
     * Registra un pasajero nuevo o recupera el existente si la cédula ya está
     * en la base de datos (activo o borrado lógicamente).
     *
     * Flujo de unicidad (dos capas):
     *   1. Capa Laravel  → unique:pasajeros,cedula ignora soft-deletes por defecto.
     *   2. Capa BD       → unique constraint en la columna `cedula` (SQLSTATE 23000).
     *
     * Si la BD lanza la excepción de clave duplicada, en lugar de rechazar la
     * petición se recupera el registro existente con withTrashed() para cubrir
     * también el caso de registros eliminados lógicamente.
     *
     * Respuesta tipada con el campo `status`:
     *   · 'created'  → nuevo pasajero insertado  (HTTP 201)
     *   · 'restored' → registro soft-deleted reactivado (HTTP 200)
     *   · 'existing' → pasajero activo recuperado sin modificar (HTTP 200)
     */
    public function store(Request $request): JsonResponse
    {
        // ── 1. Validación de entrada ──────────────────────────────────────────
        //    unique:pasajeros,cedula valida unicidad a nivel de Laravel.
        //    La segunda capa (BD) actúa como red de seguridad ante condiciones
        //    de carrera (dos cajeros registrando la misma cédula en paralelo).
        $datosValidados = $request->validate([
            'cedula'          => [
                'required',
                'string',
                'regex:/^\d{10}$/',                     // Formato: exactamente 10 dígitos
                'unique:pasajeros,cedula',              // Unicidad en tabla activa
            ],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'edad'            => ['required', 'integer', 'min:0', 'max:120'],
        ], [
            'cedula.regex'  => 'La cédula debe contener exactamente 10 dígitos numéricos.',
            'cedula.unique' => 'Esta cédula ya está registrada.',          // mensaje claro
        ]);

        // ── 2. Intento de creación ────────────────────────────────────────────
        //    Aunque la validación anterior cubre el caso normal, un race-condition
        //    entre dos requests simultáneos puede esquivarla. Por eso envolvemos
        //    el create() en try/catch sobre QueryException (SQLSTATE 23000).
        try {
            $pasajero = Pasajero::create([
                'cedula'          => $datosValidados['cedula'],
                'nombre_completo' => $datosValidados['nombre_completo'],
                'edad'            => $datosValidados['edad'],
            ]);

            return response()->json([
                'status'   => 'created',
                'message'  => 'Pasajero registrado correctamente.',
                'pasajero' => $pasajero,
            ], 201);

        } catch (QueryException $excepcion) {
            // Solo manejamos violación de unicidad (SQLSTATE 23000).
            // Cualquier otro error de BD se re-lanza para no silenciarlo.
            if ($excepcion->getCode() !== '23000') {
                Log::error('[PasajeroController@store] QueryException inesperada', [
                    'cedula'  => $datosValidados['cedula'],
                    'sql'     => $excepcion->getSql(),
                    'message' => $excepcion->getMessage(),
                ]);
                throw $excepcion;
            }
        }

        // ── 3. Recuperación del registro existente ────────────────────────────
        //    withTrashed() garantiza que también encontremos registros con
        //    soft-delete activo (deleted_at IS NOT NULL).
        $pasajero = Pasajero::withTrashed()
            ->where('cedula', $datosValidados['cedula'])
            ->firstOrFail();

        // ── 3a. Registro borrado lógicamente → restaurar y actualizar ─────────
        if ($pasajero->trashed()) {
            $pasajero->restore();
            $pasajero->update([
                'nombre_completo' => $datosValidados['nombre_completo'],
                'edad'            => $datosValidados['edad'],
            ]);

            return response()->json([
                'status'   => 'restored',
                'message'  => 'El pasajero fue reactivado en el sistema con los datos actualizados.',
                'pasajero' => $pasajero->fresh(),
            ], 200);
        }

        // ── 3b. Registro activo → devolver sin modificar ──────────────────────
        //    No sobreescribimos nombre/edad para respetar el historial del cliente.
        return response()->json([
            'status'   => 'existing',
            'message'  => 'El pasajero ya existe y fue recuperado para continuar la venta.',
            'pasajero' => $pasajero,
        ], 200);
    }

    // ─── Show / Edit / Update / Destroy ──────────────────────────────────────

    /**
     * Muestra los datos de un pasajero específico, incluyendo sus boletos.
     *
     * @param  \App\Models\Pasajero  $pasajero
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Pasajero $pasajero): JsonResponse
    {
        return response()->json($pasajero->load('boletos'));
    }



    /**
     * Realiza un borrado lógico (soft delete) del pasajero.
     *
     * @param  \App\Models\Pasajero  $pasajero
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Pasajero $pasajero)
    {
        $pasajero->delete(); // SoftDelete
        return response()->json(['message' => 'Pasajero eliminado.'], 200);
    }
}

