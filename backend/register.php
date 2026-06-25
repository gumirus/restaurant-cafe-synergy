<?php
// =============================================
// РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isJson = !$isAjax && (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'json') !== false);

if (!$isAjax && !$isJson) {
    header('Content-Type: text/html; charset=utf-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax || $isJson) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    } else {
        redirect('../frontend/register.php');
    }
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$verify_code = trim($_POST['verify_code'] ?? ''); // код подтверждения (опционально)

// Валидация
$errors = [];

if (!empty($email)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email уже используется';
    }
}

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

// Если указан код подтверждения — проверяем его в БД
if (!empty($verify_code)) {
    $method = !empty($email) ? 'email' : 'sms';
    $field = $method === 'email' ? 'email' : 'phone';
    $value = $method === 'email' ? $email : $phone;

    $stmt = $pdo->prepare("
        SELECT id FROM verification_codes
        WHERE $field = ? AND code = ? AND verified = 0 AND expires_at > NOW()
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$value, $verify_code]);
    if (!$stmt->fetch()) {
        $errors[] = 'Неверный или просроченный код подтверждения';
    }
}

if (!empty($errors)) {
    if ($isAjax || $isJson) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
    } else {
        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка регистрации — Bean Scene</title>
        <link rel="stylesheet" href="../frontend/css/color.css">
        <link rel="stylesheet" href="../frontend/css/style.css">
        <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--color-bg-section); padding:20px; }
        .error-card { background:var(--color-bg); border-radius:16px; padding:40px; max-width:420px; width:100%; box-shadow:0 10px 40px rgba(0,0,0,0.08); text-align:center; }
        .error-card .icon { font-size:3rem; margin-bottom:15px; }
        .error-card h2 { font-family:var(--font-heading); font-size:1.5rem; color:var(--color-text); margin-bottom:15px; }
        .error-card ul { list-style:none; padding:0; margin:0 0 20px; }
        .error-card li { padding:10px; margin-bottom:8px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#dc2626; font-size:0.9rem; }
        </style></head><body>
        <div class="error-card">
            <div class="icon">😕</div>
            <h2>Ошибка регистрации</h2>
            <ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/register.php" class="btn">Попробовать снова</a>
        </div></body></html>';
    }
    exit;
}

// Создаём пользователя
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (phone, email, password, access_rights_id) VALUES (?, ?, ?, 2)");
$stmt->execute([$phone, $email ?: null, $hashedPassword]);

$userId = $pdo->lastInsertId();
$_SESSION['user_id'] = $userId;
$_SESSION['user_phone'] = $phone;
$_SESSION['access_rights'] = 'USER';

if ($isAjax || $isJson) {
    echo json_encode(['success' => true, 'message' => 'Регистрация успешна', 'redirect' => '../frontend/index.php']);
} else {
    redirect('../frontend/index.php');
}
