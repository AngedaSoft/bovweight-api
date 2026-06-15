<?php

namespace Database\Factories;

use App\Models\Finca;
use App\Models\Rebano;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rebano>
 */
class RebanoFactory extends Factory
{
    protected $model = Rebano::class;

    public function definition(): array
    {
        return [
            'finca_id' => Finca::factory(),
            'nombre' => 'Lote ' . fake()->bothify('??-###'),
            'proposito' => fake()->randomElement(['terneros', 'vacas_ordeno', 'engorde', 'cria', 'otro']),
            'fecha_creacion' => now()->toDateString(),
        ];
    }
}
