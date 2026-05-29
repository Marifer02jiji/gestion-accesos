<?php

namespace App\Services;

use App\Mail\EnviarQRMail;
use App\Models\Solicitud;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QrCorreoService
{
    /**
     * Envía el correo con QR a cada visitante de la solicitud que tenga correo y QR.
     *
     * @return array{enviados: int, errores: int}
     */
    public static function enviarASolicitud(Solicitud $solicitud): array
    {
        $solicitud->loadMissing(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante']);

        $enviados = 0;
        $errores  = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            $qr     = $sv->qr;
            $correo = $sv->visitante->correo_personal ?? null;

            if (! $qr || ! $correo) {
                continue;
            }

            try {
                Mail::to($correo)->send(new EnviarQRMail($qr));
                $enviados++;
            } catch (\Throwable $e) {
                $errores++;
                Log::error('Error enviando QR por correo', [
                    'correo'       => $correo,
                    'id_qr'        => $qr->id_qr,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        return ['enviados' => $enviados, 'errores' => $errores];
    }

    public static function mensajeResultado(array $resultado): string
    {
        $enviados = $resultado['enviados'];
        $errores  = $resultado['errores'];

        if ($enviados > 0 && $errores === 0) {
            return "Correo con QR enviado a {$enviados} visitante(s).";
        }

        if ($enviados > 0 && $errores > 0) {
            return "QR enviado a {$enviados} visitante(s). {$errores} correo(s) no pudieron enviarse (revisa MAIL_* en .env y storage/logs).";
        }

        return 'No se pudo enviar ningún correo. Verifica MAIL_MAILER, credenciales SMTP y que los visitantes tengan correo.';
    }
}
