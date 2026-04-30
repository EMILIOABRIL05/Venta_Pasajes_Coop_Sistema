<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Pasajero;
use App\Models\Boleto;

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
        return view('pasajeros.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'size:10', 'regex:/^\d{10}$/', 'unique:pasajeros,cedula'],
            'correo' => ['required', 'string', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'max:20'],
        ]);

        $validated['edad'] = $validated['edad'] ?? 0;

        try {
            DB::transaction(function () use ($validated) {
                Pasajero::create($validated);
            });

            return redirect()->route('pasajeros.create')
                ->with('success', 'Pasajero registrado correctamente.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['general' => 'No se pudo guardar el pasajero. Intente nuevamente.']);
        }
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
}
