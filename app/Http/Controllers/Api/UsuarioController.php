<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\CredencialesUsuarioMailable;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Largo de la contrasena generada automaticamente al crear un usuario.
     * 12 caracteres alfanumericos sin simbolos: facil de leer/tipear en movil
     * pero con espacio combinatorio suficiente para no ser adivinable.
     */
    private const LARGO_CONTRASENA = 12;

    public function index(): JsonResponse
    {
        $usuarios = User::orderBy('rol')->orderBy('nombre_completo')->get();

        return response()->json(['data' => UserResource::collection($usuarios)]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'correo'          => ['required', 'email', 'unique:users,correo'],
            'rol'             => ['required', Rule::in(['propietario', 'veterinario'])],
        ]);

        $contrasenaPlana = $this->generarContrasenaTemporal();

        $usuario = User::create([
            'nombre_completo' => $datos['nombre_completo'],
            'correo'          => $datos['correo'],
            'contrasena_hash' => Hash::make($contrasenaPlana),
            'rol'             => $datos['rol'],
            'estado'          => 'activo',
            'fecha_registro'  => now()->toDateString(),
        ]);

        // Enviamos el correo de forma sincrona para que el admin sepa al toque
        // si fallo el envio (p.ej. SMTP mal configurado). En produccion con
        // worker activo conviene cambiar `send` por `queue`.
        $envioOk = $this->enviarCredenciales($usuario, $contrasenaPlana);

        return response()->json([
            'data' => new UserResource($usuario),
            'correo_enviado' => $envioOk,
            'mensaje' => $envioOk
                ? "Usuario creado. Se enviaron las credenciales a {$usuario->correo}."
                : "Usuario creado, pero el envio del correo fallo. Revisar logs.",
        ], 201);
    }

    public function show(User $usuario): JsonResponse
    {
        return response()->json(['data' => new UserResource($usuario)]);
    }

    public function update(Request $request, User $usuario): JsonResponse
    {
        $datos = $request->validate([
            'nombre_completo' => ['sometimes', 'string', 'max:255'],
            'correo'          => ['sometimes', 'email', Rule::unique('users', 'correo')->ignore($usuario->id)],
            'estado'          => ['sometimes', Rule::in(['activo', 'inactivo', 'bloqueado'])],
            'contrasena'      => ['sometimes', 'string', 'min:8'],
            'reenviar_contrasena' => ['sometimes', 'boolean'],
        ]);

        $contrasenaNueva = null;
        if (isset($datos['contrasena'])) {
            $contrasenaNueva = $datos['contrasena'];
            $datos['contrasena_hash'] = Hash::make($datos['contrasena']);
            unset($datos['contrasena']);
        } elseif (! empty($datos['reenviar_contrasena'])) {
            // Reset: el admin pidio una contrasena nueva sin escribirla
            $contrasenaNueva = $this->generarContrasenaTemporal();
            $datos['contrasena_hash'] = Hash::make($contrasenaNueva);
        }
        unset($datos['reenviar_contrasena']);

        $usuario->update($datos);

        $correoEnviado = null;
        if ($contrasenaNueva !== null) {
            $correoEnviado = $this->enviarCredenciales($usuario->fresh(), $contrasenaNueva);
        }

        $respuesta = ['data' => new UserResource($usuario->fresh())];
        if ($correoEnviado !== null) {
            $respuesta['correo_enviado'] = $correoEnviado;
        }
        return response()->json($respuesta);
    }

    public function destroy(User $usuario): JsonResponse
    {
        if ($usuario->id === request()->user()->id) {
            return response()->json(['message' => 'No puede eliminarse a sí mismo.'], 422);
        }

        $usuario->delete();

        return response()->json(null, 204);
    }

    public function fincas(User $usuario): JsonResponse
    {
        if ($usuario->rol === 'propietario') {
            $fincas = $usuario->fincas()->get(['id', 'nombre', 'provincia', 'canton']);
        } else {
            $fincas = $usuario->fincasAsignadas()->get(['fincas.id', 'nombre', 'provincia', 'canton']);
        }

        return response()->json(['data' => $fincas]);
    }

    public function asignarFincas(Request $request, User $usuario): JsonResponse
    {
        if ($usuario->rol !== 'veterinario') {
            return response()->json(['message' => 'Solo se pueden asignar fincas a veterinarios.'], 422);
        }

        $request->validate([
            'finca_ids'   => ['required', 'array'],
            'finca_ids.*' => ['integer', 'exists:fincas,id'],
        ]);

        $usuario->fincasAsignadas()->sync($request->finca_ids);

        $fincas = $usuario->fincasAsignadas()->get(['fincas.id', 'nombre', 'provincia', 'canton']);

        return response()->json(['data' => $fincas]);
    }

    /**
     * Genera una contrasena temporal de 12 caracteres alfanumericos.
     * No incluye simbolos para que el usuario pueda tipearla facil en movil.
     */
    private function generarContrasenaTemporal(): string
    {
        return Str::password(
            length: self::LARGO_CONTRASENA,
            letters: true,
            numbers: true,
            symbols: false,
            spaces: false,
        );
    }

    /**
     * Despacha el correo con las credenciales del usuario.
     * Devuelve true si el envio salio sin excepcion, false si hubo error
     * (lo logueamos pero NO rompemos la creacion del usuario en BD).
     */
    private function enviarCredenciales(User $usuario, string $contrasenaPlana): bool
    {
        try {
            Mail::to($usuario->correo)->send(
                new CredencialesUsuarioMailable($usuario, $contrasenaPlana)
            );
            return true;
        } catch (\Throwable $e) {
            Log::error('Fallo envio de credenciales', [
                'usuario_id' => $usuario->id,
                'correo' => $usuario->correo,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
