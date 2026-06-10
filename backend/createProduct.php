<?php
// =============================================
// ДОБАВЛЕНИЕ БЛЮДА (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $weight = (int)($_POST['weight'] ?? 0);
    $image = null;

    // Обработка изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('dish_') . '.' . $ext;
        $uploadPath = __DIR__ . '/../frontend/uploads/dishes/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $image = 'uploads/dishes/' . $newName;
        }
    }

    // Вставка в БД
    $stmt = $pdo->prepare("
        INSERT INTO dishes (category_id, name, description, price, weight, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$categoryId, $name, $description, $price, $weight, $image]);

    header('Location: admin/index.php?page=menu&success=Блюдо+добавлено');
    exit;
}
