<?php

namespace Database\Factories;

use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Raza>
 */
class RazaFactory extends Factory
{
    protected $model = Raza::class;

    public function definition(): array
    {
        return [
            'nombre' => strtoupper(fake()->unique()->word()),
            'parametros_morfologicos' => ['factor_k' => 10800, 'peso_min' => 150, 'peso_max' => 900],
        ];
    }
}
