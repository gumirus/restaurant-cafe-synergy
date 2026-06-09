<?php
// =============================================
// УДАЛЕНИЕ АКЦИИ (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$id]);
}

redirect('admin/index.php?page=promotions&success=Акция удалена');
