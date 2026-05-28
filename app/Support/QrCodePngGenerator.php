<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class QrCodePngGenerator
{
    public static function generar(string $contenido, int $tamanoPx = 220): string
    {
        try {
            return QrCode::format('png')
                ->size($tamanoPx)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($contenido);
        } catch (Throwable) {
            // Sin imagick: generar PNG con GD + matriz de Bacon
        }

        return self::generarConGd($contenido, $tamanoPx);
    }

    private static function generarConGd(string $contenido, int $tamanoPx): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Se requiere la extensión GD de PHP para generar el QR en el correo.');
        }

        $qr = Encoder::encode($contenido, ErrorCorrectionLevel::H());
        $matrix = $qr->getMatrix();
        $ancho = $matrix->getWidth();
        $alto = $matrix->getHeight();
        $margin = 2;
        $totalModules = max($ancho, $alto) + ($margin * 2);
        $escala = max(1, (int) floor($tamanoPx / $totalModules));
        $imageSize = $totalModules * $escala;

        $imagen = imagecreatetruecolor($imageSize, $imageSize);
        if ($imagen === false) {
            throw new RuntimeException('No se pudo crear la imagen del QR.');
        }

        $blanco = imagecolorallocate($imagen, 255, 255, 255);
        $negro = imagecolorallocate($imagen, 0, 0, 0);
        imagefill($imagen, 0, 0, $blanco);

        for ($fila = 0; $fila < $alto; $fila++) {
            for ($col = 0; $col < $ancho; $col++) {
                if ((int) $matrix->get($col, $fila) !== 1) {
                    continue;
                }

                $x = ($col + $margin) * $escala;
                $y = ($fila + $margin) * $escala;
                imagefilledrectangle(
                    $imagen,
                    $x,
                    $y,
                    $x + $escala - 1,
                    $y + $escala - 1,
                    $negro
                );
            }
        }

        ob_start();
        imagepng($imagen);
        $png = ob_get_clean();
        imagedestroy($imagen);

        if ($png === false || $png === '') {
            throw new RuntimeException('No se pudo generar el PNG del QR.');
        }

        return $png;
    }
}
