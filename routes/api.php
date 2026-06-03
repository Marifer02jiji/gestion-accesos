<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     routes/api.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, rutas API REST con Sanctum para solicitante y autorizador
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Rutas públicas de vigilante sin autenticación Sanctum
 * ID: 3 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar rutas de notificaciones y visitas activas
 * ID: 4 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar rutas confirmarLlegada y confirmarSalida de encuentro
 * ID: 5 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar ruta de cancelación con correo al visitante
 */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificacionApiController;
use App\Http\Controllers\Api\SolicitudApiController;
use App\Http\Controllers\Api\VigilanteApiController;
use Illuminate\Support\Facades\Route;

// ── Públicas ──────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// Vigilante: todas públicas, no existe en BD
Route::post('/vigilante/login',      [VigilanteApiController::class, 'login']);
Route::post('/vigilante/consulta', [VigilanteApiController::class, 'consulta']);
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
    Route::get('/solicitudes/{id}/qr',            [SolicitudApiController::class, 'qr']);
    Route::post('/solicitudes/{id}/enviar-qr',    [SolicitudApiController::class, 'enviarQR']);
    Route::post('/solicitudes/{id}/reenviar-qr',  [SolicitudApiController::class, 'reenviarQR']);
    Route::post('/solicitudes/{id}/extender-qr',  [SolicitudApiController::class, 'extenderQR']);

    Route::get('/autorizador/solicitudes',       [SolicitudApiController::class, 'pendientes']);
    Route::post('/autorizador/{id}/autorizar',   [SolicitudApiController::class, 'autorizar']);
    Route::post('/autorizador/{id}/rechazar',    [SolicitudApiController::class, 'rechazar']);

    Route::get('/notificaciones', [NotificacionApiController::class, 'index']);
    Route::post('/notificaciones/todas-leidas', [NotificacionApiController::class, 'marcarTodasLeidas']);
    Route::delete('/notificaciones', [NotificacionApiController::class, 'eliminarTodas']);
    Route::post('/notificaciones/{id}/leida', [NotificacionApiController::class, 'marcarLeida']);
    Route::delete('/notificaciones/{id}', [NotificacionApiController::class, 'eliminar']);

    Route::get('/visitas/activas', [SolicitudApiController::class, 'activas']);
    Route::post('/visitas/{id}/confirmar-llegada', [SolicitudApiController::class, 'confirmarLlegada']);
    Route::post('/visitas/{id}/confirmar-salida', [SolicitudApiController::class, 'confirmarSalida']);
});