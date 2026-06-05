<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    // ─── Constantes de Canal ───────────────────────────────────────────────────

    const CANAL_WEB = 'web';

    const CANAL_VENTANILLA = 'ventanilla';

    // ─── Constantes de Estado ──────────────────────────────────────────────────

    const ESTADO_PENDIENTE = 'Pendiente';

    const ESTADO_PENDIENTE_VALIDACION = 'Pendiente de Validacion';

    const ESTADO_PAGADA = 'Pagada';

    const ESTADO_CANCELADA = 'Cancelada';

    /**
     * Campos asignables masivamente.
     */
    protected $fillable = [
        'user_id',
        'cliente_id',
        'total',
        'estado',
        'comprobante',
        'canal_venta',
        'fecha_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * La venta pertenece al usuario (cajero/ventanilla) que la registró.
     * Será NULL en ventas web.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias explícito para el vendedor/cajero (para mayor claridad semántica).
     */
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * La venta pertenece a un cliente (usuario web).
     * Será NULL en ventas de ventanilla.
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

    /**
     * Una venta puede tener múltiples pagos registrados.
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Filtra ventas realizadas por canal web.
     */
    public function scopeWeb($query)
    {
        return $query->where('canal_venta', self::CANAL_WEB);
    }

    /**
     * Filtra ventas realizadas por canal ventanilla.
     */
    public function scopeVentanilla($query)
    {
        return $query->where('canal_venta', self::CANAL_VENTANILLA);
    }

    /**
     * Filtra ventas pagadas.
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', self::ESTADO_PAGADA);
    }

    /**
     * Filtra ventas pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    // ─── Métodos de Negocio ──────────────────────────────────────────────────

    /**
     * Indica si la venta fue realizada por canal web.
     */
    public function esWeb(): bool
    {
        return $this->canal_venta === self::CANAL_WEB;
    }

    /**
     * Indica si la venta fue realizada por canal ventanilla.
     */
    public function esVentanilla(): bool
    {
        return $this->canal_venta === self::CANAL_VENTANILLA;
    }

    /**
     * Indica si la venta ya fue pagada.
     */
    public function estaPagada(): bool
    {
        return $this->estado === self::ESTADO_PAGADA;
    }

    /**
     * Marca la venta como pagada y registra la fecha de pago.
     */
    public function marcarPagada(): void
    {
        $this->update([
            'estado' => self::ESTADO_PAGADA,
            'fecha_pago' => now(),
        ]);
    }
}
