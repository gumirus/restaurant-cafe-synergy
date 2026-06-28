<?php
// =============================================
// АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/rate_limit.php';

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

function jsonError($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['success' => false, 'errors' => is_array($message) ? $message : [$message]]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $isAjax ? jsonError('CSRF token validation failed') : die('CSRF token validation failed');
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkRateLimit('login_' . $ip)) {
        $isAjax ? jsonError('Слишком много попыток входа. Подождите 5 минут.') : die('Слишком много попыток входа. Подождите 5 минут.');
    }

    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($phone) || empty($password)) {
        $errors[] = 'Заполните все поля';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT u.id, u.phone, u.password, u.position, ar.name as access_rights
            FROM users u
            JOIN access_rights ar ON u.access_rights_id = ar.id
            WHERE u.phone = ?
        ");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['access_rights'] = $user['access_rights'];
            $_SESSION['user_position'] = $user['position'] ?? '';

            $redirectUrl = '../frontend/index.php';
            if ($user['access_rights'] === 'ADMIN') {
                $redirectUrl = 'admin/index.php';
            } elseif ($user['access_rights'] === 'EMPLOYEE') {
                $redirectUrl = 'employee/index.php';
            }

            if ($isAjax) {
                echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
                exit;
            } else {
                redirect($redirectUrl);
            }
        } else {
            $errors[] = 'Неверный номер телефона или пароль';
        }
    }

    if (!empty($errors)) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            echo '<h2>Ошибка входа:</h2><ul>';
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo '</ul><a href="../frontend/login.php">Назад</a>';
        }
    }
}
