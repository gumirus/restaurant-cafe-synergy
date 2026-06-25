<?php
// =============================================
// РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ
// Поддерживает два способа: email и sms (демо)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$verify_method = $_POST['verify_method'] ?? ($email ? 'email' : 'sms');

// Валидация
$errors = [];

// Проверка: хотя бы один способ подтверждения
if ($verify_method === 'email' && empty($email)) {
    $errors[] = 'Укажите email для подтверждения по почте';
}
if ($verify_method === 'sms' && (empty($phone) || strlen($phone) < 10)) {
    $errors[] = 'Укажите номер телефона для подтверждения по SMS';
}

// Если email указан — проверяем, что не занят
if (!empty($email)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email уже используется';
    }
}

// Телефон — обязателен для входа
if (empty($phone) || strlen($phone) < 10) {
    $errors[] = 'Введите корректный номер телефона';
} else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        $errors[] = 'Пользователь с таким номером уже существует';
    }
}

if (empty($password) || strlen($password) < 4) {
    $errors[] = 'Пароль должен быть не менее 4 символов';
}

if ($password !== $password_confirm) {
    $errors[] = 'Пароли не совпадают';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Регистрируем
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (phone, email, password, access_rights_id)
    VALUES (?, ?, ?, 2)
");
$stmt->execute([$phone, $email ?: null, $hashedPassword]);

$userId = $pdo->lastInsertId();
$_SESSION['user_id'] = $userId;
$_SESSION['user_phone'] = $phone;
$_SESSION['access_rights'] = 'USER';

echo json_encode([
    'success' => true,
    'message' => 'Регистрация успешна',
    'redirect' => '../frontend/index.php'
]);
