<?php
// =============================================
// ОФОРМЛЕНИЕ ЗАКАЗА
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $address = trim($_POST['address'] ?? '');

    if (empty($address)) {
        die('Укажите адрес доставки');
    }

    try {
        $pdo->beginTransaction();

        // Получить товары из корзины
        $stmt = $pdo->prepare("
            SELECT sc.dish_id, sc.count, d.price
            FROM shopping_cart sc
            JOIN dishes d ON sc.dish_id = d.id
            WHERE sc.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        if (empty($cartItems)) {
            throw new Exception('Корзина пуста');
        }

        // Посчитать сумму
        $totalPrice = array_reduce($cartItems, function($sum, $item) {
            return $sum + ($item['price'] * $item['count']);
        }, 0);

        // Создать заказ
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, address, total_price, status)
            VALUES (?, ?, ?, 'new')
        ");
        $stmt->execute([$userId, $address, $totalPrice]);
        $orderId = $pdo->lastInsertId();

        // Добавить позиции заказа
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, dish_id, count, price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($cartItems as $item) {
            $stmt->execute([
                $orderId,
                $item['dish_id'],
                $item['count'],
                $item['price']
            ]);
        }

        // Очистить корзину
        $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        $pdo->commit();

        echo "<h2>Заказ №$orderId оформлен!</h2>";
        echo "<p>Сумма заказа: $totalPrice ₽</p>";
        echo "<p>Адрес доставки: $address</p>";
        echo '<a href="../frontend/index.html">На главную</a>';

    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Ошибка оформления заказа: ' . $e->getMessage();
    }
}
