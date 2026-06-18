<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenAnimal extends Model
{
    protected $table = 'imagenes_animales';

    protected $fillable = ['animal_id', 'ruta', 'url'];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
