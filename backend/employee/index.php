<?php
// =============================================
// ПАНЕЛЬ СОТРУДНИКА Bean Scene
// =============================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if (!isEmployee() && !isAdmin()) {
    die('Доступ запрещён');
}

$user = getCurrentUser();

// Статистика
$stats = [];

// Количество заказов сегодня
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['orders_today'] = $stmt->fetch()['count'];

// Количество бронирований сегодня
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(booking_date) = CURDATE()");
$stats['bookings_today'] = $stmt->fetch()['count'];

// Активные заказы (не completed и не cancelled)
$stmt = $pdo->query("
    SELECT COUNT(*) as count FROM orders 
    WHERE status NOT IN ('completed', 'cancelled')
");
$stats['active_orders'] = $stmt->fetch()['count'];

// Заказы на сегодня
$stmt = $pdo->query("
    SELECT o.*, u.phone, u.name as user_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE DATE(o.created_at) = CURDATE()
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель сотрудника — Bean Scene</title>
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
    </style>
</head>
<body>

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>☕ BEAN SCENE</h2>
            <p>Панель сотрудника</p>
            <span class="employee-badge">👤 <?= htmlspecialchars($user['phone']) ?></span>
        </div>
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">📊</span>
                <span>Дашборд</span>
            </a>
            <a href="?page=orders" class="<?= $page === 'orders' ? 'active' : '' ?>">
                <span class="icon">📦</span>
                <span>Заказы</span>
            </a>
            <a href="?page=bookings" class="<?= $page === 'bookings' ? 'active' : '' ?>">
                <span class="icon">📅</span>
                <span>Бронирования</span>
            </a>
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
                    'orders' => 'Заказы',
                    'bookings' => 'Бронирования',
                    'profile' => 'Мой профиль',
                ];
                echo $titles[$page] ?? 'Дашборд';
                ?>
            </h1>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($user['phone']) ?></span>
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
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Время</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_name'] ?? $order['phone']) ?></td>
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
                                    <td><?= date('H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($todayOrders)): ?>
                                <tr><td colspan="5" style="text-align:center; color: var(--color-text-light);">Сегодня заказов нет</td></tr>
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

        <!-- ==================== ORDERS ==================== -->
        <?php elseif ($page === 'orders'): ?>
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
        <?php elseif ($page === 'bookings'): ?>
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

        <!-- ==================== PROFILE ==================== -->
        <?php elseif ($page === 'profile'): ?>
            <div class="card">
                <h2>Мой профиль</h2>
                <div style="display:flex; gap:30px; align-items:flex-start; flex-wrap:wrap;">
                    <div style="text-align:center; flex-shrink:0;">
                        <?php if ($employee['avatar']): ?>
                            <img src="../../frontend/uploads/<?= $employee['avatar'] ?>" alt="" style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid var(--color-primary);">
                        <?php else: ?>
                            <div style="width:120px;height:120px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#ccc;border:3px solid var(--color-primary);">👤</div>
                        <?php endif; ?>
                        <p style="margin-top:8px;font-size:0.85rem;color:var(--color-text-light);">
                            <?= htmlspecialchars($employee['position'] ?? '') ?>
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
