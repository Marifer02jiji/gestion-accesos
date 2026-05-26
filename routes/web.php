<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AutorizadorController;
use App\Http\Controllers\VigilanteController;
use App\Models\QR;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificacionController;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Rutas Solicitante
Route::middleware(['auth', 'role:solicitante'])->group(function () {
    Route::resource('solicitudes', SolicitudController::class);
    Route::post('/solicitudes/{id}/cancelar', [SolicitudController::class, 'cancelar'])->name('solicitudes.cancelar');
    Route::get('/solicitudes/{id}/qr', function($id) {
        $sv = \App\Models\SolicitudVisitante::where('id_solicitud', $id)->first();
        $qr = QR::with(['solicitudVisitante.visitante'])
            ->where('id_solicitud_visitante', $sv->id_solicitud_visitante)
            ->first();
        return view('solicitudes.qr', compact('qr'));
    })->name('solicitudes.qr');
});

// Rutas Autorizador
Route::middleware(['auth', 'role:autorizador'])->group(function () {
    Route::get('/autorizador', [AutorizadorController::class, 'index'])->name('autorizador.index');
    Route::post('/autorizador/{id}/autorizar', [AutorizadorController::class, 'autorizar'])->name('autorizador.autorizar');
    Route::post('/autorizador/{id}/rechazar', [AutorizadorController::class, 'rechazar'])->name('autorizador.rechazar');
});

// Rutas Vigilante — sin login requerido
Route::prefix('vigilante')->group(function () {
    Route::get('/', [VigilanteController::class, 'index'])->name('vigilante.index');
    Route::post('/identificar', [VigilanteController::class, 'identificar'])->name('vigilante.identificar');
    Route::get('/salir-sesion', [VigilanteController::class, 'salirSesion'])->name('vigilante.salirSesion');
    Route::post('/escanear', [VigilanteController::class, 'escanear'])->name('vigilante.escanear');
    Route::post('/entrada', [VigilanteController::class, 'registrarEntrada'])->name('vigilante.entrada');
    Route::post('/salida', [VigilanteController::class, 'registrarSalida'])->name('vigilante.salida');
    Route::get('/historial', [VigilanteController::class, 'historial'])->name('vigilante.historial');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.todas-leidas');
});

require __DIR__.'/auth.php';


