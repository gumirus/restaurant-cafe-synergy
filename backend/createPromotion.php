<?php
// =============================================
// ДОБАВЛЕНИЕ АКЦИИ (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if (empty($title)) {
        redirect('admin/index.php?page=promotions&error=Заполните название акции');
    }

    $stmt = $pdo->prepare("
        INSERT INTO promotions (title, description, start_date, end_date)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $startDate ?: null, $endDate ?: null]);

    redirect('admin/index.php?page=promotions&success=Акция добавлена');
}
