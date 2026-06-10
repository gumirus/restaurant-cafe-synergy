<?php
// =============================================
// Toggle Special — переключение флага "фирменное блюдо"
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

$stmt = $pdo->prepare("SELECT is_special FROM dishes WHERE id = ?");
$stmt->execute([$id]);
$dish = $stmt->fetch();

if (!$dish) {
    header('Location: admin/index.php?page=menu&error=Блюдо не найдено');
    exit;
}

$newValue = $dish['is_special'] ? 0 : 1;
$stmt = $pdo->prepare("UPDATE dishes SET is_special = ? WHERE id = ?");
$stmt->execute([$newValue, $id]);

header('Location: admin/index.php?page=menu&success=' . ($newValue ? 'Блюдо добавлено в фирменные' : 'Блюдо убрано из фирменных'));
exit;
