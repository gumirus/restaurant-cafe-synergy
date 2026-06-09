<?php
// =============================================
// УДАЛЕНИЕ БЛЮДА (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    // Получаем информацию о файле изображения
    $stmt = $pdo->prepare("SELECT image FROM dishes WHERE id = ?");
    $stmt->execute([$id]);
    $dish = $stmt->fetch();

    // Удаляем файл изображения
    if ($dish && $dish['image']) {
        $filePath = __DIR__ . '/uploads/' . $dish['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Удаляем из БД
    $stmt = $pdo->prepare("DELETE FROM dishes WHERE id = ?");
    $stmt->execute([$id]);
}

redirect('admin/index.php?page=menu');
