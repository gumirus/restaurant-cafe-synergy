<?php
// =============================================
// ПРОВЕРКА КОДА ПОДТВЕРЖДЕНИЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$method = $_POST['method'] ?? '';
$value = trim($_POST['value'] ?? '');
$code = trim($_POST['code'] ?? '');

if (empty($method) || empty($value) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

$field = ($method === 'email') ? 'email' : 'phone';

// Ищем действующий код
$stmt = $pdo->prepare("
    SELECT id, code FROM verification_codes 
    WHERE $field = ? AND code = ? AND verified = 0 AND expires_at > NOW()
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$value, $code]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Неверный или просроченный код']);
    exit;
}

// Помечаем как подтверждённый
$stmt = $pdo->prepare("UPDATE verification_codes SET verified = 1 WHERE id = ?");
$stmt->execute([$row['id']]);

// Сохраняем в сессию
$_SESSION['verified_' . $field] = $value;

echo json_encode(['success' => true, 'message' => 'Код подтверждён']);