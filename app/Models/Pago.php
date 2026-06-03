<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venta_id',
        'monto',
        'fecha',
        'metodo_pago',
        'referencia',
        'observaciones',
        'estado',
        'codigo_autorizacion',
        'validado_at',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'validado_at' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
