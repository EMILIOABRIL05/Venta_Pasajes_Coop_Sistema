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
        'codigo_reserva',   // Identificador legible para ventanilla (AMB-YEAR-XXXX)
        'venta_id',
        'pasajero_id',
        'frecuencia_id',
        'numero_asiento',
        'precio_final',
        'estado',
        'fecha_cancelacion',
    ];

    // ─── Generación automática de identificadores ─────────────────────────────

    /**
     * Al crear un nuevo Boleto se generan automáticamente:
     *   · id             → UUID v4 (identificador interno único)
     *   · codigo_reserva → Código alfanumérico legible (identificador de ventanilla)
     */
    protected static function booted(): void
    {
        static::creating(function (Boleto $boleto) {
            // UUID interno
            if (empty($boleto->id)) {
                $boleto->id = (string) Str::uuid();
            }

            // Código de reserva legible
            if (empty($boleto->codigo_reserva)) {
                $boleto->codigo_reserva = static::generarCodigoReserva();
            }
        });
    }

    /**
     * Genera un código de reserva único con formato: AMB-{AÑO}-{4 chars}.
     *
     * Ejemplo: AMB-2026-X4K9
     *
     * El bucle do-while garantiza unicidad real en BD ante la (muy baja)
     * probabilidad de colisión en el sufijo aleatorio.
     *
     * Espacio de colisión: 36^4 = 1 679 616 combinaciones por año.
     */
    private static function generarCodigoReserva(): string
    {
        $prefijo = 'AMB-' . now()->year . '-';

        do {
            // 4 caracteres: letras mayúsculas + dígitos (base 36)
            $sufijo = strtoupper(Str::random(4));
            $codigo = $prefijo . $sufijo;
        } while (static::withTrashed()->where('codigo_reserva', $codigo)->exists());

        return $codigo;
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

