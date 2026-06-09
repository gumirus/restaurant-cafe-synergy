<?php
// =============================================
// ЗАГРУЗКА ИЗОБРАЖЕНИЙ
// =============================================

require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $uploadDir = __DIR__ . '/uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5 MB

    // Проверка ошибок
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Ошибка загрузки файла');
    }

    // Проверка типа
    if (!in_array($file['type'], $allowedTypes)) {
        die('Разрешены только JPG, PNG, GIF, WebP');
    }

    // Проверка размера
    if ($file['size'] > $maxSize) {
        die('Файл слишком большой (макс. 5 MB)');
    }

    // Генерация уникального имени
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid('img_') . '.' . $ext;
    $uploadPath = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo "Файл загружен: uploads/$newName";
    } else {
        echo 'Ошибка сохранения файла';
    }
}
