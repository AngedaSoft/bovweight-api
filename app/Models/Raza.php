<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raza extends Model
{
    use HasFactory;

    protected $table = 'razas';

    protected $fillable = [
        'nombre',
        'parametros_morfologicos',
    ];

    protected function casts(): array
    {
        return [
            'parametros_morfologicos' => 'array',
        ];
    }

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
