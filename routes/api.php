<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpedienteController;

//login (Inicio)
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con autenticacion
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Usuario autenticado
    Route::get('/me', [AuthController::class, 'me']);

    // Listas para formularios
    Route::get('/areas',           [ExpedienteController::class, 'areas']);
    Route::get('/tipos-documento', [ExpedienteController::class, 'tiposDocumento']);

    // Registrar expediente
    Route::post('/expedientes', [ExpedienteController::class, 'store']);

    // Buscar expedientes con filtros (debe ir antes que /{id})
    Route::get('/expedientes/buscar', [ExpedienteController::class, 'search']);

    // Listar expedientes
    Route::get('/expedientes', [ExpedienteController::class, 'index']);

    // Ver detalle de expediente
    Route::get('/expedientes/{id}', [ExpedienteController::class, 'show']);

});