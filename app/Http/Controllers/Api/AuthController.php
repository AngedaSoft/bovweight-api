<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $usuario = $this->auth->actualizarPerfil($request->user(), $request->validated());

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user' => new UserResource($usuario),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $exito = $this->auth->cambiarContrasena(
            $request->user(),
            (string) $request->input('contrasena_actual'),
            (string) $request->input('nueva_contrasena'),
        );

        if (! $exito) {
            return response()->json([
                'message' => 'La contrasena actual es incorrecta.',
            ], 422);
        }

        return response()->json([
            'message' => 'Contrasena modificada correctamente.',
        ]);
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $usuario = $this->auth->actualizarAvatar(
            $request->user(),
            $request->file('avatar'),
        );

        return response()->json([
            'message' => 'Avatar actualizado correctamente.',
            'user' => new UserResource($usuario),
        ]);
    }
}
