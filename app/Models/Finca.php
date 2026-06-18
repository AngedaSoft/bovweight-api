<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finca extends Model
{
    use HasFactory;

    protected $fillable = [
        'propietario_id',
        'nombre',
        'provincia',
        'canton',
        'distrito',
        'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'date',
        ];
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }

    public function rebanos(): HasMany
    {
        return $this->hasMany(Rebano::class);
    }

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    public function accesosCompartidos(): HasMany
    {
        return $this->hasMany(AccesoCompartido::class);
    }

    /**
     * Permisos vigentes (activos y dentro de su rango de fechas) sobre esta finca.
     */
    public function accesosVigentes(): HasMany
    {
        return $this->hasMany(AccesoCompartido::class)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>', now());
            });
    }

    public function permiteAccesoA(User $usuario, string $tipoRequerido = AccesoCompartido::TIPO_LECTURA): bool
    {
        if ($usuario->id === $this->propietario_id) {
            return true;
        }
        $acceso = $this->accesosVigentes()->where('usuario_id', $usuario->id)->first();
        if ($acceso === null) {
            return false;
        }
        if ($tipoRequerido === AccesoCompartido::TIPO_EDICION) {
            return $acceso->tipo_acceso === AccesoCompartido::TIPO_EDICION;
        }
        return true;
    }
}
