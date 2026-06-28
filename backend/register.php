<?php
// =============================================
// РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed');
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkRateLimit('register_' . $ip, 3, 300)) {
        die('Слишком много попыток регистрации. Подождите 5 минут.');
    }
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация
    $errors = [];

    if (empty($phone) || strlen($phone) < 10) {
        $errors[] = 'Введите корректный номер телефона';
    }

    if (empty($password) || strlen($password) < 4) {
        $errors[] = 'Пароль должен быть не менее 4 символов';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Пароли не совпадают';
    }

    // Проверка на существующего пользователя
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);

        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким номером уже существует';
        }
    }

    // Регистрация
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (phone, email, password, access_rights_id)
            VALUES (?, ?, ?, 2)
        ");
        $stmt->execute([$phone, $email ?: null, $hashedPassword]);

        // Автоматический вход
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['access_rights'] = 'USER';

        redirect('../frontend/index.html');
    }

    // Если есть ошибки
    if (!empty($errors)) {
        echo '<h2>Ошибки регистрации:</h2><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/register.html">Назад</a>';
    }
}
