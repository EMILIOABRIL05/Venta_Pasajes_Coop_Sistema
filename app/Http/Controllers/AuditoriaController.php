<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCambio;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller
{
    public function index()
    {
        $totalSolicitudes = SolicitudCambio::count();

        $desplegadas = SolicitudCambio::where('estado_pipeline', 'Desplegado')->count();
        $tasaExito = $totalSolicitudes > 0
            ? round(($desplegadas / $totalSolicitudes) * 100, 1)
            : 0;

        $emergencias = SolicitudCambio::where('prioridad', 'Alta')->count();

        $porEstado = SolicitudCambio::select('estado_pipeline', DB::raw('count(*) as total'))
            ->groupBy('estado_pipeline')
            ->pluck('total', 'estado_pipeline')
            ->toArray();

        $equipoReal = [
            'Kevin Velasco',
            'Emilio Abril',
            'Luis Miranda',
            'Anthony Semblantes',
            'Manuel Cusme',
            'Manolo Garcia',
        ];

        $porDesarrollador = SolicitudCambio::select('users.name', DB::raw('count(*) as total'))
            ->join('users', 'solicitudes_cambio.user_id', '=', 'users.id')
            ->whereIn('users.name', $equipoReal)
            ->groupBy('users.name')
            ->pluck('total', 'users.name')
            ->toArray();

        $devLabels = array_keys($porDesarrollador);
        $devData = array_values($porDesarrollador);

        $cambiosRecientes = SolicitudCambio::with(['usuario', 'evaluador', 'reporteTecnico'])
            ->latest()
            ->paginate(10);

        return view('auditoria.dashboard', compact(
            'totalSolicitudes',
            'tasaExito',
            'emergencias',
            'porEstado',
            'devLabels',
            'devData',
            'cambiosRecientes',
        ));
    }
}
