<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Frecuencia;

class Boleto extends Model
{
    use SoftDeletes;

    // ─── Configuración UUID ───────────────────────────────────────────────────

    /**
     * El tipo de la llave primaria es string (UUID).
     */
    protected $keyType = 'string';

    /**
     * La llave primaria NO es auto-incremental.
     */
    public $incrementing = false;

    /**
     * Campos asignables masivamente.
     */
    protected $fillable = [
        'id',
        'venta_id',
        'pasajero_id',
        'frecuencia_id',
        'numero_asiento',
        'precio_final',
    ];

    // ─── Generación automática del UUID ──────────────────────────────────────

    /**
     * Al crear un nuevo Boleto, se genera automáticamente el UUID para el 'id'.
     */
    protected static function booted(): void
    {
        static::creating(function (Boleto $boleto) {
            if (empty($boleto->id)) {
                $boleto->id = (string) Str::uuid();
            }
        });
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * El boleto pertenece a una venta.
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * El boleto pertenece a una frecuencia.
     */
    public function frecuencia()
    {
        return $this->belongsTo(Frecuencia::class);
    }

    /**
     * El boleto pertenece a un pasajero.
     */
    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class);
    }

    
}
