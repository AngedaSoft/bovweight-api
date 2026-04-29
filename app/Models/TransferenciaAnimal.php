<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaAnimal extends Model
{
    use HasFactory;

    protected $table = 'transferencias_animal';

    protected $fillable = [
        'animal_id',
        'vendedor_id',
        'comprador_id',
        'finca_destino_id',
        'fecha_solicitud',
        'fecha_aceptacion',
        'estado',
        'motivo_rechazo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'fecha_aceptacion' => 'datetime',
        ];
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function comprador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    public function fincaDestino(): BelongsTo
    {
        return $this->belongsTo(Finca::class, 'finca_destino_id');
    }
}
