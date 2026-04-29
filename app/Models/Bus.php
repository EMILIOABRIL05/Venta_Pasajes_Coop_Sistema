<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use SoftDeletes;

    protected $table = 'buses';

    protected $fillable = [
        'categoria_bus_id',
        'placa',
        'marca_chasis',
        'carroceria',
        'anio',
        'foto',
        'numero_asientos',
        'mapa_asientos',
        'estado',
    ];

    protected $casts = [
        'mapa_asientos' => 'array',
    ];

    // Relación: un bus pertenece a una categoría
    public function categoria()
    {
        return $this->belongsTo(CategoriaBus::class, 'categoria_bus_id');
    }

    // Scope útil: solo buses disponibles
    public function scopeDisponible($query)
    {
        return $query->where('estado', 'disponible');
    }
}