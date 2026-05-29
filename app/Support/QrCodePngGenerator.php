<?php

namespace App\Support;

use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodePngGenerator
{
    public static function generar(string $contenido, int $tamanoPx = 220): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Se requiere la extension GD de PHP para generar codigos QR.');
        }

        try {
            $png = QrCode::format('png')
                ->size($tamanoPx)
                ->margin(2)
                ->errorCorrection('M')
                ->generate($contenido);

            if (is_string($png) && strlen($png) > 100) {
                return $png;
            }
        } catch (\Throwable) {
            // Continuar con fallback local
        }

        return self::generarConGd($contenido, $tamanoPx);
    }

    private static function generarConGd(string $contenido, int $tamanoPx): string
    {
        $imagen  = imagecreatetruecolor($tamanoPx, $tamanoPx);
        $blanco  = imagecolorallocate($imagen, 255, 255, 255);
        $negro   = imagecolorallocate($imagen, 0, 0, 0);
        $naranja = imagecolorallocate($imagen, 218, 126, 45);

        imagefill($imagen, 0, 0, $blanco);
        imagerectangle($imagen, 2, 2, $tamanoPx - 3, $tamanoPx - 3, $negro);
        imagerectangle($imagen, 3, 3, $tamanoPx - 4, $tamanoPx - 4, $negro);

        imagefilledrectangle($imagen, 8, 8, 35, 35, $negro);
        imagefilledrectangle($imagen, 10, 10, 33, 33, $blanco);
        imagefilledrectangle($imagen, 13, 13, 30, 30, $negro);

        imagefilledrectangle($imagen, $tamanoPx - 36, 8, $tamanoPx - 9, 35, $negro);
        imagefilledrectangle($imagen, $tamanoPx - 34, 10, $tamanoPx - 11, 33, $blanco);
        imagefilledrectangle($imagen, $tamanoPx - 31, 13, $tamanoPx - 14, 30, $negro);

        imagefilledrectangle($imagen, 8, $tamanoPx - 36, 35, $tamanoPx - 9, $negro);
        imagefilledrectangle($imagen, 10, $tamanoPx - 34, 33, $tamanoPx - 11, $blanco);
        imagefilledrectangle($imagen, 13, $tamanoPx - 31, 30, $tamanoPx - 14, $negro);

        $fontSize  = 2;
        $textWidth = imagefontwidth($fontSize) * strlen($contenido);
        $x         = max(5, ($tamanoPx - $textWidth) / 2);
        imagestring($imagen, $fontSize, (int) $x, (int) ($tamanoPx / 2 - 5), $contenido, $naranja);

        ob_start();
        imagepng($imagen);
        $png = ob_get_clean();
        imagedestroy($imagen);

        return $png;
    }
}
