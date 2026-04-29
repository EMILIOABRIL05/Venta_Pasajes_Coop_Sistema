<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Frecuencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'frecuencias';

    protected $fillable = [
        'ruta_id',
        'hora_salida',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
