<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fotografia extends Model
{
    use HasFactory;

    protected $table = 'fotografias';

    protected $fillable = [
        'pesaje_id',
        'ruta_archivo',
        'fecha_captura',
        'resolucion',
        'es_valida',
        'motivo_invalidez',
    ];

    protected function casts(): array
    {
        return [
            'fecha_captura' => 'datetime',
            'es_valida' => 'boolean',
        ];
    }

    public function pesaje(): BelongsTo
    {
        return $this->belongsTo(Pesaje::class);
    }
}
