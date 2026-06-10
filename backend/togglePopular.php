<?php
// =============================================
// Toggle Popular — переключение флага "популярное"
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: admin/index.php?page=menu&error=Неверный ID блюда');
    exit;
}

// Получаем текущее значение is_popular
$stmt = $pdo->prepare("SELECT is_popular FROM dishes WHERE id = ?");
$stmt->execute([$id]);
$dish = $stmt->fetch();

if (!$dish) {
    header('Location: admin/index.php?page=menu&error=Блюдо не найдено');
    exit;
}

// Переключаем
$newValue = $dish['is_popular'] ? 0 : 1;
$stmt = $pdo->prepare("UPDATE dishes SET is_popular = ? WHERE id = ?");
$stmt->execute([$newValue, $id]);

header('Location: admin/index.php?page=menu&success=' . ($newValue ? 'Блюдо добавлено в популярные' : 'Блюдо убрано из популярных'));
exit;
