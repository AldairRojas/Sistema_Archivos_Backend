<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\ArchivoDigitalController;
use App\Http\Controllers\AreaController;

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================
Route::post('/login', [AuthController::class, 'login']);

// ============================================
// RUTAS PROTEGIDAS (con autenticación)
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // ── Autenticación ──────────────────────────
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ── Listas para formularios ────────────────
    Route::get('/tipos-documento', [ExpedienteController::class, 'tiposDocumento']);

    // ── Sprint 1 ───────────────────────────────
    Route::post('/expedientes', [ExpedienteController::class, 'store']);
    Route::get('/expedientes/buscar', [ExpedienteController::class, 'search']);
    Route::get('/expedientes/alertas', [ExpedienteController::class, 'alertas']);
    Route::get('/expedientes', [ExpedienteController::class, 'index']);
    Route::get('/expedientes/{id}', [ExpedienteController::class, 'show']);

    // ── Sprint 2 ───────────────────────────────
    Route::put('/expedientes/{id}', [ExpedienteController::class, 'update']);
    Route::patch('/expedientes/{id}/estado', [ExpedienteController::class, 'cambiarEstado']);

    // ── Sprint 3 ───────────────────────────────
    Route::post('/expedientes/{id}/archivos', [ArchivoDigitalController::class, 'subir']);
    Route::get('/expedientes/{id}/archivos', [ArchivoDigitalController::class, 'listar']);
    Route::get('/expedientes/{id}/archivos/{archivo_id}', [ArchivoDigitalController::class, 'descargar']);

    // ── Sprint 4 — Áreas ───────────────────────
    Route::get('/areas', [AreaController::class, 'index']);
    Route::get('/areas/{id}', [AreaController::class, 'show']);
    Route::post('/areas', [AreaController::class, 'store']);
    Route::put('/areas/{id}', [AreaController::class, 'update']);
    Route::delete('/areas/{id}', [AreaController::class, 'destroy']);

});