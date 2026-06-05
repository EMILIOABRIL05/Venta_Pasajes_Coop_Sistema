<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Boleto extends Model
{
    use SoftDeletes;

    // ─── Constantes de Estado ──────────────────────────────────────────────────

    const ESTADO_ACTIVO = 'activo';

    const ESTADO_CANCELADO = 'cancelado';

    const ESTADO_USADO = 'usado';

    const ESTADO_NO_SHOW = 'no_show';

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
        'viaje_id',
        'numero_asiento',
        'categoria_asiento',
        'precio_final',
        'estado',
        'fecha_cancelacion',
    ];

    // ─── Generación automática de identificadores ─────────────────────────────

    /**
     * Al crear un nuevo Boleto se generan automáticamente:
     *   · id             → UUID v4 (identificador interno único)
     *   · codigo_reserva → Código alfanumérico legible (identificador de ventanilla)
     *   · estado         → 'activo' por defecto
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

            // Estado por defecto
            if (empty($boleto->estado)) {
                $boleto->estado = self::ESTADO_ACTIVO;
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
        $prefijo = 'AMB-'.now()->year.'-';

        do {
            // 4 caracteres: letras mayúsculas + dígitos (base 36)
            $sufijo = strtoupper(Str::random(4));
            $codigo = $prefijo.$sufijo;
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
     * El boleto pertenece a un viaje concreto (fecha + bus).
     * Permite distinguir asientos por día, no solo por frecuencia.
     */
    public function viaje()
    {
        return $this->belongsTo(Viaje::class);
    }

    /**
     * El boleto pertenece a un pasajero.
     */
    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Filtra boletos activos (válidos para viajar).
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    /**
     * Filtra boletos cancelados.
     */
    public function scopeCancelados($query)
    {
        return $query->where('estado', self::ESTADO_CANCELADO);
    }

    // ─── Métodos de Negocio ──────────────────────────────────────────────────

    /**
     * Indica si el boleto está activo (válido para viajar).
     */
    public function estaActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    /**
     * Indica si el boleto fue cancelado.
     */
    public function estaCancelado(): bool
    {
        return $this->estado === self::ESTADO_CANCELADO;
    }

    /**
     * Indica si el boleto ya fue utilizado (escaneado por chofer).
     */
    public function estaUsado(): bool
    {
        return $this->estado === self::ESTADO_USADO;
    }

    /**
     * Marca el boleto como usado tras escaneo QR exitoso.
     */
    public function marcarComoUsado(): void
    {
        $this->update(['estado' => self::ESTADO_USADO]);
    }

    /**
     * Marca el boleto como cancelado con fecha de cancelación.
     */
    public function marcarComoCancelado(): void
    {
        $this->update([
            'estado' => self::ESTADO_CANCELADO,
            'fecha_cancelacion' => now(),
        ]);
    }
}
