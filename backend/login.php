<?php
// =============================================
// АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($phone) || empty($password)) {
        $errors[] = 'Заполните все поля';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT u.id, u.phone, u.password, ar.name as access_rights
            FROM users u
            JOIN access_rights ar ON u.access_rights_id = ar.id
            WHERE u.phone = ?
        ");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['access_rights'] = $user['access_rights'];

            redirect('../frontend/index.html');
        } else {
            $errors[] = 'Неверный номер телефона или пароль';
        }
    }

    if (!empty($errors)) {
        echo '<h2>Ошибка входа:</h2><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/login.html">Назад</a>';
    }
}
