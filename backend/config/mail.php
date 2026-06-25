<?php
// =============================================
// EMAIL CONFIG — SMTP через Яндекс
// =============================================

define('SMTP_HOST', 'smtp.yandex.ru');
define('SMTP_PORT', 465);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASSWORD') ?: '');

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
        return sendMail($to, $subject, $body); // fallback
    }

    $resp = fgets($sock, 512);
    
    fwrite($sock, "EHLO server\r\n");
    while ($line = fgets($sock, 512)) {
        if (substr($line, 3, 1) === ' ') break;
    }

    fwrite($sock, "AUTH LOGIN\r\n"); fgets($sock, 512);
    fwrite($sock, base64_encode(SMTP_USER) . "\r\n"); fgets($sock, 512);
    fwrite($sock, base64_encode(SMTP_PASS) . "\r\n"); $resp = fgets($sock, 512);

    if (substr($resp, 0, 3) !== '235') {
        error_log("SMTP auth failed: $resp");
        fclose($sock);
        return sendMail($to, $subject, $body);
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
