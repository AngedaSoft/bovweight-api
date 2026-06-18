<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function veterinarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'finca_veterinario', 'finca_id', 'veterinario_id')->withTimestamps();
    }
}
