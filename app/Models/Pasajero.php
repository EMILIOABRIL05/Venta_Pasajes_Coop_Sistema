<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CalculaDescuentoPorEdad;

class Pasajero extends Model
{
    use SoftDeletes, CalculaDescuentoPorEdad;

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

    public function esTerceraEdad(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'tercera_edad';
    }

    public function esNino(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'nino';
    }
}
