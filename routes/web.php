<?php

use App\Http\Controllers\ProfileController;
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
    ->middleware(['auth', 'role:admin'])
    ->name('catalogos.categorias-bus');

Route::get('/catalogos/buses', BusesCrud::class)
    ->middleware(['auth', 'role:admin|oficinista'])
    ->name('catalogos.buses');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
