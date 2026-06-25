<?php
// =============================================
// СБРОС ПАРОЛЯ (после подтверждения кода)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($login) || empty($password) || strlen($password) < 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Заполните все поля. Пароль минимум 4 символа']);
    exit;
}

// Проверяем, что код был подтверждён (через verify_code.php)
// Ищем в verification_codes последний подтверждённый код
$field = strpos($login, '@') !== false ? 'email' : 'phone';
$stmt = $pdo->prepare("
    SELECT id FROM verification_codes 
    WHERE $field = ? AND verified = 1 
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$login]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Требуется подтверждение кода']);
    exit;
}

// Находим пользователя по телефону или email
$stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? OR email = ?");
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
    exit;
}

// Обновляем пароль
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hashedPassword, $user['id']]);

// Автоматический вход
$_SESSION['user_id'] = $user['id'];

echo json_encode(['success' => true, 'message' => 'Пароль сброшен', 'redirect' => '../frontend/index.php']);