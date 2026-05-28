<?php

namespace App\Mail;

use App\Models\QR;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviarQRMail extends Mailable
{
    use Queueable, SerializesModels;

    public $qr;

    public function __construct(QR $qr)
    {
        $this->qr = $qr;
    }

    public function build()
    {
        return $this->subject('Tu Código QR de Acceso al IT Toluca')
                    ->view('emails.enviar_qr'); // Esta será la vista de tu correo
    }
}