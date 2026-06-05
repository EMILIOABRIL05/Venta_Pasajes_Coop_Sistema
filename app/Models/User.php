<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\UserCreated;
use App\Traits\CalculaDescuentoPorEdad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use CalculaDescuentoPorEdad, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $dispatchesEvents = [
        'created' => UserCreated::class,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'cedula',
        'telefono',
        'fecha_nacimiento',
        'tipo_usuario',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * Ventas donde este usuario actuó como cajero/ventanilla (user_id).
     */
    public function ventasCajero()
    {
        return $this->hasMany(Venta::class, 'user_id');
    }

    /**
     * Ventas donde este usuario es el comprador web (cliente_id).
     */
    public function ventasWeb()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    // ─── Métodos de Negocio ──────────────────────────────────────────────────

    public function esTerceraEdad(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'tercera_edad';
    }

    public function esNino(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'nino';
    }

    /**
     * Indica si el usuario tiene rol de cliente (comprador web).
     */
    public function esCliente(): bool
    {
        return $this->hasRole('cliente');
    }

    /**
     * Indica si el usuario tiene rol de oficinista (vendedor ventanilla).
     */
    public function esOficinista(): bool
    {
        return $this->hasRole('oficinista');
    }
}
