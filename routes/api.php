<?php
// =============================================================================
// Proyecto  : Sistema de Gestión de Accesos y Visitas
// Archivo   : routes/api.php
// Autor     : Omega Company
// Fecha     : 2026-05-27
// Versión   : 2.0.0
// Cambio    : Rutas del vigilante son PÚBLICAS — no necesita Sanctum porque
//             no existe como usuario en BD. Solicitante y autorizador
//             sin ningún cambio.
// =============================================================================

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SolicitudApiController;
use App\Http\Controllers\Api\VigilanteApiController;
use Illuminate\Support\Facades\Route;

// ── Públicas ──────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// Vigilante: todas públicas, no existe en BD
Route::post('/vigilante/login',      [VigilanteApiController::class, 'login']);
Route::get('/vigilante/visitas-hoy', [VigilanteApiController::class, 'visitasHoy']);
Route::post('/vigilante/escanear',   [VigilanteApiController::class, 'escanear']);
Route::post('/vigilante/entrada',    [VigilanteApiController::class, 'registrarEntrada']);
Route::post('/vigilante/salida',     [VigilanteApiController::class, 'registrarSalida']);
Route::get('/vigilante/historial',   [VigilanteApiController::class, 'historial']);


// ── Protegidas con Sanctum (solicitante y autorizador) — SIN CAMBIOS ──────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/perfil',  [AuthController::class, 'perfil']);

    Route::get('/solicitudes',                [SolicitudApiController::class, 'index']);
    Route::post('/solicitudes',               [SolicitudApiController::class, 'store']);
    Route::get('/solicitudes/{id}',           [SolicitudApiController::class, 'show']);
    Route::post('/solicitudes/{id}/cancelar', [SolicitudApiController::class, 'cancelar']);
    Route::get('/solicitudes/{id}/qr',        [SolicitudApiController::class, 'qr']);

    Route::get('/autorizador/solicitudes',       [SolicitudApiController::class, 'pendientes']);
    Route::post('/autorizador/{id}/autorizar',   [SolicitudApiController::class, 'autorizar']);
    Route::post('/autorizador/{id}/rechazar',    [SolicitudApiController::class, 'rechazar']);

    Route::get('/notificaciones', [AuthController::class, 'notificaciones']);

    Route::get('/visitas/activas', [SolicitudApiController::class, 'activas']);
    Route::post('/visitas/{id}/confirmar-llegada', [SolicitudApiController::class, 'confirmarLlegada']);
    Route::post('/visitas/{id}/confirmar-salida', [SolicitudApiController::class, 'confirmarSalida']);
});