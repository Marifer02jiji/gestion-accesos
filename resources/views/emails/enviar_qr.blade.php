<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Codigo QR de acceso</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:#DA7E2D;color:#ffffff;padding:20px 24px;text-align:center;">
                            <h1 style="margin:0;font-size:20px;font-weight:bold;">Gestión de Accesos — IT Toluca</h1>
                            <p style="margin:8px 0 0;font-size:14px;opacity:0.95;">Tu pase de visita</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;">Hola <strong>{{ $visitante->nombre }} {{ $visitante->apellidos }}</strong>,</p>
                            <p style="margin:0 0 16px;line-height:1.5;">
                                Tu solicitud de acceso ha sido autorizada. Presenta el siguiente código QR en la entrada de la institución.
                            </p>

                            {{-- Folio --}}
                            @if($solicitud->folio ?? false)
                                <p style="margin:0 0 8px;"><strong>Folio:</strong> {{ str_replace('VIS-', '', $solicitud->folio) }}</p>
                            @endif

                            {{-- Anfitrión --}}
                            @if($solicitud->solicitante ?? false)
                                <p style="margin:0 0 8px;"><strong>Anfitrión:</strong> {{ $solicitud->solicitante->name ?? '—' }}</p>
                            @endif

                            <p style="margin:0 0 8px;"><strong>Fecha de visita:</strong> {{ \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y H:i') }}</p>
                            <p style="margin:0 0 8px;"><strong>Lugar de encuentro:</strong> {{ $solicitud->lugar_encuentro }}</p>
                            <p style="margin:0 0 20px;"><strong>Vigencia del QR:</strong>
                                {{ \Carbon\Carbon::parse($qr->vigencia_inicio)->format('d/m/Y H:i') }}
                                — {{ \Carbon\Carbon::parse($qr->vigencia_final)->format('d/m/Y H:i') }}
                            </p>

                            {{-- QR adjunto --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:16px;background:#FFF3EC;border-radius:8px;border:2px dashed #DA7E2D;">
                                        <p style="margin:0 0 8px;font-size:14px;color:#DA7E2D;font-weight:bold;">
                                            El código QR viene adjunto en este correo
                                        </p>
                                        <p style="margin:0;font-size:12px;color:#666;">
                                            Abre el archivo adjunto "codigo-qr.png" y preséntalo en la entrada.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Código de validación --}}
                            <p style="margin:20px 0 4px;text-align:center;font-size:12px;color:#999;">
                                Código de validación manual
                            </p>
                            <p style="margin:0;text-align:center;font-size:22px;font-weight:bold;letter-spacing:3px;color:#333;">
                                {{ $qr->codigo_numerico }}
                            </p>
                            <p style="margin:12px 0 0;font-size:12px;color:#666;line-height:1.5;text-align:center;">
                                Si el personal de vigilancia no puede escanear el adjunto, indica el código de arriba.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f0f0f0;padding:12px 24px;text-align:center;font-size:11px;color:#888;">
                            Este correo fue generado automáticamente. No respondas a este mensaje.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>