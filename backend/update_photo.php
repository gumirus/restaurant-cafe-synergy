<?php
require_once __DIR__ . '/config/db.php';
$pdo->prepare("UPDATE dishes SET image = 'uploads/dishes/13-ramen.webp' WHERE id = 13")->execute();
echo "✅ Фото обновлено!";
