<?php

namespace App\Mail;

use App\Models\QR;
use App\Support\QrCodePngGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviarQRMail extends Mailable
{
    use Queueable, SerializesModels;

    public QR $qr;

    public function __construct(QR $qr)
    {
        $this->qr = $qr->load([
            'solicitudVisitante.visitante',
            'solicitudVisitante.solicitud',
        ]);
    }

    public function build()
    {
        $visitante = $this->qr->solicitudVisitante->visitante;
        $solicitud = $this->qr->solicitudVisitante->solicitud;

        $png = QrCodePngGenerator::generar($this->qr->codigo_numerico);

        return $this->subject('Tu código QR de acceso — IT Toluca')
            ->view('emails.enviar_qr', [
                'visitante' => $visitante,
                'solicitud' => $solicitud,
                'qr'        => $this->qr,
                'qrEmbed'   => $this->embedData($png, 'codigo-qr.png', 'image/png'),
            ]);
    }
}
