<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Mail/SolicitudCanceladaMail.php
 * Creación:    01/06/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, correo de cancelación al visitante con datos del anfitrión y QR desactivado
 */

namespace App\Mail;

use App\Models\Solicitud;
use App\Models\Visitante;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudCanceladaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Visitante $visitante,
        public Solicitud $solicitud,
        public string    $anfitrion
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu visita ha sido cancelada — IT Toluca',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud_cancelada',
            with: [
                'visitante' => $this->visitante,
                'solicitud' => $this->solicitud,
                'anfitrion' => $this->anfitrion,
            ],
        );
    }
}