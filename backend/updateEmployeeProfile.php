<?php
// =============================================
// ОБНОВЛЕНИЕ ПРОФИЛЯ (админ/сотрудник)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isStaff()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $position = trim($_POST['position'] ?? '');

    // Проверяем, что редактирует свой профиль
    if ($userId !== (int)$_SESSION['user_id']) {
        $redirectPage = isAdmin() ? 'admin' : 'employee';
        redirect($redirectPage . '/index.php?page=profile&error=Нельзя+редактировать+чужой+профиль');
    }

    // Обработка загрузки аватара
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../frontend/uploads/';
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
            $avatar = $filename;
        }
    }

    // Обновляем БД
    if ($avatar) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, position = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $position, $avatar, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, position = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $position, $userId]);
    }

    // Редирект обратно
    $redirectPage = isAdmin() ? 'admin' : 'employee';
    redirect($redirectPage . '/index.php?page=profile&success=Профиль+обновлён');
} else {
    $redirectPage = isAdmin() ? 'admin' : 'employee';
    redirect($redirectPage . '/index.php?page=profile');
}
