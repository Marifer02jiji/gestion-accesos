<?php

namespace App\Mail;

use App\Models\QR;
use App\Support\QrCodePngGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnviarQRMail extends Mailable
{
    use Queueable, SerializesModels;

    public QR $qr;

    public string $qrPngBase64;

    private string $qrPngBinary;

    public function __construct(QR $qr)
    {
        $this->qr = $qr->load([
            'solicitudVisitante.visitante',
            'solicitudVisitante.solicitud',
        ]);

        $this->qrPngBinary  = QrCodePngGenerator::generar($this->qr->codigo_numerico);
        $this->qrPngBase64  = base64_encode($this->qrPngBinary);
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
                'visitante'    => $this->qr->solicitudVisitante->visitante,
                'solicitud'    => $this->qr->solicitudVisitante->solicitud,
                'qr'           => $this->qr,
                'qrPngBase64'  => $this->qrPngBase64,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->qrPngBinary, 'codigo-qr.png')
                ->withMime('image/png'),
        ];
    }
}
