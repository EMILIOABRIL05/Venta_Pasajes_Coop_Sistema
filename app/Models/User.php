<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\CalculaDescuentoPorEdad;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, CalculaDescuentoPorEdad;

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
            'password'          => 'hashed',
            'fecha_nacimiento'  => 'date',
        ];
    }

    public function esTerceraEdad(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'tercera_edad';
    }

    public function esNino(): bool
    {
        return $this->tipoDescuentoPorEdad() === 'nino';
    }
}