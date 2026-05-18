<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

/**
 * Encapsula la logica de autenticacion (registro, login, emision de tokens).
 *
 * SRP: solo se preocupa por credenciales y tokens. Los controladores delegan
 * en este servicio para evitar acumular logica en el transporte HTTP.
 */
class AuthService
{
    public function registrar(array $datos): User
    {
        return User::create([
            'nombre_completo' => $datos['nombre_completo'],
            'correo' => $datos['correo'],
            'contrasena_hash' => Hash::make($datos['contrasena']),
            'rol' => $datos['rol'] ?? 'propietario',
            'estado' => 'activo',
            'fecha_registro' => now()->toDateString(),
        ]);
    }

    public function autenticar(string $correo, string $contrasena): User
    {
        $usuario = User::where('correo', $correo)->first();

        if (! $usuario || ! Hash::check($contrasena, $usuario->contrasena_hash)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if ($usuario->estado !== 'activo') {
            throw ValidationException::withMessages([
                'correo' => ['La cuenta no esta activa.'],
            ]);
        }

        $usuario->update(['ultimo_acceso' => now()]);

        return $usuario;
    }

    public function emitirToken(User $usuario, string $nombreDispositivo = 'mobile-app'): NewAccessToken
    {
        return $usuario->createToken($nombreDispositivo, ['*'], now()->addDays(60));
    }
}
