<?php

namespace App\Mail;

use App\Models\QR;
use App\Support\QrCodePngGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class EnviarQRMail extends Mailable
{
    use Queueable, SerializesModels;

    public QR $qr;
    public string $qrPath;

    public function __construct(QR $qr)
    {
        $this->qr = $qr->load([
            'solicitudVisitante.visitante',
            'solicitudVisitante.solicitud',
        ]);

        // Guardar PNG en storage temporal
        $png = QrCodePngGenerator::generar($this->qr->codigo_numerico);
        $filename = 'qr_' . $this->qr->id_qr . '.png';
        $this->qrPath = storage_path('app/temp/' . $filename);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        file_put_contents($this->qrPath, $png);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu codigo QR de acceso - IT Toluca',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enviar_qr',
            with: [
                'visitante' => $this->qr->solicitudVisitante->visitante,
                'solicitud' => $this->qr->solicitudVisitante->solicitud,
                'qr'        => $this->qr,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->qrPath)
                ->as('codigo-qr.png')
                ->withMime('image/png'),
        ];
    }
}