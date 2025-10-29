<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\MedicionController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of|    them will be assigned to the "api" middleware group.
|
*/

// Rutas públicas (autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Rutas para usuarios (solo administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('/informes/usuarios', [InformeController::class, 'listadoUsuarios']);
        Route::get('/users/{user}', [AuthController::class, 'show']);
    });

    // Rutas para lotes
    Route::apiResource('lotes', LoteController::class)
        ->middleware('role:administrador');

    // Rutas para mediciones
    Route::get('/mediciones/lote/{lote}', [MedicionController::class, 'porLote']);
    Route::post('/mediciones', [MedicionController::class, 'store']);
    Route::middleware('role:administrador')->group(function () {
        Route::put('/mediciones/{medicion}', [MedicionController::class, 'update']);
        Route::delete('/mediciones/{medicion}', [MedicionController::class, 'destroy']);
        Route::get('/mediciones', [MedicionController::class, 'index']);
    });

    // Rutas para informes y gráficos (solo administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::get('/informes/consumos', [InformeController::class, 'consumosPorPeriodo']);
        Route::get('/informes/mediciones', [InformeController::class, 'listadoMediciones']);
        Route::get('/informes/grafico-consumos', [InformeController::class, 'graficoConsumos']);
    });

    // Ruta para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);
});

Route::get('/test-guard', function (Request $request) {
    return response()->json([
        'guard' => auth()->guard()->getName(),
        'user' => $request->user(),
    ]);
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
