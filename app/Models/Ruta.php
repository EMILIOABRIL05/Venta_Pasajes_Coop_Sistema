<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rutas';

    protected $fillable = [
        'origen_id',
        'destino_id',
        'precio_base',
        'tiempo_estimado_minutos',
    ];

    public function origen()
    {
        return $this->belongsTo(Parada::class, 'origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(Parada::class, 'destino_id');
    }

    public function frecuencias()
    {
        return $this->hasMany(Frecuencia::class, 'ruta_id');
    }
}
