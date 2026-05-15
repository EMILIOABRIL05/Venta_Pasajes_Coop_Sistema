<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fecha',
        'frecuencia_id',
        'bus_id',
        'chofer_user_id',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function frecuencia()
    {
        return $this->belongsTo(Frecuencia::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function chofer()
    {
        return $this->belongsTo(User::class, 'chofer_user_id');
    }

    /**
     * Boletos vendidos para este viaje (a través de su frecuencia).
     */
    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'frecuencia_id', 'frecuencia_id');
    }
}
