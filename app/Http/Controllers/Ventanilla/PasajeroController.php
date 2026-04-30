<?php

namespace App\Http\Controllers\Ventanilla;

use App\Http\Controllers\Controller;
use App\Models\Pasajero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasajeroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validación de campos básicos mediante Laravel
        $request->validate([
            'cedula'          => 'required|string',
            'nombre_completo' => 'required|string|max:255',
            'edad'            => 'required|integer|min:0|max:120',
        ]);

        // 2. Validación personalizada de la cédula
        if (!$this->validateCedula($request->cedula)) {
            return response()->json([
                'message' => 'La cédula debe contener exactamente 10 caracteres numéricos.',
            ], 422);
        }

        // 3. Creación del pasajero
        $pasajero = Pasajero::create($request->only(['cedula', 'nombre_completo', 'edad']));

        return response()->json([
            'message'  => 'Pasajero registrado correctamente.',
            'pasajero' => $pasajero,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // ─── Métodos privados ─────────────────────────────────────────────────────

    /**
     * Valida que la cédula tenga exactamente 10 caracteres numéricos.
     *
     * @param  string $cedula
     * @return bool
     */
    private function validateCedula(string $cedula): bool
    {
        return (bool) preg_match('/^\d{10}$/', $cedula);
    }
}
