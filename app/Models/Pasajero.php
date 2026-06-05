<?php

namespace App\Models;

use App\Traits\CalculaDescuentoPorEdad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasajero extends Model
{
    use CalculaDescuentoPorEdad, SoftDeletes;

    /**
     * Campos asignables masivamente.
     */
    protected $fillable = [
        'cedula',
        'nombre_completo',
        'edad',
        'correo',
        'telefono',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * Un pasajero puede tener muchos boletos.
     */
    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    /**
     * Vincula el pasajero con su cuenta de usuario web (si existe),
     * buscando por número de cédula.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'cedula', 'cedula');
    }

    /**
     * Indica si este pasajero tiene una cuenta de usuario vinculada.
     */
    public function tieneCuenta(): bool
    {
        return $this->user()->exists();
    }

    // ─── Métodos de Negocio ──────────────────────────────────────────────────

    public function esTerceraEdad(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'tercera_edad';
    }

    public function esNino(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'nino';
    }
}
