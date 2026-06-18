<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre_completo',
        'correo',
        'contrasena_hash',
        'rol',
        'estado',
        'avatar_url',
        'fecha_registro',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'contrasena_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
            'ultimo_acceso' => 'datetime',
            'contrasena_hash' => 'hashed',
        ];
    }

    /**
     * Sanctum / Auth necesita saber el nombre del campo password.
     */
    public function getAuthPassword(): string
    {
        return $this->contrasena_hash;
    }

    /**
     * Override para que Auth use 'correo' como columna de identificacion.
     */
    public function getEmailForVerification(): string
    {
        return $this->correo;
    }

    // ===== Relaciones =====

    public function fincas(): HasMany
    {
        return $this->hasMany(Finca::class, 'propietario_id');
    }

    public function fincasAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(Finca::class, 'finca_veterinario', 'veterinario_id', 'finca_id')->withTimestamps();
    }

    public function fincasAccesibles()
    {
        return match ($this->rol) {
            'administrador' => Finca::query(),
            'propietario'   => Finca::where('propietario_id', $this->id),
            'veterinario'   => Finca::whereIn('id', $this->fincasAsignadas()->pluck('fincas.id')),
            default         => Finca::whereRaw('1=0'),
        };
    }

    public function pesajesRegistrados(): HasMany
    {
        return $this->hasMany(Pesaje::class, 'usuario_id');
    }

    public function transferenciasComoVendedor(): HasMany
    {
        return $this->hasMany(TransferenciaAnimal::class, 'vendedor_id');
    }

    public function transferenciasComoComprador(): HasMany
    {
        return $this->hasMany(TransferenciaAnimal::class, 'comprador_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    // ===== Helpers de roles =====

    public function esPropietario(): bool
    {
        return $this->rol === 'propietario';
    }

    public function esVeterinario(): bool
    {
        return $this->rol === 'veterinario';
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }
}
