<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AutorizadorController;
use App\Http\Controllers\VigilanteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ResetPasswordController;
use App\Models\QR;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Rutas Solicitante
Route::middleware(['auth', 'role:solicitante'])->group(function () {
    Route::get('/solicitudes/historial', [SolicitudController::class, 'historial'])->name('solicitudes.historial');
    Route::resource('solicitudes', SolicitudController::class);
    Route::post('/solicitudes/{id}/cancelar', [SolicitudController::class, 'cancelar'])->name('solicitudes.cancelar');
    Route::post('/solicitudes/{id}/enviar-qr', [SolicitudController::class, 'enviarQR'])->name('solicitudes.enviarQR');
    Route::get('/solicitudes/{id}/qr', function($id) {
        $sv = \App\Models\SolicitudVisitante::where('id_solicitud', $id)->first();
        $qr = QR::with(['solicitudVisitante.visitante'])
            ->where('id_solicitud_visitante', $sv->id_solicitud_visitante)
            ->first();
        return view('solicitudes.qr', compact('qr'));
    })->name('solicitudes.qr');
    Route::post('/solicitudes/{id}/llegada-encuentro', [SolicitudController::class, 'registrarLlegadaEncuentro'])->name('solicitudes.llegadaEncuentro');
    Route::post('/solicitudes/{id}/salida-encuentro', [SolicitudController::class, 'registrarSalidaEncuentro'])->name('solicitudes.salidaEncuentro');
});

// Rutas Autorizador
Route::middleware(['auth', 'role:autorizador'])->group(function () {
    Route::get('/autorizador', [AutorizadorController::class, 'index'])->name('autorizador.index');
    Route::get('/autorizador/historial', [AutorizadorController::class, 'historial'])->name('autorizador.historial');
    Route::post('/autorizador/{id}/autorizar', [AutorizadorController::class, 'autorizar'])->name('autorizador.autorizar');
    Route::post('/autorizador/{id}/rechazar', [AutorizadorController::class, 'rechazar'])->name('autorizador.rechazar');
});

// Rutas Vigilante
Route::middleware(['auth', 'role:vigilante'])->group(function () {
    Route::get('/vigilante', [VigilanteController::class, 'index'])->name('vigilante.index');
    Route::post('/vigilante/escanear', [VigilanteController::class, 'escanear'])->name('vigilante.escanear');
    Route::post('/vigilante/entrada', [VigilanteController::class, 'registrarEntrada'])->name('vigilante.entrada');
    Route::post('/vigilante/salida', [VigilanteController::class, 'registrarSalida'])->name('vigilante.salida');
    Route::get('/vigilante/historial', [VigilanteController::class, 'historial'])->name('vigilante.historial');
    Route::post('/vigilante/identificar', [VigilanteController::class, 'identificar'])->name('vigilante.identificar');
    Route::get('/vigilante/salir-sesion', [VigilanteController::class, 'salirSesion'])->name('vigilante.salirSesion');
});

// Rutas Administrador
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin/reportes', [AdminController::class, 'reportes'])->name('admin.reportes');
    Route::get('/admin/todas-solicitudes', [AdminController::class, 'todasLasSolicitudes'])->name('admin.todas-solicitudes');
    Route::get('/admin/reporte-visitas', [AdminController::class, 'reporteVisitas'])->name('admin.reporte-visitas');
    Route::get('/admin/citas-consulta', [AdminController::class, 'citasConsulta'])->name('admin.citas-consulta');
    Route::get('/admin/exclusiones', [AdminController::class, 'exclusiones'])->name('admin.exclusiones');
    Route::post('/admin/exclusiones', [AdminController::class, 'storeExclusion'])->name('admin.exclusiones.store');
    Route::delete('/admin/exclusiones/{id}', [AdminController::class, 'destroyExclusion'])->name('admin.exclusiones.destroy');
    Route::get('/admin/visitantes-activos', [AdminController::class, 'visitantesActivos'])->name('admin.visitantes-activos');
    Route::post('/admin/registrar-salida', [AdminController::class, 'registrarSalida'])->name('admin.registrarSalida');
});

// Rutas Organizador
Route::middleware(['auth', 'role:organizador'])->group(function () {
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/crear', [EventoController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');
    Route::get('/eventos/{id}/editar', [EventoController::class, 'edit'])->name('eventos.edit');
    Route::put('/eventos/{id}', [EventoController::class, 'update'])->name('eventos.update');
    Route::post('/eventos/{id}/reenviar-qr', [EventoController::class, 'reenviarQR'])->name('eventos.reenviarQR');
    Route::delete('/eventos/{id}', [EventoController::class, 'destroy'])->name('eventos.destroy');
});

// Rutas de perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Notificaciones
Route::middleware(['auth'])->group(function () {
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.todas-leidas');
    Route::delete('/notificaciones/{id}', [NotificacionController::class, 'eliminar'])->name('notificaciones.eliminar');
    Route::delete('/notificaciones', [NotificacionController::class, 'eliminarTodas'])->name('notificaciones.eliminarTodas');
});

require __DIR__.'/auth.php';