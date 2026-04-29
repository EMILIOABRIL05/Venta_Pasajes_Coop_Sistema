<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletoValidacion extends Model
{
    use HasFactory;

    // Indicamos el nombre de la tabla ya que no sigue el plural estándar de Laravel
    protected $table = 'boleto_validacion';

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
        return $this->belongsTo(Boleto::class, 'boleto_id');
    }

    // Relación con el Usuario (el revisor/administrador)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}