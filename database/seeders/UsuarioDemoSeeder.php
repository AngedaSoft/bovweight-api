<?php

namespace Database\Seeders;

use App\Models\Finca;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['correo' => 'admin@bovweight.local'],
            [
                'nombre_completo' => 'Administrador BovWeight',
                'contrasena_hash' => Hash::make('admin12345'),
                'rol' => 'administrador',
                'estado' => 'activo',
                'fecha_registro' => now()->toDateString(),
            ],
        );

        $propietario = User::firstOrCreate(
            ['correo' => 'ganadero@bovweight.local'],
            [
                'nombre_completo' => 'Don Juan Ganadero',
                'contrasena_hash' => Hash::make('ganadero1'),
                'rol' => 'propietario',
                'estado' => 'activo',
                'fecha_registro' => now()->toDateString(),
            ],
        );

        $finca = Finca::firstOrCreate(
            ['nombre' => 'Hacienda Los Llanos', 'propietario_id' => $propietario->id],
            [
                'provincia' => 'Guanacaste',
                'canton' => 'Liberia',
                'distrito' => 'Liberia',
                'fecha_creacion' => now()->toDateString(),
            ],
        );

        $brahman = Raza::where('nombre', 'BRAHMAN')->first();

        if ($finca->animales()->count() === 0 && $brahman) {
            $finca->animales()->createMany([
                [
                    'raza_id' => $brahman->id,
                    'arete_senasa' => 'CRI-0001',
                    'sexo' => 'macho',
                    'estado' => 'activo',
                    'fecha_asignacion_arete' => now()->subYear()->toDateString(),
                ],
                [
                    'raza_id' => $brahman->id,
                    'arete_senasa' => 'CRI-0002',
                    'sexo' => 'hembra',
                    'estado' => 'activo',
                    'fecha_asignacion_arete' => now()->subMonths(8)->toDateString(),
                ],
            ]);
        }

        $this->command?->info(sprintf('Admin: admin@bovweight.local / admin12345'));
        $this->command?->info(sprintf('Ganadero: ganadero@bovweight.local / ganadero1'));
        unset($admin);
    }
}
