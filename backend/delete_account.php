<?php
// =============================================
// УДАЛЕНИЕ АККАУНТА ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
}

if (!isLoggedIn()) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Не авторизован']);
        exit;
    }
    header('Location: ../frontend/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_text = $_POST['confirm_text'] ?? '';

    $errors = [];

    if (trim($confirm_text) !== 'УДАЛИТЬ') {
        $errors[] = 'Введите слово УДАЛИТЬ для подтверждения';
    }

    if (empty($password)) {
        $errors[] = 'Введите пароль';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Неверный пароль';
        }
    }

    if (empty($errors)) {
        $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        $pdo->prepare("DELETE FROM order_feedback WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        $pdo->prepare("DELETE FROM bookings WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        $pdo->prepare("DELETE FROM booking_feedback WHERE booking_id IN (SELECT id FROM bookings WHERE user_id = ?)")->execute([$_SESSION['user_id']]);

        $orders = $pdo->prepare("SELECT id FROM orders WHERE user_id = ?");
        $orders->execute([$_SESSION['user_id']]);
        foreach ($orders as $order) {
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order['id']]);
        }
        $pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$_SESSION['user_id']]);

        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_SESSION['user_id']]);

        session_destroy();

        if ($isAjax) {
            echo json_encode(['success' => true, 'redirect' => '../frontend/index.php']);
            exit;
        }
        redirect('../frontend/index.php');
    }

    // Если есть ошибки
    if (!empty($errors)) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка — Bean Scene</title>
        <link rel="stylesheet" href="../frontend/css/color.css">
        <link rel="stylesheet" href="../frontend/css/style.css">
        <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--color-bg-section); padding:20px; }
        .error-card { background:var(--color-bg); border-radius:16px; padding:40px; max-width:420px; width:100%; box-shadow:0 10px 40px rgba(0,0,0,0.08); text-align:center; }
        .error-card h2 { font-family:var(--font-heading); font-size:1.5rem; color:var(--color-text); margin-bottom:15px; }
        .error-card ul { list-style:none; padding:0; margin:0 0 20px; }
        .error-card li { padding:10px; margin-bottom:8px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#dc2626; font-size:0.9rem; }
        </style></head><body>
        <div class="error-card">
            <h2>😕 Не удалось удалить</h2>
            <ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/profile.php" class="btn">Назад</a>
        </div></body></html>';
        exit;
    }
}
