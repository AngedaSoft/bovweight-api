<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

/**
 * Encapsula la logica de autenticacion (registro, login, emision de tokens)
 * y los flujos de gestion del propio usuario (perfil, contrasena, avatar).
 *
 * SRP: solo se preocupa por credenciales y datos del usuario autenticado.
 * Los controladores delegan en este servicio para evitar acumular logica
 * en el transporte HTTP.
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

    /**
     * Actualizar los datos del perfil de un usuario de forma segura.
     */
    public function actualizarPerfil(User $user, array $datos): User
    {
        $user->fill($datos)->save();
        return $user->fresh();
    }

    /**
     * Cambia la contrasena validando que el usuario conoce la anterior.
     */
    public function cambiarContrasena(User $user, string $actual, string $nueva): bool
    {
        if (! Hash::check($actual, $user->contrasena_hash)) {
            return false;
        }

        return $user->update([
            'contrasena_hash' => Hash::make($nueva),
        ]);
    }

    /**
     * Guarda el archivo de avatar en el disco `public`, actualiza la URL
     * en el modelo y limpia el archivo anterior si existia.
     */
    public function actualizarAvatar(User $user, UploadedFile $archivo): User
    {
        $disco = Storage::disk('public');

        if ($user->avatar_url && $disco->exists($user->avatar_url)) {
            $disco->delete($user->avatar_url);
        }

        $ruta = $archivo->store("avatars/{$user->id}", 'public');

        $user->update(['avatar_url' => $ruta]);

        return $user->fresh();
    }
}
