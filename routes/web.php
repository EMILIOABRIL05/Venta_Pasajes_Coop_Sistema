<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\BoletoValidacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Ventanilla\PasajeroController;
use App\Livewire\AdminPanel;
use App\Livewire\Catalogos\BusesCrud;
use App\Livewire\Catalogos\CategoriasBusCrud;
use App\Livewire\Catalogos\CuentasCrud;
use App\Livewire\Chofer\PanelPrincipal;
use App\Livewire\Web\CarritoCompra;
use App\Livewire\Web\PagoWeb;
use App\Livewire\Web\MisViajes;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Panel de administración (Sprint 1 - Emilio)
Route::get('/admin', AdminPanel::class)
    ->middleware(['auth', 'role:admin'])
    ->name('admin.panel');

Route::get('/admin/gestion-reembolsos', \App\Livewire\Admin\GestionReembolsos::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('admin.gestion-reembolsos');

Route::get('/catalogos/categorias-bus', CategoriasBusCrud::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('catalogos.categorias-bus');

Route::get('/catalogos/buses', BusesCrud::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('catalogos.buses');

Route::get('/catalogos/cuentas', CuentasCrud::class)
    ->middleware(['auth', 'role:admin'])
    ->name('catalogos.cuentas');

// Hoja de Ruta (Sprint 2 - Kevin)
Route::get('/hoja-ruta', \App\Livewire\Operativa\HojaRuta::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('operativa.hoja-ruta');

// Dashboard Chofer (Sprint 4 - Luis)
Route::get('/chofer/dashboard', PanelPrincipal::class)
    ->middleware(['auth', 'role:chofer|admin'])
    ->name('chofer.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // TAREAS SPRINT 3 - LUIS (Estudiante 6)
    // Descargar boleto en PDF con QR
    Route::get('/ventas/boleto/{id}/descargar', [VentaController::class, 'descargarBoleto'])->name('ventas.boleto.descargar');

    // Resumen del turno (Manolo)
    Route::get('/ventas/resumen-turno', [VentaController::class, 'resumenTurno'])
        ->middleware('role:oficinista|admin')
        ->name('ventas.resumen-turno');

    // Cierre de turno (Manolo - Sprint 4)
    Route::get('/ventas/cierre-turno', [VentaController::class, 'cierreTurno'])
        ->middleware('role:oficinista|admin')
        ->name('ventas.cierre-turno');
    Route::post('/ventas/cierre-turno', [VentaController::class, 'storeCierre'])
        ->middleware('role:oficinista|admin')
        ->name('ventas.cierre-turno.store');

    // Validar boleto (Escaneo QR)
    Route::post('/validar-boleto', [BoletoValidacionController::class, 'validar'])
        ->middleware(['role:chofer|admin', 'permission:scan_qr'])
        ->name('validar.boleto');

    // Dashboard de Reportes Administrativos
    Route::get('/admin/reportes', [ReporteController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.reportes');

    // Rutas para el CRUD de Frecuencias (Sprint 3 - Kevin)
    Route::get('/frecuencias', App\Livewire\Operativa\FrecuenciasCrud::class)
        ->middleware(['auth', 'role:admin|oficinista'])
        ->name('frecuencias.index');
});

// ─── Módulo Ventanilla ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:oficinista|admin'])
    ->prefix('ventanilla')
    ->name('ventanilla.')
    ->group(function () {

        Route::get('/historial', \App\Livewire\Ventanilla\HistorialVentas::class)->name('historial');
        Route::get('/pasajeros/buscar/{cedula}', [PasajeroController::class, 'buscarPorCedula'])->name('pasajeros.buscar');
        Route::resource('ventas', \App\Http\Controllers\Ventanilla\VentaController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/ventas/boleto/{id}/anular', [\App\Http\Controllers\Ventanilla\VentaController::class, 'anularBoleto'])->name('ventas.boleto.anular');
        Route::resource('pasajeros', PasajeroController::class)->only(['store', 'show', 'destroy']);

        // Cierre de turno (Manolo - Sprint 4)
        Route::get('/cierre', [\App\Http\Controllers\Ventanilla\VentaController::class, 'cierreTurno'])
            ->name('cierre');

        Route::post('/cierre', [\App\Http\Controllers\Ventanilla\VentaController::class, 'storeCierre'])
            ->name('cierre.store');

        // Reporte PDF de cierre (Manolo - Sprint 4)
        Route::get('/cierre/reporte-pdf', [\App\Http\Controllers\Ventanilla\VentaController::class, 'reportePdf'])
            ->name('reporte-pdf');

        // Exportar Excel de cierre (Manolo - Sprint 4)
        Route::get('/cierre/exportar-excel', [\App\Http\Controllers\Ventanilla\VentaController::class, 'exportarExcel'])
            ->name('exportar-excel');
    });


// ─── Módulo Web (Sprint 3 - Estudiante 5 Anthony) ──────────────────────────────
Route::get('/carrito/{viajeId}', CarritoCompra::class)
    ->middleware(['auth']) 
    ->name('web.carrito');

// Solicitud de Reembolso (Público)
Route::get('/solicitud-reembolso', \App\Livewire\Web\SolicitudReembolso::class)
    ->name('solicitud.reembolso');

// Pago Web y Historial (Anthony)
Route::get('/pago/{ventaId}', PagoWeb::class)->middleware(['auth'])->name('pago');
Route::get('/mis-viajes', MisViajes::class)->middleware(['auth'])->name('mis-viajes');

require __DIR__.'/auth.php';