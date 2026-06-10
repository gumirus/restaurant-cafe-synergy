<?php
// =============================================
// СОЗДАНИЕ СОТРУДНИКА (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($name)) $errors[] = 'Укажите имя сотрудника';
    if (empty($phone)) $errors[] = 'Укажите телефон';
    if (empty($position)) $errors[] = 'Укажите должность';
    if (empty($password)) $errors[] = 'Укажите пароль';

    // Проверка на дубликат телефона
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Пользователь с таким телефоном уже существует';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (phone, name, position, password, access_rights_id)
            VALUES (?, ?, ?, ?, 3)
        ");
        $stmt->execute([$phone, $name, $position, $hash]);

        redirect('admin/index.php?page=users&success=Сотрудник+' . urlencode($name) . '+создан');
    } else {
        $errorMsg = implode(', ', $errors);
        redirect('admin/index.php?page=users&error=' . urlencode($errorMsg));
    }
} else {
    redirect('admin/index.php?page=users');
}
