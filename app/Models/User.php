<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

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

    // Helper: saber si es tercera edad (>=65) para descuento
    public function esTerceraEdad(): bool
    {
        if (!$this->fecha_nacimiento) return false;
        return $this->fecha_nacimiento->age >= 65;
    }

    // Helper: saber si es niño (<5 años) para descuento
    public function esNino(): bool
    {
        if (!$this->fecha_nacimiento) return false;
        return $this->fecha_nacimiento->age < 5;
    }
}