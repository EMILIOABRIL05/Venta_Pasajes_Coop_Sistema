<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\BoletoValidacionController;
use App\Http\Controllers\ReporteController;
use App\Livewire\Catalogos\BusesCrud;
use App\Livewire\Catalogos\CategoriasBusCrud;
use Illuminate\Support\Facades\Route;
use App\Livewire\AdminPanel;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Panel de administración (ejemplo protegido por rol via Livewire)
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

    // Descargar boleto en PDF
    Route::get('/ventas/boleto/{id}/descargar', [VentaController::class, 'descargarBoleto'])->name('ventas.boleto.descargar');

    // Validar boleto
    Route::post('/validar-boleto', [BoletoValidacionController::class, 'validar'])->name('validar.boleto');

    // Reportes
    Route::get('/admin/reportes', [ReporteController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.reportes');

    // Rutas para el CRUD de Frecuencias
    Route::get('/frecuencias', App\Livewire\Operativa\FrecuenciasCrud::class)->middleware(['auth', 'permission:manage_frecuencias|role:admin'])->name('frecuencias.index');
});

require __DIR__.'/auth.php';
