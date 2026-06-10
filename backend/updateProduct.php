<?php
// =============================================
// РЕДАКТИРОВАНИЕ БЛЮДА (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $weight = (int)($_POST['weight'] ?? 0);
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $isSpecial = isset($_POST['is_special']) ? 1 : 0;
    $image = $_POST['existing_image'] ?? null;

    // Обработка нового изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('dish_') . '.' . $ext;
        $uploadPath = __DIR__ . '/../frontend/uploads/dishes/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $image = 'uploads/dishes/' . $newName;
        }
    }

    // Обновление в БД
    $stmt = $pdo->prepare("
        UPDATE dishes 
        SET category_id = ?, name = ?, description = ?, ingredients = ?, price = ?, weight = ?, image = ?, is_popular = ?, is_special = ?
        WHERE id = ?
    ");
    $stmt->execute([$categoryId, $name, $description, $ingredients, $price, $weight, $image, $isPopular, $isSpecial, $id]);

    header('Location: admin/index.php?page=menu&success=Блюдо+обновлено');
    exit;
}
