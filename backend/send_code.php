<?php
// =============================================
// ОТПРАВКА КОДА ПОДТВЕРЖДЕНИЯ
// Поддерживает email и sms (демо-режим)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mail.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$method = $_POST['method'] ?? '';   // 'email' or 'sms'
$value = trim($_POST['value'] ?? ''); // email or phone

if (empty($method) || empty($value)) {
    echo json_encode(['success' => false, 'message' => 'Укажите метод и email/телефон']);
    exit;
}

// Генерация 6-значного кода
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Удаляем старые коды для этого адреса
$field = ($method === 'email') ? 'email' : 'phone';
$stmt = $pdo->prepare("DELETE FROM verification_codes WHERE $field = ?");
$stmt->execute([$value]);

// Сохраняем новый код
$stmt = $pdo->prepare("
    INSERT INTO verification_codes (phone, email, code, method, expires_at)
    VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
");
$phone = ($method === 'sms') ? $value : null;
$email = ($method === 'email') ? $value : null;
$stmt->execute([$phone, $email, $code, $method]);

// Отправляем код
$sent = false;
$demoCode = null;

if ($method === 'email') {
    $sent = sendVerificationEmail($value, $code);
    $message = $sent ? 'Код отправлен на почту' : 'Не удалось отправить код';
} else {
    // SMS — демо-режим (показываем код на экране)
    $demoCode = $code;
    $sent = true;
    $message = 'Демо-режим: код показан ниже';
}

echo json_encode([
    'success' => $sent,
    'message' => $message,
    'demo_code' => $demoCode,
    'debug_info' => $sent ? null : 'SMTP не настроен. Укажите SMTP_USER и SMTP_PASSWORD в .env'
]);