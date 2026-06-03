<?php

use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EstimacionController;
use App\Http\Controllers\Api\FincaController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PesajeController;
use App\Http\Controllers\Api\RazaController;
use App\Http\Controllers\Api\RebanoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - BovWeight CR
|--------------------------------------------------------------------------
*/

// Salud del sistema
Route::get('/health', HealthController::class)->name('health');

// Autenticación Pública
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

// Rutas Protegidas (Sesión Iniciada)
Route::middleware('auth:sanctum')->group(function () {
    
    //  Grupo Auth Extendido (Perfil / Configuración - Requerimiento #7)
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::patch('/me', [AuthController::class, 'updateProfile'])->name('auth.me.update');
        Route::post('/cambiar-contrasena', [AuthController::class, 'changePassword'])->name('auth.password.change');
        Route::post('/avatar', [AuthController::class, 'uploadAvatar'])->name('auth.avatar.upload');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    //  Dashboard Agregado (Requerimiento #8)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    //  Notificaciones / Alertas (Requerimiento #3)
    Route::prefix('notificaciones')->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('notificaciones.index');
        Route::patch('/{id}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
        Route::post('/leer-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.leer-todas');
    });

    // Fincas
    Route::apiResource('fincas', FincaController::class);
    
    // Rebaños (Asociados a Fincas + CRUD - Requerimiento #1)
    Route::get('/fincas/{id}/rebanos', [RebanoController::class, 'porFinca'])->name('fincas.rebanos');
    Route::apiResource('rebanos', RebanoController::class);

    //  Animales y Catálogos
    Route::apiResource('animales', AnimalController::class);
    Route::get('/razas', [RazaController::class, 'index'])->name('razas.index');

    //  Pesajes e Inteligencia Artificial
    Route::get('/animales/{animal}/pesajes', [PesajeController::class, 'porAnimal'])->name('animales.pesajes');
    Route::get('/pesajes/{pesaje}', [PesajeController::class, 'show'])->name('pesajes.show');
    Route::patch('/pesajes/{pesaje}/correccion', [PesajeController::class, 'corregir'])->name('pesajes.corregir');
    Route::post('/estimaciones', [EstimacionController::class, 'store'])->name('estimaciones.store');
});