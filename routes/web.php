<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VentaController; // Modificado por tus compañeros
use App\Http\Controllers\BoletoValidacionController; // Sprint 3 - Luis
use App\Http\Controllers\ReporteController; // Sprint 3 - Luis
use App\Http\Controllers\Ventanilla\PasajeroController;
use App\Livewire\AdminPanel;
use App\Livewire\Catalogos\BusesCrud;
use App\Livewire\Catalogos\CategoriasBusCrud;
use App\Livewire\Web\CarritoCompra;
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

Route::get('/catalogos/categorias-bus', CategoriasBusCrud::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('catalogos.categorias-bus');

Route::get('/catalogos/buses', BusesCrud::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('catalogos.buses');

// Hoja de Ruta (Sprint 2 - Kevin)
Route::get('/hoja-ruta', \App\Livewire\Operativa\HojaRuta::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('operativa.hoja-ruta');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // TAREAS SPRINT 3 - LUIS (Estudiante 6)
    // Descargar boleto en PDF con QR
    Route::get('/ventas/boleto/{id}/descargar', [VentaController::class, 'descargarBoleto'])->name('ventas.boleto.descargar');

    // Validar boleto (Escaneo QR)
    Route::post('/validar-boleto', [BoletoValidacionController::class, 'validar'])->name('validar.boleto');

    // Dashboard de Reportes Administrativos
    Route::get('/admin/reportes', [ReporteController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.reportes');

    // Rutas para el CRUD de Frecuencias (Sprint 3 - Kevin)
    Route::get('/frecuencias', App\Livewire\Operativa\FrecuenciasCrud::class)
        ->middleware(['auth', 'role:admin|oficinista'])
        ->name('frecuencias.index');
});

// ─── Módulo Ventanilla ────────────────────────────────────────────────────────
Route::middleware('auth')
    ->prefix('ventanilla')
    ->name('ventanilla.')
    ->group(function () {
        // Usamos el VentaController que fusionaron tus compañeros
        Route::resource('ventas',     VentaController::class);
        Route::resource('pasajeros', PasajeroController::class);
    });

// ─── Módulo Web (Sprint 3 - Estudiante 5 Anthony) ──────────────────────────────
Route::get('/carrito/{viajeId}', CarritoCompra::class)
    ->middleware(['auth']) 
    ->name('web.carrito');

require __DIR__.'/auth.php';