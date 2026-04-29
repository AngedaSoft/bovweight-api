<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - BovWeight CR
|--------------------------------------------------------------------------
|
| Todas las rutas tienen el prefijo /api (configurado en bootstrap/app.php).
| Las rutas publicas no requieren autenticacion. Las rutas protegidas
| requieren un token Bearer emitido por Sanctum.
|
*/

// ===== Rutas publicas =====
Route::get('/health', HealthController::class)->name('health');

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

// ===== Rutas protegidas =====
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });

    // Aqui van los endpoints de Finca, Animal, Pesaje, etc. en proximos sprints.
});
