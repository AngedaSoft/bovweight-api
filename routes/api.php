<?php

use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EstimacionController;
use App\Http\Controllers\Api\FincaController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PesajeController;
use App\Http\Controllers\Api\RazaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - BovWeight CR
|--------------------------------------------------------------------------
| Prefijo /api configurado en bootstrap/app.php. Las rutas publicas no
| requieren autenticacion. Las rutas protegidas requieren un Bearer token
| emitido por Sanctum.
*/

Route::get('/health', HealthController::class)->name('health');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });

    Route::apiResource('fincas', FincaController::class);
    Route::apiResource('animales', AnimalController::class);

    Route::get('/razas', [RazaController::class, 'index'])->name('razas.index');

    Route::get('/animales/{animal}/pesajes', [PesajeController::class, 'porAnimal'])
        ->name('animales.pesajes');
    Route::get('/pesajes/{pesaje}', [PesajeController::class, 'show'])->name('pesajes.show');
    Route::patch('/pesajes/{pesaje}/correccion', [PesajeController::class, 'corregir'])
        ->name('pesajes.corregir');

    Route::post('/estimaciones', [EstimacionController::class, 'store'])->name('estimaciones.store');
});
