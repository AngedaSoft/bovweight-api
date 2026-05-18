<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Animal extends Model
{
    use HasFactory;

    protected $table = 'animales';

    protected $fillable = [
        'finca_id',
        'rebano_id',
        'raza_id',
        'arete_senasa',
        'fecha_asignacion_arete',
        'nombre',
        'fecha_nacimiento_aprox',
        'sexo',
        'estado',
        'motivo_inactivacion',
        'fecha_inactivacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion_arete' => 'date',
            'fecha_nacimiento_aprox' => 'date',
            'fecha_inactivacion' => 'date',
        ];
    }

    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    public function rebano(): BelongsTo
    {
        return $this->belongsTo(Rebano::class);
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class);
    }

    public function ultimoPesaje()
    {
        return $this->hasOne(Pesaje::class)->latestOfMany('fecha');
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class);
    }
}
