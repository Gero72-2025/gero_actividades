<?php
/**
 * MailHelper — Envía correos usando PHPMailer + configuración SMTP del .env
 *
 * Uso:
 *   require_once APPROOT . '/libraries/MailHelper.php';
 *   $ok = enviarCorreoRecuperacion('destino@correo.com', 'Jorge', 'Pass$Temp!123');
 */

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Obtiene una variable del entorno con fallback.
 */
function _envGet(string $key, string $default = ''): string {
    $val = getenv($key);
    if ($val !== false) return $val;
    return $_ENV[$key] ?? $default;
}

/**
 * Envía el correo de recuperación de contraseña con la contraseña temporal.
 *
 * @param  string $destinatario  Email del usuario
 * @param  string $nombre        Nombre para mostrar en el saludo
 * @param  string $passTemp      Contraseña temporal en texto plano (NO el hash)
 * @return bool   true si se envió, false si hubo error
 */
function enviarCorreoRecuperacion(string $destinatario, string $nombre, string $passTemp): bool {
    $mail = new PHPMailer(true);

    try {
        // ── Servidor SMTP ────────────────────────────────
        $mail->isSMTP();
        $mail->Host       = _envGet('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = _envGet('MAIL_USERNAME');
        $mail->Password   = _envGet('MAIL_PASSWORD');
        $mail->SMTPSecure = strtolower(_envGet('MAIL_ENCRYPTION', 'tls')) === 'ssl'
                            ? PHPMailer::ENCRYPTION_SMTPS
                            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) _envGet('MAIL_PORT', '587');
        $mail->CharSet    = 'UTF-8';

        // ── Remitente y destinatario ──────────────────────
        $fromAddr = _envGet('MAIL_FROM_ADDRESS', _envGet('MAIL_USERNAME'));
        $fromName = _envGet('MAIL_FROM_NAME', 'Gero Actividades');
        $mail->setFrom($fromAddr, $fromName);
        $mail->addAddress($destinatario, $nombre);
        $mail->addReplyTo($fromAddr, $fromName);

        // ── Contenido ─────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña — ' . SITENAME;

        $urlCambio = URLROOT . '/users/cambiarPassword';
        $año       = date('Y');

        $mail->Body = <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:40px 0;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
                <!-- Cabecera -->
                <tr>
                  <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 40px 30px;text-align:center;">
                    <h1 style="margin:0;color:#fff;font-size:28px;font-weight:bold;">🔐 Recuperación de Contraseña</h1>
                    <p  style="margin:10px 0 0;color:rgba(255,255,255,.85);font-size:15px;">{$fromName}</p>
                  </td>
                </tr>
                <!-- Cuerpo -->
                <tr>
                  <td style="padding:40px;">
                    <p style="color:#333;font-size:16px;">Hola, <strong>{$nombre}</strong>:</p>
                    <p style="color:#555;font-size:15px;line-height:1.7;">
                      Recibimos una solicitud de recuperación de contraseña para tu cuenta
                      <strong>{$destinatario}</strong>. Tu nueva contraseña temporal es:
                    </p>

                    <!-- Contraseña temporal destacada -->
                    <div style="background:#f0f0ff;border:2px dashed #667eea;border-radius:8px;padding:20px;text-align:center;margin:25px 0;">
                      <p style="margin:0 0 8px;color:#555;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Contraseña Temporal</p>
                      <p style="margin:0;font-size:28px;font-weight:bold;font-family:monospace;color:#667eea;letter-spacing:3px;">{$passTemp}</p>
                    </div>

                    <p style="color:#555;font-size:15px;line-height:1.7;">
                      Al iniciar sesión con esta contraseña, serás redirigido automáticamente para
                      <strong>establecer una nueva contraseña segura</strong>.
                    </p>

                    <!-- Botón CTA -->
                    <div style="text-align:center;margin:30px 0;">
                      <a href="{$urlCambio}"
                         style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;text-decoration:none;
                                padding:14px 36px;border-radius:8px;font-size:16px;font-weight:bold;display:inline-block;">
                        Ir al sistema
                      </a>
                    </div>

                    <!-- Advertencias de seguridad -->
                    <table width="100%" cellpadding="12" cellspacing="0"
                           style="background:#fff8e1;border-left:4px solid #ffc107;border-radius:4px;margin-top:20px;">
                      <tr>
                        <td>
                          <p style="margin:0;color:#856404;font-size:13px;">
                            <strong>⚠️ Avisos de seguridad:</strong><br>
                            • Esta contraseña expirará en <strong>24 horas</strong>.<br>
                            • Si no solicitaste este cambio, ignora este correo; tu contraseña actual seguirá siendo válida.<br>
                            • Nunca compartas esta contraseña con nadie.
                          </p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <!-- Pie -->
                <tr>
                  <td style="background:#f8f9fa;padding:20px 40px;text-align:center;border-top:1px solid #eee;">
                    <p style="margin:0;color:#999;font-size:12px;">
                      © {$año} {$fromName} · Este es un correo automático, por favor no respondas.
                    </p>
                  </td>
                </tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;

        $mail->AltBody = "Hola {$nombre},\n\nTu contraseña temporal es: {$passTemp}\n\n"
                       . "Al ingresar serás redirigido para cambiarla.\n\n"
                       . "Si no solicitaste este cambio, ignora este correo.\n\n"
                       . "— {$fromName}";

        $mail->send();
        return true;

    } catch (MailException $e) {
        error_log('[MailHelper] Error al enviar correo a ' . $destinatario . ': ' . $mail->ErrorInfo);
        return false;
    }
}
