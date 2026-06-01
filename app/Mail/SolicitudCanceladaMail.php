<?php

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