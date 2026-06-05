<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reembolso extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venta_id',
        'monto',
        'motivo',
        'estado',
        'comentarios',
        'fecha_solicitud',
        'fecha_resolucion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_solicitud' => 'datetime',
        'fecha_resolucion' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
