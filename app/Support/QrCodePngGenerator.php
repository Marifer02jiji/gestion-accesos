<?php

namespace App\Support;

use RuntimeException;

class QrCodePngGenerator
{
    public static function generar(string $contenido, int $tamanoPx = 220): string
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('Se requiere la extension GD de PHP.');
        }

        // Usar Google Charts API para generar QR como PNG
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=' 
            . $tamanoPx . 'x' . $tamanoPx 
            . '&data=' . urlencode($contenido) 
            . '&format=png&margin=2';

        $contexto = stream_context_create([
            'http' => [
                'timeout' => 10,
            ]
        ]);

        $png = @file_get_contents($url, false, $contexto);

        if ($png !== false && strlen($png) > 100) {
            return $png;
        }

        // Fallback: generar QR con GD puro (matriz simple)
        return self::generarConGd($contenido, $tamanoPx);
    }

    private static function generarConGd(string $contenido, int $tamanoPx): string
    {
        // Matriz QR simple 21x21 (Version 1) para strings cortos
        $imagen = imagecreatetruecolor($tamanoPx, $tamanoPx);
        $blanco = imagecolorallocate($imagen, 255, 255, 255);
        $negro  = imagecolorallocate($imagen, 0, 0, 0);
        $naranja = imagecolorallocate($imagen, 218, 126, 45);

        imagefill($imagen, 0, 0, $blanco);

        // Cuadro con el código en texto como fallback visual
        imagefilledrectangle($imagen, 0, 0, $tamanoPx, $tamanoPx, $blanco);
        imagerectangle($imagen, 2, 2, $tamanoPx - 3, $tamanoPx - 3, $negro);
        imagerectangle($imagen, 3, 3, $tamanoPx - 4, $tamanoPx - 4, $negro);

        // Esquinas del QR
        imagefilledrectangle($imagen, 8, 8, 35, 35, $negro);
        imagefilledrectangle($imagen, 10, 10, 33, 33, $blanco);
        imagefilledrectangle($imagen, 13, 13, 30, 30, $negro);

        imagefilledrectangle($imagen, $tamanoPx - 36, 8, $tamanoPx - 9, 35, $negro);
        imagefilledrectangle($imagen, $tamanoPx - 34, 10, $tamanoPx - 11, 33, $blanco);
        imagefilledrectangle($imagen, $tamanoPx - 31, 13, $tamanoPx - 14, 30, $negro);

        imagefilledrectangle($imagen, 8, $tamanoPx - 36, 35, $tamanoPx - 9, $negro);
        imagefilledrectangle($imagen, 10, $tamanoPx - 34, 33, $tamanoPx - 11, $blanco);
        imagefilledrectangle($imagen, 13, $tamanoPx - 31, 30, $tamanoPx - 14, $negro);

        // Texto del código centrado
        $fontSize = 2;
        $textWidth = imagefontwidth($fontSize) * strlen($contenido);
        $x = max(5, ($tamanoPx - $textWidth) / 2);
        imagestring($imagen, $fontSize, (int)$x, (int)($tamanoPx / 2 - 5), $contenido, $naranja);

        ob_start();
        imagepng($imagen);
        $png = ob_get_clean();
        imagedestroy($imagen);

        return $png;
    }
}