<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Muestra el dashboard de reportes.
     */
    public function index()
    {
        // Total recaudado hoy
        $totalRecaudado = Pago::whereDate('fecha', now()->toDateString())->sum('monto');

        // Boletos vendidos hoy agrupados por ruta
        $boletosPorRuta = DB::table('boletos')
            ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
            ->join('frecuencias', 'boletos.frecuencia_id', '=', 'frecuencias.id')
            ->join('rutas', 'frecuencias.ruta_id', '=', 'rutas.id')
            ->join('paradas as origen', 'rutas.origen_id', '=', 'origen.id')
            ->join('paradas as destino', 'rutas.destino_id', '=', 'destino.id')
            ->whereDate('ventas.created_at', now()->toDateString())
            ->selectRaw("CONCAT(origen.nombre, ' - ', destino.nombre) as ruta, COUNT(*) as total")
            ->groupBy('ruta')
            ->orderBy('total', 'desc')
            ->get();

        return view('reportes.index', compact('totalRecaudado', 'boletosPorRuta'));
    }
}
