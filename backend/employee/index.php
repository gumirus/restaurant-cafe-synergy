<?php
// =============================================
// ПАНЕЛЬ СОТРУДНИКА Точка Кипения
// =============================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if (!isEmployee() && !isAdmin()) {
    die('Доступ запрещён');
}

$user = getCurrentUser();
$position = $user['position'] ?? '';

// Определяем тип сотрудника по должности
$isSousChef = ($position === 'Повар' || $position === 'Су-шеф');
$isCook = ($position === 'Повар');
$isWaiter = ($position === 'Официант' || $position === 'Старший официант');

// Статистика
$stats = [];

// Количество заказов сегодня (исключая корзины)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cart'");
$stats['orders_today'] = $stmt->fetch()['count'];

// Количество бронирований сегодня
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(booking_date) = CURDATE()");
$stats['bookings_today'] = $stmt->fetch()['count'];

// Активные заказы (не completed, не cancelled и не cart)
$stmt = $pdo->query("
    SELECT COUNT(*) as count FROM orders 
    WHERE status NOT IN ('completed', 'cancelled', 'cart')
");
$stats['active_orders'] = $stmt->fetch()['count'];

// Заказы на сегодня (исключая корзины)
$stmt = $pdo->query("
    SELECT o.*, u.phone, u.name as user_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE DATE(o.created_at) = CURDATE() AND o.status != 'cart'
    ORDER BY o.created_at DESC
");
$todayOrders = $stmt->fetchAll();

// Бронирования на сегодня
$stmt = $pdo->query("
    SELECT * FROM bookings 
    WHERE DATE(booking_date) = CURDATE()
    ORDER BY booking_time ASC
");
$todayBookings = $stmt->fetchAll();

// Получаем данные сотрудника
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$employee = $stmt->fetch();

$page = $_GET['page'] ?? 'dashboard';

$statusLabels = [
    'pending' => '🕐 Ожидает',
    'confirmed' => '✅ Подтверждён',
    'preparing' => '👨‍🍳 Готовится',
    'ready' => '🍽️ Готов',
    'completed' => '✔️ Выполнен',
    'cancelled' => '❌ Отменён'
];

// Иконка должности
$positionIcon = match(true) {
    $isSousChef || $isCook => '👨‍🍳',
    $isWaiter => '🧑‍💼',
    default => '👤'
};
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель сотрудника — Точка Кипения</title>
    <link rel="stylesheet" href="../admin/css/admin.css">
    <style>
        .employee-badge {
            background: #3498db;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sidebar .employee-badge {
            display: inline-block;
            margin-top: 4px;
        }
        .order-items-list {
            font-size: 0.85rem;
            color: #555;
            max-width: 250px;
        }
        .order-items-list li {
            margin-bottom: 2px;
        }
        .position-badge {
            display: inline-block;
            background: #2ecc71;
            color: #fff;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 4px;
        }
        .recipe-card {
            background: #fff;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: box-shadow 0.2s;
        }
        .recipe-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .recipe-card h3 {
            margin: 0 0 8px;
            color: var(--color-text);
            font-size: 1.1rem;
        }
        .recipe-card .meta {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--color-text-light);
            margin-bottom: 10px;
        }
        .recipe-card .description {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
        }
        .recipe-card .recipe-hint {
            margin-top: 12px;
            padding: 12px 16px;
            background: #f8f9fa;
            border-left: 3px solid var(--color-primary);
            border-radius: 6px;
            font-size: 0.88rem;
            color: #444;
            line-height: 1.5;
        }
        .recipe-card .recipe-hint strong {
            color: var(--color-primary);
        }
        .kitchen-order-card {
            background: #fff;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .kitchen-order-card .order-info {
            flex: 1;
            min-width: 200px;
        }
        .kitchen-order-card .order-info .order-number {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--color-text);
        }
        .kitchen-order-card .order-info .order-items {
            font-size: 0.85rem;
            color: #555;
            margin-top: 4px;
        }
        .kitchen-order-card .order-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .status-btn {
            padding: 6px 14px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .status-btn:hover {
            transform: translateY(-1px);
        }
        .status-btn.cooking {
            background: #f39c12;
            color: #fff;
        }
        .status-btn.ready {
            background: #2ecc71;
            color: #fff;
        }
        .status-btn.done {
            background: #3498db;
            color: #fff;
        }
    </style>
</head>
<body>

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>☕ ТОЧКА КИПЕНИЯ</h2>
            <p>Панель сотрудника</p>
            <span class="employee-badge"><?= $positionIcon ?> <?= htmlspecialchars($position ?: $user['phone']) ?></span>
            <?php if ($position): ?>
                <div class="position-badge"><?= htmlspecialchars($position) ?></div>
            <?php endif; ?>
        </div>
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">📊</span>
                <span>Дашборд</span>
            </a>

            <?php if ($isSousChef || $isCook): ?>
                <!-- Повара видят кухонную панель -->
                <a href="?page=kitchen" class="<?= $page === 'kitchen' ? 'active' : '' ?>">
                    <span class="icon">👨‍🍳</span>
                    <span>Кухня</span>
                </a>
            <?php endif; ?>

            <?php if ($isWaiter || $isSousChef): ?>
                <!-- Официанты и су-шефы видят заказы -->
                <a href="?page=orders" class="<?= $page === 'orders' ? 'active' : '' ?>">
                    <span class="icon">📦</span>
                    <span>Заказы</span>
                </a>
            <?php endif; ?>

            <?php if ($isWaiter): ?>
                <!-- Официанты видят бронирования -->
                <a href="?page=bookings" class="<?= $page === 'bookings' ? 'active' : '' ?>">
                    <span class="icon">📅</span>
                    <span>Бронирования</span>
                </a>
            <?php endif; ?>

            <?php if ($isSousChef): ?>
                <!-- Су-шеф видит меню и рецепты -->
                <a href="?page=menu-recipes" class="<?= $page === 'menu-recipes' ? 'active' : '' ?>">
                    <span class="icon">📖</span>
                    <span>Меню и рецепты</span>
                </a>
            <?php endif; ?>

            <a href="?page=profile" class="<?= $page === 'profile' ? 'active' : '' ?>">
                <span class="icon">👤</span>
                <span>Мой профиль</span>
            </a>
            <a href="../logout.php" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 15px;">
                <span class="icon">🚪</span>
                <span>Выйти</span>
            </a>
        </nav>
    </aside>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main-content">

        <div class="page-header">
            <h1>
                <?php
                $titles = [
                    'dashboard' => 'Дашборд',
                    'kitchen' => 'Кухня',
                    'orders' => 'Заказы',
                    'bookings' => 'Бронирования',
                    'menu-recipes' => 'Меню и рецепты',
                    'profile' => 'Мой профиль',
                ];
                echo $titles[$page] ?? 'Дашборд';
                ?>
            </h1>
            <div class="user-info">
                <span><?= $positionIcon ?> <?= htmlspecialchars($position ?: $user['phone']) ?></span>
                <a href="../logout.php">Выйти</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <!-- ==================== DASHBOARD ==================== -->
        <?php if ($page === 'dashboard'): ?>
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['orders_today'] ?></div>
                    <div class="stat-label">📦 Заказов сегодня</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['bookings_today'] ?></div>
                    <div class="stat-label">📅 Бронирований сегодня</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['active_orders'] ?></div>
                    <div class="stat-label">👨‍🍳 Активных заказов</div>
                </div>
            </div>

            <div class="card">
                <h2>Заказы на сегодня</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Клиент</th>
                                <th>Тип</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Оплата</th>
                                <th>Время</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_name'] ?? $order['phone']) ?></td>
                                    <td>
                                        <?php
                                        $typeLabels = ['delivery' => '🏠 Доставка', 'pickup' => '🚶 Самовывоз', 'booking' => '🍽️ Бронь'];
                                        echo $typeLabels[$order['type']] ?? $order['type'];
                                        ?>
                                    </td>
                                    <td><strong><?= number_format($order['total_price'], 0, '', ' ') ?> ₽</strong></td>
                                    <td>
                                        <span class="badge <?= match($order['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'preparing' => 'badge-info',
                                            'ready' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-warning'
                                        } ?>">
                                            <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                            <?= $order['payment_status'] === 'paid' ? '✅ Оплачено' : '⏳ Не оплачено' ?>
                                        </span>
                                    </td>
                                    <td><?= date('H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($todayOrders)): ?>
                                <tr><td colspan="7" style="text-align:center; color: var(--color-text-light);">Сегодня заказов нет</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>Бронирования на сегодня</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Имя</th>
                                <th>Телефон</th>
                                <th>Гости</th>
                                <th>Время</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayBookings as $booking): ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td><?= htmlspecialchars($booking['name']) ?></td>
                                    <td><?= htmlspecialchars($booking['phone']) ?></td>
                                    <td><?= $booking['guests'] ?></td>
                                    <td><?= $booking['booking_time'] ?></td>
                                    <td>
                                        <span class="badge <?= match($booking['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-info'
                                        } ?>">
                                            <?= match($booking['status']) {
                                                'pending' => 'Ожидание',
                                                'confirmed' => 'Подтверждено',
                                                'cancelled' => 'Отменено',
                                                default => $booking['status']
                                            } ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($todayBookings)): ?>
                                <tr><td colspan="6" style="text-align:center; color: var(--color-text-light);">Сегодня бронирований нет</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== KITCHEN (для поваров) ==================== -->
        <?php elseif ($page === 'kitchen' && ($isSousChef || $isCook)): ?>
            <div class="card">
                <h2>👨‍🍳 Активные заказы на кухне</h2>
                <p style="color: var(--color-text-light); margin-bottom: 20px;">
                    Заказы, которые нужно приготовить. Нажмите «Готовится», когда начали, и «Готов», когда блюдо готово.
                </p>

                <?php
                $stmt = $pdo->query("
                    SELECT o.*, u.phone, u.name as user_name
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    WHERE o.status IN ('confirmed', 'preparing')
                    ORDER BY o.created_at ASC
                ");
                $kitchenOrders = $stmt->fetchAll();

                if (empty($kitchenOrders)): ?>
                    <div style="text-align:center; padding:40px 20px; color: var(--color-text-light);">
                        <div style="font-size:3rem; margin-bottom:10px;">🍳</div>
                        <p>На кухне нет активных заказов</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($kitchenOrders as $order): 
                        // Получаем состав заказа
                        $itemsStmt = $pdo->prepare("
                            SELECT oi.quantity, d.name, oi.price
                            FROM order_items oi
                            JOIN dishes d ON oi.dish_id = d.id
                            WHERE oi.order_id = ?
                        ");
                        $itemsStmt->execute([$order['id']]);
                        $items = $itemsStmt->fetchAll();
                    ?>
                        <div class="kitchen-order-card">
                            <div class="order-info">
                                <div class="order-number">
                                    Заказ #<?= $order['id'] ?> 
                                    <span style="font-weight:400;font-size:0.85rem;color:var(--color-text-light);">
                                        — <?= htmlspecialchars($order['user_name'] ?? $order['phone']) ?>
                                    </span>
                                </div>
                                <div class="order-items">
                                    <?php foreach ($items as $item): ?>
                                        <div>• <?= $item['quantity'] ?>× <?= htmlspecialchars($item['name']) ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <div style="font-size:0.8rem;color:var(--color-text-light);margin-top:4px;">
                                    🕐 <?= date('H:i', strtotime($order['created_at'])) ?> 
                                    | 💰 <?= number_format($order['total_price'], 0, '', ' ') ?> ₽
                                </div>
                            </div>
                            <div class="order-actions">
                                <?php if ($order['status'] === 'confirmed'): ?>
                                    <form method="POST" action="../updateOrderStatus.php" style="display:inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="status" value="preparing">
                                        <button type="submit" class="status-btn cooking">👨‍🍳 Готовится</button>
                                    </form>
                                <?php elseif ($order['status'] === 'preparing'): ?>
                                    <span class="badge badge-info" style="font-size:0.85rem;">👨‍🍳 Готовится...</span>
                                    <form method="POST" action="../updateOrderStatus.php" style="display:inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="status" value="ready">
                                        <button type="submit" class="status-btn ready">🍽️ Готов</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- История приготовленных заказов -->
            <div class="card">
                <h2>✅ Приготовленные заказы (сегодня)</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Клиент</th>
                                <th>Состав</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Время</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT o.*, u.phone, u.name as user_name
                                FROM orders o
                                JOIN users u ON o.user_id = u.id
                                WHERE DATE(o.created_at) = CURDATE()
                                AND o.status IN ('ready', 'completed')
                                ORDER BY o.updated_at DESC
                            ");
                            while ($order = $stmt->fetch()):
                                $itemsStmt = $pdo->prepare("
                                    SELECT oi.quantity, d.name
                                    FROM order_items oi
                                    JOIN dishes d ON oi.dish_id = d.id
                                    WHERE oi.order_id = ?
                                ");
                                $itemsStmt->execute([$order['id']]);
                                $items = $itemsStmt->fetchAll();
                            ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_name'] ?? $order['phone']) ?></td>
                                    <td>
                                        <?php foreach ($items as $item): ?>
                                            <div><?= $item['quantity'] ?>× <?= htmlspecialchars($item['name']) ?></div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><strong><?= number_format($order['total_price'], 0, '', ' ') ?> ₽</strong></td>
                                    <td>
                                        <span class="badge <?= $order['status'] === 'ready' ? 'badge-info' : 'badge-success' ?>">
                                            <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($stmt->rowCount() === 0): ?>
                                <tr><td colspan="6" style="text-align:center; color: var(--color-text-light);">Сегодня ещё нет приготовленных заказов</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== ORDERS ==================== -->
        <?php elseif ($page === 'orders' && ($isWaiter || $isSousChef)): ?>
            <div class="card">
                <h2>Все заказы</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Клиент</th>
                                <th>Состав</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT o.*, u.phone, u.name as user_name
                                FROM orders o
                                JOIN users u ON o.user_id = u.id
                                WHERE o.status != 'cart'
                                ORDER BY o.created_at DESC
                            ");
                            while ($order = $stmt->fetch()):
                                // Получаем состав заказа
                                $itemsStmt = $pdo->prepare("
                                    SELECT oi.quantity, d.name, oi.price
                                    FROM order_items oi
                                    JOIN dishes d ON oi.dish_id = d.id
                                    WHERE oi.order_id = ?
                                ");
                                $itemsStmt->execute([$order['id']]);
                                $items = $itemsStmt->fetchAll();
                            ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_name'] ?? $order['phone']) ?></td>
                                    <td>
                                        <ul class="order-items-list">
                                            <?php foreach ($items as $item): ?>
                                                <li><?= $item['quantity'] ?>× <?= htmlspecialchars($item['name']) ?> — <?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?> ₽</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td><strong><?= number_format($order['total_price'], 0, '', ' ') ?> ₽</strong></td>
                                    <td>
                                        <span class="badge <?= match($order['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'preparing' => 'badge-info',
                                            'ready' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-warning'
                                        } ?>">
                                            <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" action="../updateOrderStatus.php" style="display:inline">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" style="padding:4px 8px;border:1px solid var(--color-border);border-radius:4px;font-size:0.8rem;">
                                                <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>🕐 Ожидает</option>
                                                <option value="confirmed" <?= $order['status']==='confirmed'?'selected':'' ?>>✅ Подтверждён</option>
                                                <option value="preparing" <?= $order['status']==='preparing'?'selected':'' ?>>👨‍🍳 Готовится</option>
                                                <option value="ready" <?= $order['status']==='ready'?'selected':'' ?>>🍽️ Готов</option>
                                                <option value="completed" <?= $order['status']==='completed'?'selected':'' ?>>✔️ Выполнен</option>
                                                <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>❌ Отменён</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm">💾</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== BOOKINGS ==================== -->
        <?php elseif ($page === 'bookings' && $isWaiter): ?>
            <div class="card">
                <h2>Все бронирования</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Имя</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Гости</th>
                                <th>Дата</th>
                                <th>Время</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM bookings ORDER BY booking_date DESC, booking_time ASC");
                            while ($booking = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td><?= htmlspecialchars($booking['name']) ?></td>
                                    <td><?= htmlspecialchars($booking['phone']) ?></td>
                                    <td><?= htmlspecialchars($booking['email'] ?? '—') ?></td>
                                    <td><?= $booking['guests'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= $booking['booking_time'] ?></td>
                                    <td>
                                        <span class="badge <?= match($booking['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-info'
                                        } ?>">
                                            <?= match($booking['status']) {
                                                'pending' => 'Ожидание',
                                                'confirmed' => 'Подтверждено',
                                                'cancelled' => 'Отменено',
                                                default => $booking['status']
                                            } ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../updateBookingStatus.php?id=<?= $booking['id'] ?>&status=confirmed" class="btn btn-sm">✅</a>
                                        <a href="../updateBookingStatus.php?id=<?= $booking['id'] ?>&status=cancelled" class="btn btn-sm btn-danger">❌</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== MENU & RECIPES (для су-шефа) ==================== -->
        <?php elseif ($page === 'menu-recipes' && $isSousChef): ?>
            <div class="card">
                <h2>📖 Меню и рецепты</h2>
                <p style="color: var(--color-text-light); margin-bottom: 20px;">
                    Полный список блюд с кратким описанием приготовления для су-шефа.
                </p>

                <?php
                $stmt = $pdo->query("
                    SELECT d.*, c.name as category_name
                    FROM dishes d
                    JOIN categories c ON d.category_id = c.id
                    ORDER BY c.name, d.name
                ");
                $currentCategory = '';
                while ($dish = $stmt->fetch()):
                    if ($currentCategory !== $dish['category_name']):
                        $currentCategory = $dish['category_name'];
                ?>
                    <h3 style="margin: 24px 0 12px; color: var(--color-primary); border-bottom: 2px solid var(--color-primary); padding-bottom: 6px;">
                        <?= htmlspecialchars($currentCategory) ?>
                    </h3>
                <?php endif; ?>
                    <div class="recipe-card">
                        <div style="display:flex; gap:16px; align-items:flex-start;">
                            <?php if ($dish['image']): ?>
                                <img src="../../frontend/<?= $dish['image'] ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                            <?php endif; ?>
                            <div style="flex:1;">
                                <h3><?= htmlspecialchars($dish['name']) ?></h3>
                                <div class="meta">
                                    <span>💰 <?= number_format($dish['price'], 0, '', ' ') ?> ₽</span>
                                    <?php if ($dish['weight']): ?>
                                        <span>⚖️ <?= $dish['weight'] ?> г</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($dish['description']): ?>
                                    <div class="description">
                                        <?= htmlspecialchars($dish['description']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="recipe-hint">
                                    <strong>💡 Краткий рецепт:</strong> 
                                    <?= htmlspecialchars($dish['description'] ?: 'Описание приготовления уточните у шеф-повара') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <!-- ==================== PROFILE ==================== -->
        <?php elseif ($page === 'profile'): ?>
            <div class="card">
                <h2>Мой профиль</h2>
                <div style="display:flex; gap:30px; align-items:flex-start; flex-wrap:wrap;">
                    <div style="text-align:center; flex-shrink:0;">
                        <?php if ($employee['avatar']): ?>
                            <img src="../../frontend/uploads/<?= $employee['avatar'] ?>" alt="" style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid var(--color-primary);">
                        <?php else: ?>
                            <div style="width:120px;height:120px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#ccc;border:3px solid var(--color-primary);"><?= $positionIcon ?></div>
                        <?php endif; ?>
                        <p style="margin-top:8px;font-size:0.85rem;color:var(--color-text-light);">
                            <?= htmlspecialchars($position ?: 'Должность не указана') ?>
                        </p>
                    </div>
                    <div style="flex:1;min-width:280px;">
                        <form method="POST" action="../updateEmployeeProfile.php" enctype="multipart/form-data">
                            <input type="hidden" name="user_id" value="<?= $employee['id'] ?>">
                            <div class="form-group">
                                <label>Имя</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($employee['name'] ?? '') ?>" placeholder="Ваше имя">
                            </div>
                            <div class="form-group">
                                <label>О себе</label>
                                <textarea name="bio" placeholder="Расскажите о себе" rows="4"><?= htmlspecialchars($employee['bio'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Фото профиля</label>
                                <input type="file" name="avatar" accept="image/*">
                            </div>
                            <button type="submit" class="btn">💾 Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
