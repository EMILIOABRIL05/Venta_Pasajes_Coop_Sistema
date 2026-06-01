<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaAsiento extends Model
{
    use SoftDeletes;

    protected $table = 'categorias_asiento';

    protected $fillable = [
        'nombre',
        'recargo',
        'es_default',
        'descripcion',
    ];

    protected $casts = [
        'recargo' => 'decimal:2',
        'es_default' => 'boolean',
    ];
}
