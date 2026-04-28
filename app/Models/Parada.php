<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Parada extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'paradas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nombre',
        'ciudad',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function rutasOrigen()
    {
        return $this->hasMany(Ruta::class, 'origen_id');
    }

    public function rutasDestino()
    {
        return $this->hasMany(Ruta::class, 'destino_id');
    }
}
