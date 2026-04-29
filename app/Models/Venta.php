<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    /**
     * Campos asignables masivamente.
     */
    protected $fillable = [
        'user_id',
        'total',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * La venta pertenece al usuario (cajero/ventanilla) que la registró.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Una venta tiene muchos boletos.
     */
    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }
}
