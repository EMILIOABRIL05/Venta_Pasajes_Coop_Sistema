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
        'cliente_id',
        'total',
        'estado',
        'comprobante',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * La venta pertenece al usuario (cajero/ventanilla) que la registró.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * La venta pertenece a un cliente (usuario web).
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Una venta tiene muchos boletos.
     */
    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    /**
     * Una venta puede tener reembolsos asociados.
     */
    public function reembolsos()
    {
        return $this->hasMany(Reembolso::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
