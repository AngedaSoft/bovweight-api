<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena_hash' => static::$password ??= Hash::make('password'),
            'rol' => 'propietario',
            'estado' => 'activo',
            'fecha_registro' => now()->toDateString(),
            'remember_token' => Str::random(10),
        ];
    }

    public function administrador(): static
    {
        return $this->state(fn () => ['rol' => 'administrador']);
    }

    public function veterinario(): static
    {
        return $this->state(fn () => ['rol' => 'veterinario']);
    }
}
