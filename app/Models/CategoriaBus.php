<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaBus extends Model
{
    use SoftDeletes;

    protected $table = 'categorias_bus';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación: una categoría tiene muchos buses
    public function buses()
    {
        return $this->hasMany(Bus::class, 'categoria_bus_id');
    }
}