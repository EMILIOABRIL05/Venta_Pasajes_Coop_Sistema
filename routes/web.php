<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Ventanilla\PasajeroController;
use App\Http\Controllers\Ventanilla\VentaController;
use App\Livewire\AdminPanel;
use App\Livewire\Catalogos\BusesCrud;
use App\Livewire\Catalogos\CategoriasBusCrud;
use Illuminate\Support\Facades\Route;

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

// CRUD de Frecuencias (Sprint 3 - Kevin)
Route::get('/frecuencias', \App\Livewire\Operativa\FrecuenciasCrud::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('operativa.frecuencias');    

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas para el CRUD de Frecuencias
    Route::get('/frecuencias', App\Livewire\Operativa\FrecuenciasCrud::class)->middleware(['auth', 'permission:manage_frecuencias|role:admin'])->name('frecuencias.index');
});

// ─── Módulo Ventanilla ────────────────────────────────────────────────────────
Route::middleware('auth')
    ->prefix('ventanilla')
    ->name('ventanilla.')
    ->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index'); // /ventanilla
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::resource('ventas',    VentaController::class);
        Route::resource('pasajeros', PasajeroController::class);
    });

require __DIR__.'/auth.php';
