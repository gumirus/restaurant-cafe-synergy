<?php
// =============================================
// АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ
// Поддерживает вход по телефону или email
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['phone'] ?? '');  // может быть телефон или email
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($login) || empty($password)) {
        $errors[] = 'Заполните все поля';
    }

    if (empty($errors)) {
        // Поиск по телефону или email
        $stmt = $pdo->prepare("
            SELECT u.id, u.phone, u.email, u.password, u.position, ar.name as access_rights
            FROM users u
            JOIN access_rights ar ON u.access_rights_id = ar.id
            WHERE u.phone = ? OR u.email = ?
        ");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_email'] = $user['email'] ?? '';
            $_SESSION['access_rights'] = $user['access_rights'];
            $_SESSION['user_position'] = $user['position'] ?? '';

            // Админа — в админ-панель, сотрудника — в панель сотрудника, пользователя — на главную
            if ($user['access_rights'] === 'ADMIN') {
                redirect('admin/index.php');
            } elseif ($user['access_rights'] === 'EMPLOYEE') {
                redirect('employee/index.php');
            } else {
                redirect('../frontend/index.php');
            }
        } else {
            $errors[] = 'Неверный логин (телефон/email) или пароль';
        }
    }

    if (!empty($errors)) {
        echo '<h2>Ошибка входа:</h2><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/login.php">Назад</a>';
    }
}
