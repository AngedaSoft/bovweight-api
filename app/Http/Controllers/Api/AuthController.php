<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $usuario = $this->auth->registrar($request->validated());
        $token = $this->auth->emitirToken($usuario, 'mobile-app');

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'user' => new UserResource($usuario),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $usuario = $this->auth->autenticar($datos['correo'], $datos['contrasena']);
        $token = $this->auth->emitirToken($usuario, $request->input('dispositivo', 'mobile-app'));

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'user' => new UserResource($usuario),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesion cerrada correctamente.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * PATCH /api/auth/me
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'nombre_completo' => ['sometimes', 'required', 'string', 'max:255'],
            'correo' => [
                'sometimes', 
                'required', 
                'email', 
                'max:255', 
                Rule::unique('users', 'correo')->ignore($user->id)
            ],
        ]);
        
        $usuarioActualizado = $this->auth->actualizarPerfil($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'user' => new UserResource($usuarioActualizado),
        ]);
    }

    /**
     * POST /api/auth/cambiar-contrasena
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'nueva_contrasena' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $exito = $this->auth->cambiarContrasena(
            $request->user(),
            $request->input('contrasena_actual'),
            $request->input('nueva_contrasena')
        );

        if (!$exito) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contraseña modificada correctamente.'
        ]);
    }
}