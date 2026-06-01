<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Visita Cancelada</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:#3B5675;color:#ffffff;padding:20px 24px;text-align:center;">
                            <h1 style="margin:0;font-size:20px;font-weight:bold;">Gestión de Accesos — IT Toluca</h1>
                            <p style="margin:8px 0 0;font-size:14px;opacity:0.95;">Aviso de cancelación de visita</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;">Estimado/a <strong>{{ $visitante->nombre }} {{ $visitante->apellidos }}</strong>,</p>
                            <p style="margin:0 0 16px;line-height:1.6;">
                                Le informamos que su visita programada ha sido <strong style="color:#dc2626;">cancelada</strong>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f9f9;border-radius:8px;padding:16px;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #eee;">
                                        <span style="font-size:12px;color:#888;">Folio</span><br>
                                        <strong>{{ str_replace('VIS-', '', $solicitud->folio) }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #eee;">
                                        <span style="font-size:12px;color:#888;">Fecha programada</span><br>
                                        <strong>{{ \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y H:i') }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #eee;">
                                        <span style="font-size:12px;color:#888;">Lugar</span><br>
                                        <strong>{{ $solicitud->lugar_encuentro }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;">
                                        <span style="font-size:12px;color:#888;">Anfitrión</span><br>
                                        <strong>{{ $anfitrion }}</strong>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:16px;background:#FFF3EC;border-radius:8px;border-left:4px solid #DA7E2D;">
                                        <p style="margin:0;font-size:13px;color:#555;line-height:1.6;">
                                            Si tienes dudas sobre esta cancelación, comunícate directamente con tu anfitrión
                                            <strong>{{ $anfitrion }}</strong> para más información.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0;font-size:12px;color:#999;text-align:center;">
                                Tu código QR ha sido desactivado y ya no es válido para acceder a las instalaciones.
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