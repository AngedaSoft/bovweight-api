<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rebano extends Model
{
    use HasFactory;

    protected $table = 'rebanos';

    protected $fillable = [
        'finca_id',
        'nombre',
        'proposito',
        'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'date',
        ];
    }

    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class);
    }

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
