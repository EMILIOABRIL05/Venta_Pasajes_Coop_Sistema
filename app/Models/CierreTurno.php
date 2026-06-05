<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CierreTurno extends Model
{
    use SoftDeletes;

    protected $table = 'cierres_turno';

    protected $fillable = [
        'user_id',
        'fecha',
        'total_bruto',
        'total_reembolsos',
        'total_neto',
        'total_boletos',
    ];

    protected $casts = [
        'fecha'            => 'date',
        'total_bruto'      => 'decimal:2',
        'total_reembolsos' => 'decimal:2',
        'total_neto'       => 'decimal:2',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * El cierre pertenece al cajero que lo generó.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Query scopes ─────────────────────────────────────────────────────────

    /**
     * Verifica si ya existe un cierre para un usuario en una fecha dada.
     */
    public static function existeParaHoy(int $userId, string $fecha): bool
    {
        return static::where('user_id', $userId)
            ->where('fecha', $fecha)
            ->exists();
    }
}
