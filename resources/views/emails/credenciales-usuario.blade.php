<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Bienvenido a BovWeight CR</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1f2933;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f4f6fa;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 18px rgba(15,23,42,0.08);">
                <tr>
                    <td style="background:#173a2c;padding:32px 32px 24px;color:#ffffff;">
                        <h1 style="margin:0;font-size:24px;letter-spacing:-0.5px;">BovWeight CR</h1>
                        <p style="margin:8px 0 0;font-size:14px;color:#b6d7c4;">Estimación inteligente de peso bovino</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;">
                        <h2 style="margin:0 0 16px;font-size:20px;color:#173a2c;">Hola {{ $nombre }},</h2>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                            Un administrador acaba de crear tu cuenta en BovWeight CR con el rol de
                            <strong>{{ ucfirst($rol) }}</strong>. Usa estas credenciales para ingresar:
                        </p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;background:#f4f4ef;border-radius:8px;width:100%;">
                            <tr>
                                <td style="padding:16px 20px;">
                                    <p style="margin:0 0 4px;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;color:#6b7280;">Correo</p>
                                    <p style="margin:0;font-size:16px;font-weight:600;color:#173a2c;">{{ $correo }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 20px 16px;">
                                    <p style="margin:0 0 4px;font-size:12px;text-transform:uppercase;letter-spacing:0.06em;color:#6b7280;">Contraseña temporal</p>
                                    <p style="margin:0;font-size:18px;font-weight:700;font-family:'Consolas','Courier New',monospace;color:#173a2c;">{{ $contrasena }}</p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#475569;">
                            <strong>Importante:</strong> esta contraseña fue generada automáticamente.
                            Te recomendamos cambiarla la primera vez que ingreses desde
                            <em>Configuración → Cambiar contraseña</em>.
                        </p>

                        @if($loginUrl)
                        <p style="margin:0 0 24px;">
                            <a href="{{ $loginUrl }}" style="display:inline-block;background:#173a2c;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;">
                                Ingresar al sistema
                            </a>
                        </p>
                        @endif

                        <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.5;">
                            Si no esperabas este correo o no reconoces al administrador que creó tu
                            cuenta, ignora este mensaje y la cuenta podrá ser desactivada.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f4f4ef;padding:16px 32px;text-align:center;font-size:12px;color:#94a3b8;">
                        BovWeight CR · Universidad de Costa Rica · Sede de Guanacaste
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
