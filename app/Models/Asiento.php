<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asiento extends Model
{
    protected $table = 'asientos';

    protected $fillable = [
        'bus_id',
        'numero',
        'categoria',
    ];

    protected $casts = [
        'bus_id' => 'integer',
        'numero' => 'integer',
        'categoria' => 'string',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function esVip(): bool
    {
        return $this->categoria === 'vip';
    }

    public function esEstandar(): bool
    {
        return $this->categoria !== 'vip';
    }
}