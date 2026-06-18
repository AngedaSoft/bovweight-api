<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Finca;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
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
            'contrasena'      => ['required', 'string', 'min:8'],
            'rol'             => ['required', Rule::in(['propietario', 'veterinario'])],
        ]);

        $usuario = User::create([
            'nombre_completo' => $datos['nombre_completo'],
            'correo'          => $datos['correo'],
            'contrasena_hash' => Hash::make($datos['contrasena']),
            'rol'             => $datos['rol'],
            'estado'          => 'activo',
            'fecha_registro'  => now()->toDateString(),
        ]);

        return response()->json(['data' => new UserResource($usuario)], 201);
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
        ]);

        if (isset($datos['contrasena'])) {
            $datos['contrasena_hash'] = Hash::make($datos['contrasena']);
            unset($datos['contrasena']);
        }

        $usuario->update($datos);

        return response()->json(['data' => new UserResource($usuario)]);
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
}
