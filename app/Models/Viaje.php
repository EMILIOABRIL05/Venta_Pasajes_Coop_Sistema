<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Viaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fecha',
        'frecuencia_id',
        'bus_id',
        'chofer_user_id',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function frecuencia()
    {
        return $this->belongsTo(Frecuencia::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function chofer()
    {
        return $this->belongsTo(User::class, 'chofer_user_id');
    }

    /**
     * Boletos vendidos para este viaje (directamente por viaje_id).
     */
    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'viaje_id');
    }

    /**
     * Boletos vendidos por canal ventanilla para este viaje.
     */
    public function boletosVentanilla()
    {
        return $this->hasMany(Boleto::class, 'viaje_id')
            ->whereHas('venta', fn ($q) => $q->where('canal_venta', Venta::CANAL_VENTANILLA));
    }

    /**
     * Boletos vendidos por canal web para este viaje.
     */
    public function boletosWeb()
    {
        return $this->hasMany(Boleto::class, 'viaje_id')
            ->whereHas('venta', fn ($q) => $q->where('canal_venta', Venta::CANAL_WEB));
    }

    // ─── Métodos de Consulta ─────────────────────────────────────────────────

    /**
     * Retorna los números de asiento ocupados por canal ventanilla.
     *
     * @return Collection
     */
    public function asientosOcupadosVentanilla()
    {
        return $this->boletosVentanilla()
            ->activos()
            ->pluck('numero_asiento');
    }

    /**
     * Retorna los números de asiento ocupados por canal web.
     *
     * @return Collection
     */
    public function asientosOcupadosWeb()
    {
        return $this->boletosWeb()
            ->activos()
            ->pluck('numero_asiento');
    }

    /**
     * Retorna TODOS los números de asiento ocupados (ambos canales).
     *
     * @return Collection
     */
    public function todosLosAsientosOcupados()
    {
        return $this->boletos()
            ->activos()
            ->pluck('numero_asiento');
    }

    /**
     * Manifiesto completo de pasajeros para este viaje,
     * incluyendo información del canal de venta.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function manifestPasajeros()
    {
        return $this->boletos()
            ->activos()
            ->with(['pasajero', 'venta'])
            ->get()
            ->map(function ($boleto) {
                return (object) [
                    'numero_asiento' => $boleto->numero_asiento,
                    'nombre' => $boleto->pasajero->nombre_completo ?? 'N/A',
                    'cedula' => $boleto->pasajero->cedula ?? 'N/A',
                    'edad' => $boleto->pasajero->edad ?? 'N/A',
                    'canal_venta' => $boleto->venta->canal_venta ?? 'N/A',
                    'precio_final' => $boleto->precio_final,
                    'boleto_id' => $boleto->id,
                ];
            });
    }
}
