<?php
// =============================================
// АДМИН-ПАНЕЛЬ
// =============================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель — Ресторан</title>
    <link rel="stylesheet" href="../../frontend/css/style.css">
</head>
<body>
    <div class="container" style="padding-top: 100px;">
        <h1>Админ-панель</h1>
        <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['user_phone']) ?></p>
        <a href="../logout.php">Выйти</a>

        <hr>

        <h2>Управление меню</h2>
        <form method="POST" action="../createProduct.php" enctype="multipart/form-data">
            <select name="category_id" required>
                <option value="">Выберите категорию</option>
                <?php
                $stmt = $pdo->query("SELECT * FROM categories");
                while ($cat = $stmt->fetch()) {
                    echo "<option value=\"{$cat['id']}\">{$cat['name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="name" placeholder="Название блюда" required>
            <textarea name="description" placeholder="Описание"></textarea>
            <input type="number" name="price" placeholder="Цена" step="0.01" required>
            <input type="number" name="weight" placeholder="Вес (гр)">
            <input type="file" name="image">
            <button type="submit" class="btn">Добавить блюдо</button>
        </form>

        <hr>

        <h2>Список заказов</h2>
        <table border="1" cellpadding="10" style="width:100%; border-collapse: collapse;">
            <tr>
                <th>№</th>
                <th>Клиент</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Дата</th>
            </tr>
            <?php
            $stmt = $pdo->query("
                SELECT o.id, u.phone, o.total_price, o.status, o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
            ");
            while ($order = $stmt->fetch()) {
                echo "<tr>
                    <td>{$order['id']}</td>
                    <td>{$order['phone']}</td>
                    <td>{$order['total_price']} ₽</td>
                    <td>{$order['status']}</td>
                    <td>{$order['created_at']}</td>
                </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
