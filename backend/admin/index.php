<?php
// =============================================
// АДМИН-ПАНЕЛЬ Bean Scene — Главная
// =============================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$user = getCurrentUser();

// Статистика
$stats = [];

// Количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch()['count'];

// Количество блюд
$stmt = $pdo->query("SELECT COUNT(*) as count FROM dishes");
$stats['dishes'] = $stmt->fetch()['count'];

// Количество заказов
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch()['count'];

// Количество бронирований
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
$stats['bookings'] = $stmt->fetch()['count'];

// Последние заказы
$stmt = $pdo->query("
    SELECT o.id, u.phone, o.total_price, o.status, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrders = $stmt->fetchAll();

// Последние бронирования
$stmt = $pdo->query("
    SELECT b.id, b.name, b.phone, b.guests, b.booking_date, b.booking_time, b.status
    FROM bookings b
    ORDER BY b.created_at DESC
    LIMIT 10
");
$recentBookings = $stmt->fetchAll();

// Активные страницы
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель — Bean Scene</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>☕ BEAN SCENE</h2>
            <p>Админ-панель</p>
        </div>
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">📊</span>
                <span>Дашборд</span>
            </a>
            <a href="?page=menu" class="<?= $page === 'menu' ? 'active' : '' ?>">
                <span class="icon">🍽</span>
                <span>Меню</span>
            </a>
            <a href="?page=add-dish" class="<?= $page === 'add-dish' ? 'active' : '' ?>">
                <span class="icon">➕</span>
                <span>Добавить блюдо</span>
            </a>
            <a href="?page=orders" class="<?= $page === 'orders' ? 'active' : '' ?>">
                <span class="icon">📦</span>
                <span>Заказы</span>
            </a>
            <a href="?page=bookings" class="<?= $page === 'bookings' ? 'active' : '' ?>">
                <span class="icon">📅</span>
                <span>Бронирования</span>
            </a>
            <a href="?page=users" class="<?= $page === 'users' ? 'active' : '' ?>">
                <span class="icon">👥</span>
                <span>Пользователи</span>
            </a>
            <a href="?page=promotions" class="<?= $page === 'promotions' ? 'active' : '' ?>">
                <span class="icon">🏷</span>
                <span>Акции</span>
            </a>
            <a href="?page=reviews" class="<?= $page === 'reviews' ? 'active' : '' ?>">
                <span class="icon">⭐</span>
                <span>Отзывы</span>
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
                    'menu' => 'Управление меню',
                    'add-dish' => 'Добавить блюдо',
                    'orders' => 'Заказы',
                    'bookings' => 'Бронирования',
                    'users' => 'Пользователи',
                    'promotions' => 'Акции',
                    'reviews' => 'Отзывы',
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
                    <div class="stat-value"><?= $stats['users'] ?></div>
                    <div class="stat-label">👥 Пользователей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['dishes'] ?></div>
                    <div class="stat-label">🍽 Блюд в меню</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['orders'] ?></div>
                    <div class="stat-label">📦 Заказов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['bookings'] ?></div>
                    <div class="stat-label">📅 Бронирований</div>
                </div>
            </div>

            <div class="card">
                <h2>Последние заказы</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Клиент</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['phone']) ?></td>
                                    <td><?= number_format($order['total'], 0, '', ' ') ?> ₽</td>
                                    <td>
                                        <?php
                                        $badgeClass = match($order['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'preparing' => 'badge-info',
                                            'ready' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-warning'
                                        };
                                        $statusLabels = [
                                            'pending' => '🕐 Ожидает',
                                            'confirmed' => '✅ Подтверждён',
                                            'preparing' => '👨‍🍳 Готовится',
                                            'ready' => '🍽️ Готов',
                                            'completed' => '✔️ Выполнен',
                                            'cancelled' => '❌ Отменён'
                                        ];
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentOrders)): ?>
                                <tr><td colspan="5" style="text-align:center; color: var(--color-text-light);">Нет заказов</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>Последние бронирования</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Имя</th>
                                <th>Телефон</th>
                                <th>Гости</th>
                                <th>Дата</th>
                                <th>Время</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td><?= htmlspecialchars($booking['name']) ?></td>
                                    <td><?= htmlspecialchars($booking['phone']) ?></td>
                                    <td><?= $booking['guests'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= $booking['booking_time'] ?></td>
                                    <td>
                                        <?php
                                        $bBadge = match($booking['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-info'
                                        };
                                        $bLabels = [
                                            'pending' => 'Ожидание',
                                            'confirmed' => 'Подтверждено',
                                            'cancelled' => 'Отменено'
                                        ];
                                        ?>
                                        <span class="badge <?= $bBadge ?>">
                                            <?= $bLabels[$booking['status']] ?? $booking['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentBookings)): ?>
                                <tr><td colspan="7" style="text-align:center; color: var(--color-text-light);">Нет бронирований</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== MENU ==================== -->
        <?php elseif ($page === 'menu'): ?>
            <div class="card">
                <h2>Все блюда</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Фото</th>
                                <th>Название</th>
                                <th>Категория</th>
                                <th>Цена</th>
                                <th>Вес</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT d.*, c.name as category_name
                                FROM dishes d
                                JOIN categories c ON d.category_id = c.id
                                ORDER BY d.id DESC
                            ");
                            while ($dish = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td><?= $dish['id'] ?></td>
                                    <td>
                                        <?php if ($dish['image']): ?>
                                            <img src="../uploads/<?= $dish['image'] ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                        <?php else: ?>
                                            <span style="color:var(--color-text-light)">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($dish['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($dish['category_name']) ?></td>
                                    <td><?= number_format($dish['price'], 0, '', ' ') ?> ₽</td>
                                    <td><?= $dish['weight'] ? $dish['weight'] . ' г' : '—' ?></td>
                                    <td>
                                        <a href="?page=edit-dish&id=<?= $dish['id'] ?>" class="btn btn-sm">✏️</a>
                                        <a href="../deleteProduct.php?id=<?= $dish['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить блюдо?')">🗑</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== ADD DISH ==================== -->
        <?php elseif ($page === 'add-dish'): ?>
            <div class="card">
                <h2>Добавить новое блюдо</h2>
                <form method="POST" action="../createProduct.php" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Название блюда</label>
                            <input type="text" name="name" placeholder="Например: Салат Цезарь" required>
                        </div>
                        <div class="form-group">
                            <label>Категория</label>
                            <select name="category_id" required>
                                <option value="">Выберите категорию</option>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                                while ($cat = $stmt->fetch()) {
                                    echo "<option value=\"{$cat['id']}\">{$cat['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description" placeholder="Описание блюда, состав"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Цена (₽)</label>
                            <input type="number" name="price" placeholder="590" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Вес (грамм)</label>
                            <input type="number" name="weight" placeholder="250">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Фото блюда</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn">➕ Добавить блюдо</button>
                </form>
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
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT o.*, u.phone
                                FROM orders o
                                JOIN users u ON o.user_id = u.id
                                ORDER BY o.created_at DESC
                            ");
                            while ($order = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['phone']) ?></td>
                                    <td><strong><?= number_format($order['total'], 0, '', ' ') ?> ₽</strong></td>
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
                                        <form method="POST" action="orders.php" style="display:inline">
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
                            $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
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
                                            <?= $bLabels[$booking['status']] ?? $booking['status'] ?>
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

        <!-- ==================== USERS ==================== -->
        <?php elseif ($page === 'users'): ?>
            <div class="card">
                <h2>Пользователи</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Телефон</th>
                                <th>Права доступа</th>
                                <th>Дата регистрации</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT u.*, ar.name as access_name
                                FROM users u
                                JOIN access_rights ar ON u.access_rights_id = ar.id
                                ORDER BY u.id DESC
                            ");
                            while ($userRow = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td><?= $userRow['id'] ?></td>
                                    <td><?= htmlspecialchars($userRow['phone']) ?></td>
                                    <td>
                                        <span class="badge <?= $userRow['access_name'] === 'ADMIN' ? 'badge-danger' : 'badge-info' ?>">
                                            <?= $userRow['access_name'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($userRow['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== PROMOTIONS ==================== -->
        <?php elseif ($page === 'promotions'): ?>
            <div class="card">
                <h2>Управление акциями</h2>
                <form method="POST" action="../createPromotion.php" style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid var(--color-border);">
                    <div class="form-group">
                        <label>Название акции</label>
                        <input type="text" name="title" placeholder="Например: Скидка 20%" required>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description" placeholder="Описание акции"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Дата начала</label>
                            <input type="date" name="start_date">
                        </div>
                        <div class="form-group">
                            <label>Дата окончания</label>
                            <input type="date" name="end_date">
                        </div>
                    </div>
                    <button type="submit" class="btn">➕ Добавить акцию</button>
                </form>

                <h3 style="margin-bottom: 15px;">Текущие акции</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Период</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
                            while ($promo = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td><?= $promo['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($promo['title']) ?></strong></td>
                                    <td>
                                        <?= $promo['start_date'] ? date('d.m.Y', strtotime($promo['start_date'])) : '—' ?>
                                        —
                                        <?= $promo['end_date'] ? date('d.m.Y', strtotime($promo['end_date'])) : '—' ?>
                                    </td>
                                    <td>
                                        <a href="../deletePromotion.php?id=<?= $promo['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить акцию?')">🗑</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== REVIEWS ==================== -->
        <?php elseif ($page === 'reviews'): ?>
            <div class="card">
                <h2>Отзывы гостей</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Отзыв</th>
                                <th>Рейтинг</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
                            while ($review = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td><?= $review['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($review['name']) ?></strong></td>
                                    <td style="max-width: 300px;"><?= htmlspecialchars($review['text']) ?></td>
                                    <td>
                                        <span style="color: var(--color-primary);">
                                            <?= str_repeat('⭐', (int)$review['rating']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($review['created_at'])) ?></td>
                                    <td>
                                        <a href="../deleteReview.php?id=<?= $review['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить отзыв?')">🗑</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
