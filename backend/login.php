<?php
// =============================================
// АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($login) || empty($password)) {
        $errors[] = 'Заполните все поля';
    }

    if (empty($errors)) {
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

            $redirect = ($user['access_rights'] === 'ADMIN') ? 'admin/index.php'
                : (($user['access_rights'] === 'EMPLOYEE') ? 'employee/index.php' : '../frontend/index.php');

            if ($isAjax) {
                echo json_encode(['success' => true, 'redirect' => $redirect]);
                exit;
            }
            redirect($redirect);
        } else {
            $errors[] = 'Неверный логин (телефон/email) или пароль';
        }
    }

    if ($isAjax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    if (!empty($errors)) {
        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка входа — Bean Scene</title>
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
        <div class="error-card"><div class="icon">😕</div><h2>Ошибка входа</h2><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul><a href="../frontend/login.php" class="btn">Попробовать снова</a>
        </div></body></html>';
    }
}
