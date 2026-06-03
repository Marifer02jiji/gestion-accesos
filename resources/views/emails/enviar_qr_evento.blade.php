{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/emails/enviar_qr_evento.blade.php
Creación:    28/05/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, plantilla de correo QR grupal para responsable de evento
ID: 2 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar aviso de uso grupal y mostrar folio sin prefijo EVT-
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Código QR de Acceso al Evento</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#DA7E2D;color:#ffffff;padding:20px 24px;text-align:center;">
                            <h1 style="margin:0;font-size:20px;font-weight:bold;">Gestión de Accesos — IT Toluca</h1>
                            <p style="margin:8px 0 0;font-size:14px;opacity:0.95;">Pase de Acceso a Evento</p>
                        </td>
                    </tr>

                    {{-- Cuerpo --}}
                    <tr>
                        <td style="padding:24px;">

                            <p style="margin:0 0 16px;">
                                Estimado/a <strong>{{ $evento->nombre_responsable }}</strong>,
                            </p>
                            <p style="margin:0 0 20px;line-height:1.6;">
                                Se le hace entrega del código QR de acceso grupal para el siguiente evento. Preséntelo al vigilante al momento de la entrada y salida del grupo.
                            </p>

                            {{-- Datos del evento --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF3EC;border-radius:8px;padding:16px;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #f0d8c8;">
                                        <span style="font-size:12px;color:#888;">Folio</span><br>
                                        <strong style="font-size:15px;">{{ str_replace('EVT-', '', $evento->folio) }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #f0d8c8;">
                                        <span style="font-size:12px;color:#888;">Tipo de Evento</span><br>
                                        <strong>{{ $evento->tipo_evento }}</strong>
                                    </td>
                                </tr>
                                @if($evento->descripcion)
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #f0d8c8;">
                                        <span style="font-size:12px;color:#888;">Descripción</span><br>
                                        <strong>{{ $evento->descripcion }}</strong>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #f0d8c8;">
                                        <span style="font-size:12px;color:#888;">Lugar</span><br>
                                        <strong>{{ $evento->lugar }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;border-bottom:1px solid #f0d8c8;">
                                        <span style="font-size:12px;color:#888;">Fecha y Hora</span><br>
                                        <strong>{{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y H:i') }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;">
                                        <span style="font-size:12px;color:#888;">Número de Personas</span><br>
                                        <strong>{{ $evento->numero_personas }} persona(s)</strong>
                                    </td>
                                </tr>
                            </table>

                            {{-- QR adjunto --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:16px;background:#f9f9f9;border-radius:8px;border:2px dashed #DA7E2D;">
                                        <p style="margin:0 0 6px;font-size:14px;color:#DA7E2D;font-weight:bold;">
                                            El código QR viene adjunto en este correo
                                        </p>
                                        <p style="margin:0;font-size:12px;color:#666;">
                                            Abra el archivo adjunto <strong>codigo-qr.png</strong> y preséntelo en la entrada.
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
                            <p style="margin:8px 0 0;font-size:12px;color:#666;line-height:1.5;text-align:center;">
                                Si el vigilante no puede escanear el adjunto, indique el código de arriba.
                            </p>
                            <p style="margin:16px 0 0;font-size:13px;color:#DA7E2D;font-weight:bold;text-align:center;background:#FFF3EC;padding:10px;border-radius:6px;">
                                ⚠️ Este QR es para uso grupal — un solo escaneo registra la entrada/salida de todo el grupo.⚠️
                            </p>

                            {{-- Vigencia --}}
                            <p style="margin:16px 0 0;font-size:11px;color:#aaa;text-align:center;">
                                Válido del {{ \Carbon\Carbon::parse($qr->vigencia_inicio)->format('d/m/Y H:i') }}
                                al {{ \Carbon\Carbon::parse($qr->vigencia_final)->format('d/m/Y H:i') }}
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
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