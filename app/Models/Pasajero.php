<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasajero extends Model
{
    use SoftDeletes;

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
}
