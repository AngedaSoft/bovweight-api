<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    public function definition(): array
    {
        return [
            'finca_id' => Finca::factory(),
            'raza_id' => Raza::factory(),
            'arete_senasa' => 'CRI-' . fake()->unique()->numberBetween(1000, 999999),
            'sexo' => fake()->randomElement(['macho', 'hembra']),
            'estado' => 'activo',
            'fecha_asignacion_arete' => now()->subMonths(fake()->numberBetween(1, 24))->toDateString(),
        ];
    }
}
