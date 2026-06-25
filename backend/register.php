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

// Валидация
$errors = [];

// Если email указан — проверяем, что не занят
if (!empty($email)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email уже используется';
    }
}

// Телефон — обязателен
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
    if ($isAjax || $isJson) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
    } else {
        echo '<h2>Ошибки регистрации:</h2><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/register.php">Назад</a>';
    }
    exit;
}

// Регистрируем
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
