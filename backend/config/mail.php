<?php
// =============================================
// EMAIL CONFIG — SMTP через Яндекс
// =============================================

define('SMTP_HOST', 'smtp.yandex.ru');
define('SMTP_PORT', 465);
$smtpUser = getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? ($_SERVER['SMTP_USER'] ?? ''));
$smtpPass = getenv('SMTP_PASSWORD') ?: ($_ENV['SMTP_PASSWORD'] ?? ($_SERVER['SMTP_PASSWORD'] ?? ''));
define('SMTP_USER', $smtpUser);
define('SMTP_PASS', $smtpPass);

function sendMail(string $to, string $subject, string $body): bool {
    if (empty(SMTP_USER) || empty(SMTP_PASS)) {
        error_log("SMTP not configured");
        return false;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=utf-8',
        'From: ' . SMTP_USER,
        'Reply-To: ' . SMTP_USER,
        'X-Mailer: PHP/' . phpversion(),
    ];

    return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}

function sendSmtpMail(string $to, string $subject, string $body): bool {
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $sock = @stream_socket_client(
        'ssl://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno, $errstr, 15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$sock) {
        error_log("SMTP connection failed: $errstr ($errno)");
        return false; // don't fallback to mail(), it won't work on Railway
    }

    $resp = fgets($sock, 512);

    fwrite($sock, "EHLO server\r\n");
    while ($line = fgets($sock, 512)) {
        if (substr($line, 3, 1) === ' ') break;
    }

    fwrite($sock, "AUTH LOGIN\r\n"); $resp = fgets($sock, 512);
    if (substr($resp, 0, 3) !== '334') {
        error_log("SMTP AUTH LOGIN failed: $resp");
        fclose($sock); return false;
    }

    fwrite($sock, base64_encode(SMTP_USER) . "\r\n"); $resp = fgets($sock, 512);
    if (substr($resp, 0, 3) !== '334') {
        error_log("SMTP USER failed: $resp");
        fclose($sock); return false;
    }

    fwrite($sock, base64_encode(SMTP_PASS) . "\r\n"); $resp = fgets($sock, 512);
    if (substr($resp, 0, 3) !== '235') {
        error_log("SMTP auth failed: $resp");
        fclose($sock);
        return false;
    }

    fwrite($sock, "MAIL FROM:<" . SMTP_USER . ">\r\n"); fgets($sock, 512);
    fwrite($sock, "RCPT TO:<$to>\r\n"); fgets($sock, 512);
    fwrite($sock, "DATA\r\n"); fgets($sock, 512);

    $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\nFrom: " . SMTP_USER . "\r\nTo: $to\r\nSubject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n\r\n";
    fwrite($sock, $headers . $body . "\r\n.\r\n"); fgets($sock, 512);
    fwrite($sock, "QUIT\r\n");
    fclose($sock);

    return true;
}

function sendVerificationEmail(string $email, string $code): bool {
    if (empty(SMTP_USER)) {
        error_log("SMTP_USER not configured - email cannot be sent");
        return false;
    }
    $subject = 'Код подтверждения — Bean Scene';
    $body = "Здравствуйте!\n\nВаш код подтверждения: $code\n\nКод действителен 10 минут.\n\nС уважением,\nРесторан Bean Scene";
    return sendSmtpMail($email, $subject, $body);
}
