<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletoValidacion extends Model
{
    use HasFactory;

    // Indicamos el nombre de la tabla para seguir la convención plural snake_case
    protected $table = 'boleto_validaciones';

    protected $fillable = [
        'boleto_id',
        'usuario_id',
        'fecha_validacion',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_validacion' => 'datetime',
    ];

    // Relación con el Boleto (el cual usa UUID)
    public function boleto()
    {
        return $this->belongsTo('App\\Models\\Boleto', 'boleto_id');
    }

    // Relación con el Usuario (el revisor/administrador)
    public function usuario()
    {
        return $this->belongsTo('App\\Models\\User', 'usuario_id');
    }
}