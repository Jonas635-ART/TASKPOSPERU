<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/conexion.php';

class Mailer {

    /**
     * Envía el código de 6 dígitos para restablecer la contraseña mediante SMTP (Gmail / STARTTLS o SSL).
     */
    public static function enviarCodigoRecuperacion(string $destinatario, string $nombre, string $codigo, int $minutos = 15): array {
        $env      = Database::getEnv();
        $host     = trim((string)($env['SMTP_HOST'] ?? ''));
        $port     = (int)($env['SMTP_PORT'] ?? 587);
        $user     = trim((string)($env['SMTP_USER'] ?? ''));
        $pass     = str_replace(' ', '', trim((string)($env['SMTP_PASS'] ?? '')));
        $from     = trim((string)($env['SMTP_FROM'] ?? $user));
        $fromName = trim((string)($env['SMTP_FROM_NAME'] ?? 'Soporte TASKPOSPERU'));

        if ($host === '' || $user === '' || $pass === '') {
            error_log('Mailer: Faltan variables SMTP en .env');
            return ['success' => false, 'error' => 'El servicio de correo SMTP no está configurado en el servidor.'];
        }

        if ($from === '') {
            $from = $user;
        }

        $asunto = 'Código de recuperación de contraseña - PosPerú';
        $html   = self::construirPlantillaCodigo($nombre, $codigo, $minutos);

        return self::enviarSmtp($host, $port, $user, $pass, $from, $fromName, $destinatario, $nombre, $asunto, $html);
    }

    private static function construirPlantillaCodigo(string $nombre, string $codigo, int $minutos): string {
        $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $codigoSeguro = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
                    <tr>
                        <td style="background:#312e81;padding:24px 28px;color:#ffffff;">
                            <h1 style="margin:0;font-size:20px;font-weight:700;">PosPerú <span style="color:#a5b4fc;font-size:13px;">PRO</span></h1>
                            <p style="margin:4px 0 0;font-size:12px;color:#c7d2fe;">Restablecimiento de contraseña</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px;font-size:15px;">Hola <strong>{$nombreSeguro}</strong>,</p>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.5;color:#475569;">
                                Recibimos una solicitud para restablecer tu contraseña. Ingresa el siguiente código de verificación en la pantalla de recuperación:
                            </p>
                            <div style="text-align:center;margin:24px 0;">
                                <span style="display:inline-block;padding:14px 28px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;font-family:'Courier New',monospace;font-size:28px;font-weight:700;letter-spacing:6px;color:#3730a3;">
                                    {$codigoSeguro}
                                </span>
                            </div>
                            <p style="margin:0 0 12px;font-size:13px;color:#64748b;">
                                Este código es válido por <strong>{$minutos} minutos</strong> y es de un solo uso.
                            </p>
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
                                Si no solicitaste este cambio, puedes ignorar este mensaje con seguridad.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:16px 28px;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;text-align:center;">
                            Enviado automáticamente por PosPerú PRO
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function enviarSmtp(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $from,
        string $fromName,
        string $to,
        string $toName,
        string $subject,
        string $htmlBody
    ): array {
        $protocolo = ($port === 465) ? 'ssl://' : 'tcp://';
        $context   = stream_context_create([
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ],
        ]);

        $socket = @stream_socket_client("{$protocolo}{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            error_log("Mailer: No se pudo conectar a {$host}:{$port} - ({$errno}) {$errstr}");
            return ['success' => false, 'error' => 'No se pudo conectar al servidor de correo.'];
        }

        stream_set_timeout($socket, 15);

        try {
            self::expect($socket, 220);
            self::command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

            if ($port !== 465) {
                self::command($socket, 'STARTTLS', 220);
                $cryptoOk = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$cryptoOk) {
                    throw new RuntimeException('No se pudo iniciar cifrado TLS con el servidor SMTP.');
                }
                self::command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);
            }

            self::command($socket, 'AUTH LOGIN', 334);
            self::command($socket, base64_encode($user), 334);
            self::command($socket, base64_encode($pass), 235);

            self::command($socket, "MAIL FROM:<{$from}>", 250);
            self::command($socket, "RCPT TO:<{$to}>", [250, 251]);
            self::command($socket, 'DATA', 354);

            $encodedSubject  = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
            $encodedToName   = '=?UTF-8?B?' . base64_encode($toName !== '' ? $toName : $to) . '?=';

            $headers = [
                'Date: ' . date('r'),
                "From: {$encodedFromName} <{$from}>",
                "To: {$encodedToName} <{$to}>",
                "Subject: {$encodedSubject}",
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];

            $bodyChunked = chunk_split(base64_encode($htmlBody));
            $payload     = implode("\r\n", $headers) . "\r\n\r\n" . $bodyChunked . "\r\n.";

            // Escapar puntos al inicio de línea por RFC 5321 (dot-stuffing)
            $payload = preg_replace('/^\./m', '..', $payload);
            // Restaurar el terminador final "."
            $payload = substr($payload, 0, -2) . '.';

            fwrite($socket, $payload . "\r\n");
            self::expect($socket, 250);
            self::command($socket, 'QUIT', 221);

            fclose($socket);
            return ['success' => true, 'error' => ''];
        } catch (Throwable $e) {
            error_log('Mailer SMTP error: ' . $e->getMessage());
            @fclose($socket);
            return ['success' => false, 'error' => 'No se pudo enviar el correo. Verifica la configuración SMTP.'];
        }
    }

    private static function command($socket, string $cmd, $expectedCodes): string {
        fwrite($socket, $cmd . "\r\n");
        return self::expect($socket, $expectedCodes);
    }

    private static function expect($socket, $expectedCodes): string {
        $expected = is_array($expectedCodes) ? $expectedCodes : [$expectedCodes];
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException("Respuesta SMTP inesperada ({$code}): " . trim($response));
        }

        return $response;
    }
}
