<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorreccionPeso extends Model
{
    use HasFactory;

    protected $table = 'correcciones_peso';

    protected $fillable = [
        'pesaje_id',
        'usuario_id',
        'peso_original_kg',
        'peso_corregido_kg',
        'motivo',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'peso_original_kg' => 'decimal:2',
            'peso_corregido_kg' => 'decimal:2',
            'fecha' => 'datetime',
        ];
    }

    public function pesaje(): BelongsTo
    {
        return $this->belongsTo(Pesaje::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
