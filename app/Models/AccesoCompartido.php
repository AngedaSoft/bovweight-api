<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa el permiso de un veterinario (u otro usuario) sobre una finca
 * que no le pertenece. Soporta dos niveles de acceso:
 *  - `lectura`: ver fincas, animales, pesajes, reportes
 *  - `edicion`: ademas puede registrar pesajes y correcciones
 *
 * Una pareja `(finca_id, usuario_id)` es unica (constraint UQ en migracion),
 * asi que para invitar al mismo vet a la misma finca dos veces hay que actualizar
 * la fila existente o eliminarla y volver a crearla.
 */
class AccesoCompartido extends Model
{
    use HasFactory;

    public const TIPO_LECTURA = 'lectura';
    public const TIPO_EDICION = 'edicion';

    protected $table = 'acceso_compartido';

    protected $fillable = [
        'finca_id',
        'usuario_id',
        'fecha_inicio',
        'fecha_fin',
        'tipo_acceso',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'finca_id' => 'integer',
            'usuario_id' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function estaVigente(): bool
    {
        if (! $this->activo) {
            return false;
        }
        if ($this->fecha_fin !== null && $this->fecha_fin->isPast()) {
            return false;
        }
        return true;
    }

    public function permiteEdicion(): bool
    {
        return $this->estaVigente() && $this->tipo_acceso === self::TIPO_EDICION;
    }
}
