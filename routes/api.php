<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SolicitudApiController;
use App\Http\Controllers\Api\VigilanteApiController;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/perfil', [AuthController::class, 'perfil']);

    // Solicitante
    Route::get('/solicitudes', [SolicitudApiController::class, 'index']);
    Route::post('/solicitudes', [SolicitudApiController::class, 'store']);
    Route::get('/solicitudes/{id}', [SolicitudApiController::class, 'show']);
    Route::post('/solicitudes/{id}/cancelar', [SolicitudApiController::class, 'cancelar']);
    Route::get('/solicitudes/{id}/qr', [SolicitudApiController::class, 'qr']);

    // Autorizador
    Route::get('/autorizador/solicitudes', [SolicitudApiController::class, 'pendientes']);
    Route::post('/autorizador/{id}/autorizar', [SolicitudApiController::class, 'autorizar']);
    Route::post('/autorizador/{id}/rechazar', [SolicitudApiController::class, 'rechazar']);

    // Vigilante
    Route::get('/vigilante/visitas-hoy', [VigilanteApiController::class, 'visitasHoy']);
    Route::post('/vigilante/escanear', [VigilanteApiController::class, 'escanear']);
    Route::post('/vigilante/entrada', [VigilanteApiController::class, 'registrarEntrada']);
    Route::post('/vigilante/salida', [VigilanteApiController::class, 'registrarSalida']);
    Route::get('/vigilante/historial', [VigilanteApiController::class, 'historial']);

    // Notificaciones
    Route::get('/notificaciones', [AuthController::class, 'notificaciones']);
});