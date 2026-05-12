<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\BoletoValidacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BoletoValidacionController extends Controller
{
    /**
     * Valida un boleto mediante su UUID.
     */
    public function validar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => ['required', 'uuid', 'exists:boletos,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'UUID invalido o boleto no existe',
                'details' => $validator->errors(),
            ], 422);
        }

        $uuid = $validator->validated()['uuid'];

        // Buscar el boleto por UUID
        $boleto = Boleto::find($uuid);

        if (!$boleto) {
            return response()->json(['error' => 'Boleto no encontrado'], 404);
        }

        // Verificar si ya fue validado
        $validacionExistente = BoletoValidacion::where('boleto_id', $boleto->id)->first();

        if ($validacionExistente) {
            return response()->json(['error' => 'Este boleto ya fue utilizado'], 409);
        }

        // Registrar la validación en una transacción
        DB::transaction(function () use ($boleto) {
            BoletoValidacion::create([
                'boleto_id' => $boleto->id,
                'usuario_id' => Auth::id(),
                'fecha_validacion' => now(),
                'estado' => 'validado',
            ]);
        });

        return response()->json(['message' => 'Boleto validado exitosamente'], 200);
    }
}