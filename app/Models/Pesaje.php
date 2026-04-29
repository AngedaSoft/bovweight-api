<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'usuario_id',
        'fecha',
        'peso_estimado_kg',
        'rango_confianza_kg',
        'fue_corregido',
        'peso_corregido_kg',
        'tipo',
        'modelo_ia_version',
        'tiempo_procesamiento_seg',
        'estado_procesamiento',
        'formula_zootecnica',
        'es_offline',
        'perimetro_toracico_cm',
        'largo_cuerpo_cm',
        'fecha_medicion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'peso_estimado_kg' => 'decimal:2',
            'rango_confianza_kg' => 'decimal:2',
            'peso_corregido_kg' => 'decimal:2',
            'perimetro_toracico_cm' => 'decimal:2',
            'largo_cuerpo_cm' => 'decimal:2',
            'fue_corregido' => 'boolean',
            'es_offline' => 'boolean',
            'fecha_medicion' => 'date',
        ];
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function fotografias(): HasMany
    {
        return $this->hasMany(Fotografia::class);
    }

    public function correcciones(): HasMany
    {
        return $this->hasMany(CorreccionPeso::class);
    }

    public function pesoFinal(): float
    {
        return $this->fue_corregido && $this->peso_corregido_kg
            ? (float) $this->peso_corregido_kg
            : (float) $this->peso_estimado_kg;
    }
}
