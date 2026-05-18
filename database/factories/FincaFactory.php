<?php

namespace Database\Factories;

use App\Models\Finca;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Finca>
 */
class FincaFactory extends Factory
{
    protected $model = Finca::class;

    public function definition(): array
    {
        return [
            'propietario_id' => User::factory(),
            'nombre' => 'Hacienda ' . fake()->lastName(),
            'provincia' => fake()->randomElement(['Guanacaste', 'Alajuela', 'San Jose']),
            'canton' => fake()->city(),
            'distrito' => fake()->streetName(),
            'fecha_creacion' => now()->toDateString(),
        ];
    }
}
