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

// Получаем полные данные админа (шеф-повара)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$adminProfile = $stmt->fetch();

// Статистика
$stats = [];

// Количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch()['count'];

// Количество блюд
$stmt = $pdo->query("SELECT COUNT(*) as count FROM dishes");
$stats['dishes'] = $stmt->fetch()['count'];

// Количество заказов (исключая корзины)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status != 'cart'");
$stats['orders'] = $stmt->fetch()['count'];

// Количество бронирований
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
$stats['bookings'] = $stmt->fetch()['count'];

// Последние заказы (с деталями и обратной связью)
$stmt = $pdo->query("
    SELECT o.id, u.phone, u.name as user_name, o.total_price, o.status, o.type, o.payment_status, o.created_at,
           GROUP_CONCAT(CONCAT(d.name, ' (', oi.count, ' шт.)') SEPARATOR ', ') as items,
           ofb.rating as feedback_rating, ofb.comment as feedback_comment
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN dishes d ON oi.dish_id = d.id
    LEFT JOIN order_feedback ofb ON ofb.order_id = o.id
    WHERE o.status != 'cart'
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 15
");
$recentOrders = $stmt->fetchAll();

// Последние бронирования
$stmt = $pdo->query("
    SELECT b.id, b.name, b.phone, b.email, b.guests, b.booking_date, b.booking_time, b.comment, b.status, b.created_at,
           bf.rating as feedback_rating, bf.comment as feedback_comment,
           u.phone as user_phone, u.name as user_name
    FROM bookings b
    LEFT JOIN booking_feedback bf ON bf.booking_id = b.id
    LEFT JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 15
");
$recentBookings = $stmt->fetchAll();

// Объединённая лента активности
$activityFeed = [];

foreach ($recentOrders as $order) {
    $feedbackStr = '';
    if ($order['feedback_rating']) {
        $feedbackStr = ($order['feedback_rating'] === 'like' ? '👍' : '👎') . ($order['feedback_comment'] ? ' — ' . htmlspecialchars($order['feedback_comment']) : '');
    }

    $clientName = $order['user_name'] ?: ($order['phone'] === $order['user_name'] ? $order['phone'] : $order['user_name']);
    $activityFeed[] = [
        'type' => 'order',
        'id' => $order['id'],
        'title' => '📦 Заказ #' . $order['id'],
        'client' => $clientName ?: $order['phone'],
        'phone' => $order['phone'],
        'details' => $order['items'] ?: '—',
        'amount' => $order['total_price'],
        'status' => $order['status'],
        'status_label' => $statusLabels[$order['status']] ?? $order['status'],
        'sub_info' => ($order['type'] === 'delivery' ? '🏠 Доставка' : ($order['type'] === 'pickup' ? '🚶 Самовывоз' : '🍽️ В зале')) . ' · ' . ($order['payment_status'] === 'paid' ? '✅ Оплачено' : '⏳ Ожидает оплаты'),
        'feedback' => $feedbackStr,
        'time' => strtotime($order['created_at']),
        'created_at' => $order['created_at']
    ];
}

foreach ($recentBookings as $booking) {
    $feedbackStr = '';
    if ($booking['feedback_rating']) {
        $feedbackStr = ($booking['feedback_rating'] === 'like' ? '👍' : '👎') . ($booking['feedback_comment'] ? ' — ' . htmlspecialchars($booking['feedback_comment']) : '');
    }

    $activityFeed[] = [
        'type' => 'booking',
        'id' => $booking['id'],
        'title' => '📅 Бронь #' . $booking['id'],
        'client' => $booking['name'],
        'phone' => $booking['phone'],
        'details' => $booking['guests'] . ' гостей',
        'amount' => null,
        'status' => $booking['status'],
        'status_label' => $bLabels[$booking['status']] ?? $booking['status'],
        'sub_info' => date('d.m.Y', strtotime($booking['booking_date'])) . ' в ' . $booking['booking_time'],
        'feedback' => $feedbackStr,
        'time' => strtotime($booking['created_at']),
        'created_at' => $booking['created_at']
    ];
}

// Статусы заказов
$statusLabels = [
    'pending' => '🕐 Ожидает',
    'confirmed' => '✅ Подтверждён',
    'preparing' => '👨‍🍳 Готовится',
    'ready' => '🍽️ Готов',
    'completed' => '✔️ Выполнен',
    'cancelled' => '❌ Отменён'
];

// Статусы бронирований
$bLabels = [
    'pending' => 'Ожидание',
    'confirmed' => 'Подтверждено',
    'cancelled' => 'Отменено'
];

// Сортируем по времени создания (сначала новые)
usort($activityFeed, function($a, $b) {
    return $b['time'] - $a['time'];
});

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
    <script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('collapsed');
        localStorage.setItem('admin_sidebar', document.querySelector('.sidebar').classList.contains('collapsed') ? 'collapsed' : 'expanded');
    }
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('admin_sidebar') === 'collapsed') {
            document.querySelector('.sidebar').classList.add('collapsed');
        }
    });
    </script>

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()" title="Свернуть/развернуть">☰</button>
            <div class="sidebar-header-text">
                <h2>☕ BEAN SCENE</h2>
                <p>Админ-панель</p>
            </div>
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
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main-content">

        <div class="page-header">
            <button class="mobile-menu-btn" onclick="openMobileSidebar()" style="display:none;background:none;border:none;font-size:1.5rem;cursor:pointer;padding:5px;margin-right:10px;">☰</button>
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
                    'edit-profile' => 'Редактировать профиль',
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
            <!-- Профиль шеф-повара -->
            <div class="card" style="margin-bottom:20px;">
                <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                    <div style="flex-shrink:0; text-align:center;">
                        <?php if ($adminProfile['avatar']): ?>
                            <img src="../../frontend/uploads/<?= $adminProfile['avatar'] ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:3px solid var(--color-primary);">
                        <?php else: ?>
                            <div style="width:80px;height:80px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#ccc;border:3px solid var(--color-primary);">👨‍🍳</div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <h2 style="margin:0 0 5px;"><?= htmlspecialchars($adminProfile['name'] ?? 'Шеф-повар') ?></h2>
                        <p style="margin:0;color:var(--color-primary);font-weight:600;">
                            <?= htmlspecialchars($adminProfile['position'] ?? 'Шеф-повар') ?> 
                            <span style="color:var(--color-text-light);font-weight:400;">— Владелец ресторана</span>
                        </p>
                        <?php if ($adminProfile['bio']): ?>
                            <p style="margin:8px 0 0;color:var(--color-text-light);font-size:0.9rem;"><?= htmlspecialchars($adminProfile['bio']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="?page=edit-profile" class="btn">✏️ Редактировать профиль</a>
                    </div>
                </div>
            </div>

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

            <!-- ===== ЕДИНАЯ ЛЕНТА АКТИВНОСТИ ===== -->
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                    <h2 style="margin:0;">📋 Лента активности</h2>
                    <div style="display:flex; gap:8px; font-size:0.85rem; color:var(--color-text-light);">
                        <span>📦 Заказы</span>
                        <span>·</span>
                        <span>📅 Бронирования</span>
                    </div>
                </div>
                <div class="activity-feed">
                    <?php if (empty($activityFeed)): ?>
                        <div style="text-align:center; padding:40px; color:var(--color-text-light);">
                            <p style="font-size:2rem; margin-bottom:10px;">📭</p>
                            <p>Пока нет активности</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activityFeed as $item): ?>
                            <div class="activity-item activity-<?= $item['type'] ?>">
                                <div class="activity-icon">
                                    <?= $item['type'] === 'order' ? '📦' : '📅' ?>
                                </div>
                                <div class="activity-body">
                                    <div class="activity-header">
                                        <span class="activity-title"><?= $item['title'] ?></span>
                                        <span class="activity-time"><?= date('d.m.Y H:i', $item['time']) ?></span>
                                    </div>
                                    <div class="activity-client">
                                        👤 <?= htmlspecialchars($item['client']) ?>
                                        <span style="color:var(--color-text-light); font-size:0.8rem;"><?= htmlspecialchars($item['phone']) ?></span>
                                    </div>
                                    <div class="activity-details">
                                        <?php if ($item['type'] === 'order'): ?>
                                            <span class="activity-items"><?= htmlspecialchars($item['details']) ?></span>
                                            <span class="activity-amount"><?= number_format($item['amount'], 0, '', ' ') ?> ₽</span>
                                        <?php else: ?>
                                            <span class="activity-items">👥 <?= $item['details'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-footer">
                                        <span class="badge <?= match($item['status']) {
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-success',
                                            'preparing' => 'badge-info',
                                            'ready' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-danger',
                                            default => 'badge-warning'
                                        } ?>">
                                            <?= $item['status_label'] ?>
                                        </span>
                                        <span class="activity-subinfo"><?= $item['sub_info'] ?></span>
                                        <?php if (!empty($item['feedback'])): ?>
                                            <span class="activity-feedback"><?= $item['feedback'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <style>
            .activity-feed {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .activity-item {
                display: flex;
                gap: 14px;
                padding: 14px 18px;
                border-radius: 10px;
                background: var(--color-surface);
                border: 1px solid var(--color-border);
                transition: all 0.2s;
            }
            .activity-item:hover {
                border-color: var(--color-primary);
                box-shadow: 0 2px 12px rgba(212, 168, 83, 0.1);
            }
            .activity-order {
                border-left: 3px solid #3498db;
            }
            .activity-booking {
                border-left: 3px solid #d4a853;
            }
            .activity-icon {
                font-size: 1.5rem;
                flex-shrink: 0;
                padding-top: 2px;
            }
            .activity-body {
                flex: 1;
                min-width: 0;
            }
            .activity-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                margin-bottom: 4px;
            }
            .activity-title {
                font-weight: 700;
                font-size: 0.95rem;
                color: var(--color-text);
            }
            .activity-time {
                font-size: 0.75rem;
                color: var(--color-text-light);
                white-space: nowrap;
            }
            .activity-client {
                font-size: 0.85rem;
                color: var(--color-text);
                margin-bottom: 4px;
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .activity-details {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                margin-bottom: 6px;
            }
            .activity-items {
                font-size: 0.8rem;
                color: var(--color-text-light);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 400px;
            }
            .activity-amount {
                font-weight: 700;
                color: var(--color-primary);
                font-size: 0.9rem;
                white-space: nowrap;
            }
            .activity-footer {
                display: flex;
                gap: 10px;
                align-items: center;
                flex-wrap: wrap;
            }
            .activity-subinfo {
                font-size: 0.78rem;
                color: var(--color-text-light);
            }
            .activity-feedback {
                font-size: 0.8rem;
                color: var(--color-primary);
                font-weight: 500;
                padding: 2px 8px;
                background: rgba(212, 168, 83, 0.1);
                border-radius: 4px;
            }
            </style>

        <!-- ==================== MENU ==================== -->
        <?php elseif ($page === 'menu'): ?>
            <?php
            // Получаем все категории
            $catStmt = $pdo->query("SELECT * FROM categories ORDER BY id");
            $categories = $catStmt->fetchAll();

            // Активная категория из GET
            $activeCat = $_GET['cat'] ?? 'all';
            ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                    <h2 style="margin:0;">Меню ресторана</h2>
                    <a href="?page=add-dish" class="btn">➕ Добавить блюдо</a>
                </div>

                <!-- Вкладки категорий -->
                <div class="menu-categories" style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid var(--color-border);">
                    <a href="?page=menu&cat=all" class="menu-cat-btn <?= $activeCat === 'all' ? 'active' : '' ?>" style="padding:8px 18px; border-radius:20px; text-decoration:none; font-size:0.85rem; font-weight:600; transition:all 0.2s; <?= $activeCat === 'all' ? 'background:var(--color-primary); color:#fff;' : 'background:var(--color-surface); color:var(--color-text-light);' ?>">
                        🍽 Все
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?page=menu&cat=<?= $cat['id'] ?>" class="menu-cat-btn <?= $activeCat == $cat['id'] ? 'active' : '' ?>" style="padding:8px 18px; border-radius:20px; text-decoration:none; font-size:0.85rem; font-weight:600; transition:all 0.2s; <?= $activeCat == $cat['id'] ? 'background:var(--color-primary); color:#fff;' : 'background:var(--color-surface); color:var(--color-text-light);' ?>">
                            <?= match($cat['name']) {
                                'Салаты' => '🥗',
                                'Супы' => '🍜',
                                'Горячие блюда' => '🔥',
                                'Десерты' => '🍰',
                                'Напитки' => '🥤',
                                default => '🍽'
                            } ?> <?= $cat['name'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Таблица блюд -->
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
                                <th>Фирменное</th>
                                <th>Популярное</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($activeCat === 'all') {
                                $stmt = $pdo->query("
                                    SELECT d.*, c.name as category_name
                                    FROM dishes d
                                    JOIN categories c ON d.category_id = c.id
                                    ORDER BY d.name
                                ");
                            } else {
                                $stmt = $pdo->prepare("
                                    SELECT d.*, c.name as category_name
                                    FROM dishes d
                                    JOIN categories c ON d.category_id = c.id
                                    WHERE d.category_id = ?
                                    ORDER BY d.name
                                ");
                                $stmt->execute([$activeCat]);
                            }
                            while ($dish = $stmt->fetch()):
                            ?>
                                <tr>
                                    <td><?= $dish['id'] ?></td>
                                    <td>
                                        <?php if ($dish['image']): ?>
                                            <img src="../../frontend/<?= $dish['image'] ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;cursor:pointer;transition:transform 0.2s;" onclick="event.stopPropagation();openImageModal('../../frontend/<?= $dish['image'] ?>', '<?= htmlspecialchars($dish['name'], ENT_QUOTES) ?>')" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                        <?php else: ?>
                                            <span style="color:var(--color-text-light)">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($dish['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($dish['category_name']) ?></td>
                                    <td><?= number_format($dish['price'], 0, '', ' ') ?> ₽</td>
                                    <td><?= $dish['weight'] ? $dish['weight'] . ' г' : '—' ?></td>
                                    <td style="text-align:center;">
                                        <a href="../toggleSpecial.php?id=<?= $dish['id'] ?>" style="text-decoration:none;font-size:1.3rem;" title="<?= $dish['is_special'] ? 'Убрать из фирменных' : 'Сделать фирменным' ?>">
                                            <?= $dish['is_special'] ? '👨‍🍳' : '○' ?>
                                        </a>
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="../togglePopular.php?id=<?= $dish['id'] ?>" style="text-decoration:none;font-size:1.3rem;" title="<?= $dish['is_popular'] ? 'Убрать из популярных' : 'Сделать популярным' ?>">
                                            <?= $dish['is_popular'] ? '⭐' : '☆' ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="?page=edit-dish&id=<?= $dish['id'] ?>" class="btn btn-sm">✏️</a>
                                        <a href="../deleteProduct.php?id=<?= $dish['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить блюдо?')">🗑</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($stmt->rowCount() === 0): ?>
                                <tr><td colspan="7" style="text-align:center; color:var(--color-text-light); padding:30px;">😕 В этой категории пока нет блюд</td></tr>
                            <?php endif; ?>
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
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_special" value="1" style="width:18px;height:18px;cursor:pointer;">
                            <span>👨‍🍳 Отметить как фирменное блюдо (шеф-рекомендует)</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_popular" value="1" style="width:18px;height:18px;cursor:pointer;">
                            <span>⭐ Отметить как популярное блюдо</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Фото блюда</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn">➕ Добавить блюдо</button>
                </form>
            </div>

        <!-- ==================== EDIT DISH ==================== -->
        <?php elseif ($page === 'edit-dish'): ?>
            <?php
            $dishId = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT d.*, c.name as category_name
                FROM dishes d
                JOIN categories c ON d.category_id = c.id
                WHERE d.id = ?
            ");
            $stmt->execute([$dishId]);
            $dish = $stmt->fetch();

            if (!$dish):
            ?>
                <div class="card">
                    <div style="text-align:center; padding:40px;">
                        <p style="font-size:3rem; margin-bottom:15px;">😕</p>
                        <h2>Блюдо не найдено</h2>
                        <a href="?page=menu" class="btn">← Вернуться в меню</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>✏️ Редактировать блюдо</h2>
                    <form method="POST" action="../updateProduct.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $dish['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= $dish['image'] ?>">

                        <div style="display:flex; gap:30px; align-items:flex-start; flex-wrap:wrap; margin-bottom:20px;">
                            <div style="flex-shrink:0; text-align:center;">
                                <?php if ($dish['image']): ?>
                                    <img src="../../frontend/<?= $dish['image'] ?>" alt="" style="width:150px;height:150px;object-fit:cover;border-radius:12px;border:2px solid var(--color-border);">
                                <?php else: ?>
                                    <div style="width:150px;height:150px;border-radius:12px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#ccc;border:2px solid var(--color-border);">🍽</div>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1; min-width:280px;">
                                <div class="form-group">
                                    <label>Название блюда</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($dish['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Описание</label>
                                    <textarea name="description" rows="3"><?= htmlspecialchars($dish['description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>🧂 Ингредиенты / Состав</label>
                                    <textarea name="ingredients" rows="2" placeholder="Например: Куриное филе, салат айсберг, пармезан, гренки, соус Цезарь"><?= htmlspecialchars($dish['ingredients'] ?? '') ?></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Цена (₽)</label>
                                        <input type="number" name="price" value="<?= $dish['price'] ?>" step="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Вес (грамм)</label>
                                        <input type="number" name="weight" value="<?= $dish['weight'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Категория</label>
                                    <select name="category_id" required>
                                        <option value="">Выберите категорию</option>
                                        <?php
                                        $catStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                                        while ($cat = $catStmt->fetch()):
                                            $selected = $cat['id'] == $dish['category_id'] ? 'selected' : '';
                                        ?>
                                            <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= $cat['name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding-top:24px;">
                                        <input type="checkbox" name="is_special" value="1" <?= $dish['is_special'] ? 'checked' : '' ?> style="width:18px;height:18px;cursor:pointer;">
                                        <span>👨‍🍳 Отметить как фирменное блюдо (шеф-рекомендует)</span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding-top:24px;">
                                        <input type="checkbox" name="is_popular" value="1" <?= $dish['is_popular'] ? 'checked' : '' ?> style="width:18px;height:18px;cursor:pointer;">
                                        <span>⭐ Отметить как популярное блюдо</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Новое фото (оставьте пустым, чтобы оставить текущее)</label>
                            <input type="file" name="image" accept="image/*">
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn">💾 Сохранить изменения</button>
                            <a href="?page=menu" class="btn" style="background:var(--color-surface); color:var(--color-text-light);">← Отмена</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        <!-- ==================== ORDERS ==================== -->
        <?php elseif ($page === 'orders'): ?>
            <?php
            $orderStatusFilter = $_GET['order_status'] ?? 'all';
            ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                    <h2 style="margin:0;">Все заказы</h2>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="?page=orders&order_status=all" class="btn btn-sm <?= $orderStatusFilter === 'all' ? '' : '' ?>" style="<?= $orderStatusFilter === 'all' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">Все</a>
                        <a href="?page=orders&order_status=pending" class="btn btn-sm" style="<?= $orderStatusFilter === 'pending' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">🕐 Ожидает</a>
                        <a href="?page=orders&order_status=confirmed" class="btn btn-sm" style="<?= $orderStatusFilter === 'confirmed' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">✅ Подтверждён</a>
                        <a href="?page=orders&order_status=preparing" class="btn btn-sm" style="<?= $orderStatusFilter === 'preparing' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">👨‍🍳 Готовится</a>
                        <a href="?page=orders&order_status=ready" class="btn btn-sm" style="<?= $orderStatusFilter === 'ready' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">🍽️ Готов</a>
                        <a href="?page=orders&order_status=completed" class="btn btn-sm" style="<?= $orderStatusFilter === 'completed' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">✔️ Выполнен</a>
                        <a href="?page=orders&order_status=cancelled" class="btn btn-sm" style="<?= $orderStatusFilter === 'cancelled' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">❌ Отменён</a>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn btn-sm" style="background:#e74c3c; color:#fff;" onclick="showConfirmModal('orders-completed')">🗑 Очистить завершённые</button>
                        <button class="btn btn-sm" style="background:#e67e22; color:#fff;" onclick="showConfirmModal('orders-uncompleted')">🗑 Очистить незавершённые</button>
                        <button class="btn btn-sm btn-danger" onclick="showConfirmModal('orders-all')">🗑 Очистить всё</button>
                    </div>
                </div>
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
                                <th>Обратная связь</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orderSql = "
                                SELECT o.*, u.phone, u.name as user_name, ofb.rating as feedback_rating, ofb.comment as feedback_comment
                                FROM orders o
                                JOIN users u ON o.user_id = u.id
                                LEFT JOIN order_feedback ofb ON ofb.order_id = o.id
                                WHERE o.status != 'cart'
                            ";
                            if ($orderStatusFilter !== 'all') {
                                $orderSql .= " AND o.status = " . $pdo->quote($orderStatusFilter);
                            }
                            $orderSql .= " ORDER BY o.created_at DESC";
                            $stmt = $pdo->query($orderSql);
                            while ($order = $stmt->fetch()):
                                $orderClient = $order['user_name'] ? $order['user_name'] . ' (' . $order['phone'] . ')' : $order['phone'];
                            ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($orderClient) ?></td>
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
                                    <td>
                                        <?php if ($order['feedback_rating']): ?>
                                            <span style="font-size:1.1rem;"><?= $order['feedback_rating'] === 'like' ? '👍' : '👎' ?></span>
                                            <?php if ($order['feedback_comment']): ?>
                                                <span style="font-size:0.75rem;color:var(--color-text-light);display:block;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($order['feedback_comment']) ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:var(--color-text-light);font-size:0.8rem;">—</span>
                                        <?php endif; ?>
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
            <?php
            $bookingStatusFilter = $_GET['booking_status'] ?? 'all';
            ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                    <h2 style="margin:0;">Все бронирования</h2>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="?page=bookings&booking_status=all" class="btn btn-sm" style="<?= $bookingStatusFilter === 'all' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">Все</a>
                        <a href="?page=bookings&booking_status=pending" class="btn btn-sm" style="<?= $bookingStatusFilter === 'pending' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">🕐 Ожидание</a>
                        <a href="?page=bookings&booking_status=confirmed" class="btn btn-sm" style="<?= $bookingStatusFilter === 'confirmed' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">✅ Подтверждено</a>
                        <a href="?page=bookings&booking_status=cancelled" class="btn btn-sm" style="<?= $bookingStatusFilter === 'cancelled' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-light);' ?>">❌ Отменено</a>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn btn-sm" style="background:#e74c3c; color:#fff;" onclick="showConfirmModal('cancelled')">🗑 Очистить отменённые</button>
                        <button class="btn btn-sm btn-danger" onclick="showConfirmModal('all')">🗑 Очистить всё</button>
                    </div>
                </div>
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
                                <th>Пожелания</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $bookingSql = "SELECT * FROM bookings";
                            if ($bookingStatusFilter !== 'all') {
                                $bookingSql .= " WHERE status = " . $pdo->quote($bookingStatusFilter);
                            }
                            $bookingSql .= " ORDER BY created_at DESC";
                            $stmt = $pdo->query($bookingSql);
                            while ($booking = $stmt->fetch()):
                            ?>
                                <tr class="booking-row" style="cursor:pointer;" onclick="openBookingModal(
                                    <?= $booking['id'] ?>,
                                    '<?= htmlspecialchars($booking['name'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($booking['phone'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($booking['email'] ?? '', ENT_QUOTES) ?>',
                                    <?= $booking['guests'] ?>,
                                    '<?= date('d.m.Y', strtotime($booking['booking_date'])) ?>',
                                    '<?= $booking['booking_time'] ?>',
                                    '<?= htmlspecialchars($booking['comment'] ?? '', ENT_QUOTES) ?>',
                                    '<?= $bLabels[$booking['status']] ?? $booking['status'] ?>',
                                    '<?= date('d.m.Y H:i', strtotime($booking['created_at'])) ?>'
                                )">
                                    <td>#<?= $booking['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($booking['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($booking['phone']) ?></td>
                                    <td><?= htmlspecialchars($booking['email'] ?? '—') ?></td>
                                    <td><?= $booking['guests'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= $booking['booking_time'] ?></td>
                                    <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        <?php if ($booking['comment']): ?>
                                            <span style="color:var(--color-text-light);">💬 <?= htmlspecialchars(mb_substr($booking['comment'], 0, 30)) ?><?= mb_strlen($booking['comment']) > 30 ? '...' : '' ?></span>
                                        <?php else: ?>
                                            <span style="color:#999;">—</span>
                                        <?php endif; ?>
                                    </td>
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
                                    <td onclick="event.stopPropagation();">
                                        <a href="../updateBookingStatus.php?id=<?= $booking['id'] ?>&status=confirmed" class="btn btn-sm">✅</a>
                                        <a href="../updateBookingStatus.php?id=<?= $booking['id'] ?>&status=cancelled" class="btn btn-sm btn-danger">❌</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Модальное окно бронирования -->
            <div id="booking-detail-modal" class="booking-detail-overlay" onclick="if(event.target===this)closeBookingDetailModal()">
                <div class="booking-detail-content">
                    <button class="booking-detail-close" onclick="closeBookingDetailModal()">&times;</button>
                    <div class="booking-detail-body">
                        <div class="booking-detail-icon">📅</div>
                        <h2 id="booking-detail-name"></h2>
                        <div class="booking-detail-info">
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">📞 Телефон</span>
                                <span class="booking-detail-value" id="booking-detail-phone"></span>
                            </div>
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">✉️ Email</span>
                                <span class="booking-detail-value" id="booking-detail-email"></span>
                            </div>
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">👥 Гости</span>
                                <span class="booking-detail-value" id="booking-detail-guests"></span>
                            </div>
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">📅 Дата</span>
                                <span class="booking-detail-value" id="booking-detail-date"></span>
                            </div>
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">⏰ Время</span>
                                <span class="booking-detail-value" id="booking-detail-time"></span>
                            </div>
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">📋 Статус</span>
                                <span class="booking-detail-value" id="booking-detail-status"></span>
                            </div>
                            <div class="booking-detail-row">
                                <span class="booking-detail-label">🕐 Создано</span>
                                <span class="booking-detail-value" id="booking-detail-created"></span>
                            </div>
                        </div>
                        <div class="booking-detail-comment" id="booking-detail-comment">
                            <strong>💬 Пожелания:</strong>
                            <p id="booking-detail-comment-text"></p>
                        </div>
                    </div>
                </div>
            </div>

            <style>
            .booking-detail-overlay {
                position: fixed; inset: 0; z-index: 99999;
                background: rgba(0,0,0,0.6);
                display: flex; align-items: center; justify-content: center;
                visibility: hidden; opacity: 0;
                transition: all 0.3s;
            }
            .booking-detail-overlay.active {
                visibility: visible; opacity: 1;
            }
            .booking-detail-content {
                background: #fff; border-radius: 16px;
                max-width: 480px; width: 90%; padding: 40px 35px;
                position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                transform: scale(0.9); transition: transform 0.3s;
            }
            .booking-detail-overlay.active .booking-detail-content {
                transform: scale(1);
            }
            .booking-detail-close {
                position: absolute; top: 12px; right: 18px;
                background: none; border: none;
                font-size: 2rem; cursor: pointer; color: #999;
                line-height: 1;
            }
            .booking-detail-close:hover { color: #333; }
            .booking-detail-body { text-align: left; }
            .booking-detail-icon {
                font-size: 3rem; text-align: center; margin-bottom: 15px;
            }
            .booking-detail-body h2 {
                font-size: 1.5rem; text-align: center;
                margin-bottom: 20px; color: #1a1a2e;
            }
            .booking-detail-info {
                background: #f8f8f8; border-radius: 12px;
                padding: 20px; margin-bottom: 20px;
            }
            .booking-detail-row {
                display: flex; justify-content: space-between;
                padding: 8px 0; border-bottom: 1px solid #eee;
            }
            .booking-detail-row:last-child { border-bottom: none; }
            .booking-detail-label { color: #888; font-size: 0.9rem; }
            .booking-detail-value { color: #333; font-weight: 600; font-size: 0.95rem; }
            .booking-detail-comment {
                background: #fff8e1; border-radius: 12px;
                padding: 20px; border-left: 4px solid #d4a853;
            }
            .booking-detail-comment strong {
                display: block; margin-bottom: 8px; color: #1a1a2e;
            }
            .booking-detail-comment p {
                margin: 0; color: #555; line-height: 1.6;
                font-size: 0.95rem;
            }
            .booking-row:hover {
                background: rgba(212, 168, 83, 0.08);
            }
            </style>

            <script>
            function openBookingModal(id, name, phone, email, guests, date, time, comment, status, created) {
                document.getElementById('booking-detail-name').textContent = '#' + id + ' — ' + name;
                document.getElementById('booking-detail-phone').textContent = phone;
                document.getElementById('booking-detail-email').textContent = email || '—';
                document.getElementById('booking-detail-guests').textContent = guests + ' ' + (guests === 1 ? 'гость' : 'гостей');
                document.getElementById('booking-detail-date').textContent = date;
                document.getElementById('booking-detail-time').textContent = time;
                document.getElementById('booking-detail-status').textContent = status;
                document.getElementById('booking-detail-created').textContent = created;

                const commentEl = document.getElementById('booking-detail-comment-text');
                const commentBlock = document.getElementById('booking-detail-comment');
                if (comment) {
                    commentEl.textContent = comment;
                    commentBlock.style.display = 'block';
                } else {
                    commentBlock.style.display = 'none';
                }

                document.getElementById('booking-detail-modal').classList.add('active');
            }

            function closeBookingDetailModal() {
                document.getElementById('booking-detail-modal').classList.remove('active');
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeBookingDetailModal();
            });
            </script>

            <!-- Модальное окно подтверждения -->
            <div id="confirm-modal" class="confirm-overlay" onclick="if(event.target===this)closeConfirmModal()">
                <div class="confirm-content">
                    <div class="confirm-icon" id="confirm-icon">⚠️</div>
                    <h2 class="confirm-title" id="confirm-title">Вы уверены?</h2>
                    <p class="confirm-text" id="confirm-text">Это действие нельзя отменить.</p>
                    <div class="confirm-buttons">
                        <button class="btn confirm-btn-cancel" onclick="closeConfirmModal()">Нет, отмена</button>
                        <button class="btn confirm-btn-yes" id="confirm-yes-btn" onclick="executeConfirm()">Да, удалить</button>
                    </div>
                </div>
            </div>

            <style>
            .confirm-overlay {
                position: fixed; inset: 0; z-index: 999999;
                background: rgba(0,0,0,0.6);
                display: flex; align-items: center; justify-content: center;
                visibility: hidden; opacity: 0;
                transition: all 0.3s ease;
                backdrop-filter: blur(4px);
            }
            .confirm-overlay.active {
                visibility: visible; opacity: 1;
            }
            .confirm-content {
                background: #fff; border-radius: 20px;
                max-width: 400px; width: 90%;
                padding: 45px 35px 35px;
                text-align: center;
                box-shadow: 0 25px 80px rgba(0,0,0,0.4);
                transform: scale(0.85) translateY(20px);
                transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            }
            .confirm-overlay.active .confirm-content {
                transform: scale(1) translateY(0);
            }
            .confirm-icon {
                font-size: 3.5rem; margin-bottom: 15px;
            }
            .confirm-title {
                font-size: 1.4rem; color: #1a1a2e;
                margin-bottom: 10px;
            }
            .confirm-text {
                color: #666; font-size: 0.95rem;
                line-height: 1.6; margin-bottom: 28px;
            }
            .confirm-buttons {
                display: flex; gap: 10px; justify-content: center;
            }
            .confirm-btn-cancel {
                background: #f0f0f0 !important; color: #555 !important;
                border: none; padding: 12px 24px; border-radius: 10px;
                font-size: 0.9rem; font-weight: 600; cursor: pointer;
                transition: all 0.3s;
            }
            .confirm-btn-cancel:hover {
                background: #e0e0e0 !important; transform: translateY(-2px);
            }
            .confirm-btn-yes {
                background: #e74c3c !important; color: #fff !important;
                border: none; padding: 12px 24px; border-radius: 10px;
                font-size: 0.9rem; font-weight: 600; cursor: pointer;
                transition: all 0.3s;
            }
            .confirm-btn-yes:hover {
                background: #c0392b !important; transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
            }
            </style>

            <script>
            let confirmAction = null;

            function showConfirmModal(action) {
                const icon = document.getElementById('confirm-icon');
                const title = document.getElementById('confirm-title');
                const text = document.getElementById('confirm-text');
                const btn = document.getElementById('confirm-yes-btn');

                if (action === 'cancelled') {
                    icon.textContent = '🗑️';
                    title.textContent = 'Очистить отменённые?';
                    text.textContent = 'Будут удалены все бронирования со статусом "Отменено". Это действие нельзя отменить.';
                    btn.textContent = 'Да, очистить';
                } else if (action === 'all') {
                    icon.textContent = '⚠️';
                    title.textContent = 'Очистить всё?';
                    text.textContent = 'Будут удалены ВСЕ бронирования без возможности восстановления. Вы уверены?';
                    btn.textContent = 'Да, удалить всё';
                } else if (action === 'orders-completed') {
                    icon.textContent = '🗑️';
                    title.textContent = 'Очистить завершённые заказы?';
                    text.textContent = 'Будут удалены все заказы со статусом "Выполнен". Это действие нельзя отменить.';
                    btn.textContent = 'Да, очистить';
                } else if (action === 'orders-uncompleted') {
                    icon.textContent = '⚠️';
                    title.textContent = 'Очистить незавершённые заказы?';
                    text.textContent = 'Будут удалены все заказы, кроме выполненных и корзин. Это действие нельзя отменить.';
                    btn.textContent = 'Да, очистить';
                } else if (action === 'orders-all') {
                    icon.textContent = '⚠️';
                    title.textContent = 'Очистить все заказы?';
                    text.textContent = 'Будут удалены ВСЕ заказы (кроме корзин) без возможности восстановления. Вы уверены?';
                    btn.textContent = 'Да, удалить всё';
                }

                confirmAction = action;
                document.getElementById('confirm-modal').classList.add('active');
            }

            function closeConfirmModal() {
                document.getElementById('confirm-modal').classList.remove('active');
                confirmAction = null;
            }

            function executeConfirm() {
                if (!confirmAction) return;

                if (confirmAction.startsWith('orders-')) {
                    const orderAction = confirmAction.replace('orders-', '');
                    window.location.href = '../clearOrders.php?action=' + orderAction;
                } else {
                    window.location.href = '../clearBookings.php?action=' + confirmAction;
                }
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeConfirmModal();
            });
            </script>

        <!-- ==================== USERS ==================== -->
        <?php elseif ($page === 'users'): ?>
            
            <!-- Вкладки: Сотрудники / Клиенты -->
            <?php
            $usersTab = $_GET['tab'] ?? 'staff';
            ?>
            <div class="card">
                <div style="display:flex; gap:0; margin-bottom:20px; border-bottom:2px solid var(--color-border);">
                    <a href="?page=users&tab=staff" style="padding:10px 24px; text-decoration:none; font-weight:600; border-bottom:3px solid <?= $usersTab === 'staff' ? 'var(--color-primary)' : 'transparent' ?>; color:<?= $usersTab === 'staff' ? 'var(--color-primary)' : 'var(--color-text-light)' ?>; transition:all 0.2s;">
                        👥 Сотрудники ресторана
                    </a>
                    <a href="?page=users&tab=clients" style="padding:10px 24px; text-decoration:none; font-weight:600; border-bottom:3px solid <?= $usersTab === 'clients' ? 'var(--color-primary)' : 'transparent' ?>; color:<?= $usersTab === 'clients' ? 'var(--color-primary)' : 'var(--color-text-light)' ?>; transition:all 0.2s;">
                        🧑 Клиенты
                    </a>
                </div>

                <?php if ($usersTab === 'staff'): ?>
                    <h2>👥 Сотрудники ресторана</h2>
                    <p style="color: var(--color-text-light); margin-bottom: 20px;">
                        Пользователи с правами ADMIN и EMPLOYEE — персонал ресторана.
                    </p>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Фото</th>
                                    <th>Имя</th>
                                    <th>Телефон</th>
                                    <th>Должность</th>
                                    <th>Права</th>
                                    <th>Дата</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT u.*, ar.name as access_name
                                    FROM users u
                                    JOIN access_rights ar ON u.access_rights_id = ar.id
                                    WHERE u.access_rights_id IN (1, 3)
                                    ORDER BY u.access_rights_id ASC, u.position ASC
                                ");
                                while ($userRow = $stmt->fetch()):
                                ?>
                                    <tr class="user-row" style="cursor:pointer;" onclick="openUserModal(<?= $userRow['id'] ?>, '<?= htmlspecialchars($userRow['name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['position'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['bio'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['avatar'] ?? '', ENT_QUOTES) ?>', '<?= $userRow['access_name'] ?>', '<?= date('d.m.Y H:i', strtotime($userRow['created_at'])) ?>')">
                                        <td><?= $userRow['id'] ?></td>
                                        <td>
                                            <?php if ($userRow['avatar']): ?>
                                                <img src="../../frontend/uploads/<?= $userRow['avatar'] ?>" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:50%;">
                                            <?php else: ?>
                                                <span style="font-size:1.3rem;"><?= $userRow['access_name'] === 'ADMIN' ? '👨‍🍳' : '👤' ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($userRow['name'] ?? '—') ?></strong></td>
                                        <td><?= htmlspecialchars($userRow['phone']) ?></td>
                                        <td><?= htmlspecialchars($userRow['position'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge <?= $userRow['access_name'] === 'ADMIN' ? 'badge-danger' : 'badge-info' ?>">
                                                <?= $userRow['access_name'] === 'ADMIN' ? '👑 Админ' : '👔 Сотрудник' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($userRow['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <h2>🧑 Клиенты ресторана</h2>
                    <p style="color: var(--color-text-light); margin-bottom: 20px;">
                        Обычные пользователи (гости) — зарегистрировались на сайте.
                    </p>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Фото</th>
                                    <th>Имя</th>
                                    <th>Телефон</th>
                                    <th>Дата регистрации</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT u.*, ar.name as access_name
                                    FROM users u
                                    JOIN access_rights ar ON u.access_rights_id = ar.id
                                    WHERE u.access_rights_id = 2
                                    ORDER BY u.id DESC
                                ");
                                while ($userRow = $stmt->fetch()):
                                ?>
                                    <tr class="user-row" style="cursor:pointer;" onclick="openUserModal(<?= $userRow['id'] ?>, '<?= htmlspecialchars($userRow['name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['position'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['bio'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($userRow['avatar'] ?? '', ENT_QUOTES) ?>', '<?= $userRow['access_name'] ?>', '<?= date('d.m.Y H:i', strtotime($userRow['created_at'])) ?>')">
                                        <td><?= $userRow['id'] ?></td>
                                        <td>
                                            <?php if ($userRow['avatar']): ?>
                                                <img src="../../frontend/uploads/<?= $userRow['avatar'] ?>" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:50%;">
                                            <?php else: ?>
                                                <span style="font-size:1.3rem;">🧑</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($userRow['name'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($userRow['phone']) ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($userRow['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Форма создания сотрудника -->
            <div class="card" style="margin-top: 20px;">
                <h2>➕ Добавить сотрудника</h2>
                <form method="POST" action="../createEmployee.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Имя сотрудника</label>
                            <input type="text" name="name" placeholder="Например: Анна" required>
                        </div>
                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="text" name="phone" placeholder="+79990000000" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Должность</label>
                            <select name="position" required>
                                <option value="">Выберите должность</option>
                                <option value="Шеф-повар">👨‍🍳 Шеф-повар (админ)</option>
                                <option value="Су-шеф">👨‍🍳 Су-шеф</option>
                                <option value="Повар">👨‍🍳 Повар</option>
                                <option value="Кондитер">🧑‍🍳 Кондитер</option>
                                <option value="Официант">🧑‍💼 Официант</option>
                                <option value="Старший официант">🧑‍💼 Старший официант</option>
                                <option value="Администратор">👔 Администратор</option>
                                <option value="Менеджер">👔 Менеджер</option>
                                <option value="Бариста">☕ Бариста</option>
                                <option value="Бармен">🍸 Бармен</option>
                                <option value="Сомелье">🍷 Сомелье</option>
                                <option value="Курьер">🚚 Курьер</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Пароль</label>
                            <input type="text" name="password" placeholder="password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn">➕ Создать сотрудника</button>
                </form>
            </div>

            <!-- Модальное окно пользователя -->
            <div id="user-modal" class="user-modal-overlay" onclick="if(event.target===this)closeUserModal()">
                <div class="user-modal-content">
                    <button class="user-modal-close" onclick="closeUserModal()">&times;</button>
                    <div class="user-modal-body">
                        <div class="user-modal-avatar" id="user-modal-avatar">
                            <img src="" alt="Аватар" id="user-modal-img">
                        </div>
                        <h2 id="user-modal-name"></h2>
                        <div class="user-modal-position" id="user-modal-position"></div>
                        <div class="user-modal-phone" id="user-modal-phone"></div>
                        <div class="user-modal-role" id="user-modal-role"></div>
                        <div class="user-modal-date" id="user-modal-date"></div>
                        <div class="user-modal-bio" id="user-modal-bio"></div>
                    </div>
                </div>
            </div>

            <style>
            .user-modal-overlay {
                position: fixed; inset: 0; z-index: 99999;
                background: rgba(0,0,0,0.6);
                display: flex; align-items: center; justify-content: center;
                visibility: hidden; opacity: 0;
                transition: all 0.3s;
            }
            .user-modal-overlay.active {
                visibility: visible; opacity: 1;
            }
            .user-modal-content {
                background: #fff; border-radius: 16px;
                max-width: 420px; width: 90%; padding: 40px 30px;
                position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                transform: scale(0.9); transition: transform 0.3s;
            }
            .user-modal-overlay.active .user-modal-content {
                transform: scale(1);
            }
            .user-modal-close {
                position: absolute; top: 12px; right: 18px;
                background: none; border: none;
                font-size: 2rem; cursor: pointer; color: #999;
                line-height: 1;
            }
            .user-modal-close:hover { color: #333; }
            .user-modal-body { text-align: center; }
            .user-modal-avatar {
                width: 100px; height: 100px; border-radius: 50%;
                overflow: hidden; margin: 0 auto 15px;
                background: #f0f0f0; display: flex;
                align-items: center; justify-content: center;
            }
            .user-modal-avatar img {
                width: 100%; height: 100%; object-fit: cover;
            }
            .user-modal-body h2 {
                font-size: 1.4rem; margin-bottom: 5px; color: #1a1a2e;
            }
            .user-modal-position {
                font-size: 0.9rem; color: #d4a853; font-weight: 600; margin-bottom: 5px;
            }
            .user-modal-phone {
                font-size: 1rem; color: #555; margin-bottom: 8px;
            }
            .user-modal-role {
                display: inline-block;
                background: #d4a853; color: #fff;
                padding: 4px 14px; border-radius: 20px;
                font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
                letter-spacing: 1px; margin-bottom: 10px;
            }
            .user-modal-date {
                font-size: 0.8rem; color: #999; margin-bottom: 15px;
            }
            .user-modal-bio {
                font-size: 0.9rem; color: #555; line-height: 1.6;
                padding-top: 15px; border-top: 1px solid #eee;
            }
            .user-row:hover {
                background: rgba(212, 168, 83, 0.08);
            }

            </style>

            <script>


            function openUserModal(id, name, phone, position, bio, avatar, role, date) {
                document.getElementById('user-modal-name').textContent = name || 'Без имени';
                document.getElementById('user-modal-position').textContent = position || '';
                document.getElementById('user-modal-phone').textContent = phone;
                document.getElementById('user-modal-role').textContent = role;
                document.getElementById('user-modal-date').textContent = 'Зарегистрирован: ' + date;
                document.getElementById('user-modal-bio').textContent = bio || 'Пользователь не заполнил информацию о себе';

                const img = document.getElementById('user-modal-img');
                if (avatar) {
                    img.src = '../../frontend/uploads/' + avatar;
                    img.style.display = 'block';
                } else {
                    img.style.display = 'none';
                }

                document.getElementById('user-modal').classList.add('active');
            }

            function closeUserModal() {
                document.getElementById('user-modal').classList.remove('active');
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeUserModal();
            });
            </script>

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

        <!-- ==================== EDIT PROFILE ==================== -->
        <?php elseif ($page === 'edit-profile'): ?>
            <div class="card">
                <h2>Редактировать профиль</h2>
                <div style="display:flex; gap:30px; align-items:flex-start; flex-wrap:wrap;">
                    <div style="text-align:center; flex-shrink:0;">
                        <?php if ($adminProfile['avatar']): ?>
                            <img src="../../frontend/uploads/<?= $adminProfile['avatar'] ?>" alt="" style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid var(--color-primary);">
                        <?php else: ?>
                            <div style="width:120px;height:120px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#ccc;border:3px solid var(--color-primary);">👨‍🍳</div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;min-width:280px;">
                        <form method="POST" action="../updateEmployeeProfile.php" enctype="multipart/form-data">
                            <input type="hidden" name="user_id" value="<?= $adminProfile['id'] ?>">
                            <div class="form-group">
                                <label>Имя</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($adminProfile['name'] ?? '') ?>" placeholder="Ваше имя">
                            </div>
                            <div class="form-group">
                                <label>Должность</label>
                                <select name="position">
                                    <option value="Шеф-повар" <?= ($adminProfile['position'] ?? '') === 'Шеф-повар' ? 'selected' : '' ?>>Шеф-повар</option>
                                    <option value="Владелец" <?= ($adminProfile['position'] ?? '') === 'Владелец' ? 'selected' : '' ?>>Владелец</option>
                                    <option value="Шеф-повар и владелец" <?= ($adminProfile['position'] ?? '') === 'Шеф-повар и владелец' ? 'selected' : '' ?>>Шеф-повар и владелец</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>О себе</label>
                                <textarea name="bio" placeholder="Расскажите о себе" rows="4"><?= htmlspecialchars($adminProfile['bio'] ?? '') ?></textarea>
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

    <!-- Модальное окно просмотра фото -->
    <div id="image-modal" class="image-modal-overlay" onclick="if(event.target===this)closeImageModal()">
        <div class="image-modal-content">
            <button class="image-modal-close" onclick="closeImageModal()">&times;</button>
            <img src="" alt="" id="image-modal-img">
            <p class="image-modal-caption" id="image-modal-caption"></p>
        </div>
    </div>

    <style>
    .image-modal-overlay {
        position: fixed; inset: 0; z-index: 999999;
        background: rgba(0,0,0,0.85);
        display: flex; align-items: center; justify-content: center;
        visibility: hidden; opacity: 0;
        transition: all 0.3s ease;
        backdrop-filter: blur(8px);
    }
    .image-modal-overlay.active {
        visibility: visible; opacity: 1;
    }
    .image-modal-content {
        max-width: 600px; width: 90%;
        position: relative;
        transform: scale(0.8);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .image-modal-overlay.active .image-modal-content {
        transform: scale(1);
    }
    .image-modal-content img {
        width: 100%; height: auto;
        border-radius: 16px;
        box-shadow: 0 20px 80px rgba(0,0,0,0.6);
        display: block;
    }
    .image-modal-close {
        position: absolute; top: -40px; right: 0;
        background: none; border: none;
        font-size: 2.5rem; cursor: pointer; color: #fff;
        line-height: 1; opacity: 0.7;
        transition: opacity 0.2s;
    }
    .image-modal-close:hover { opacity: 1; }
    .image-modal-caption {
        color: #fff; text-align: center;
        margin-top: 15px; font-size: 1rem;
        opacity: 0.8;
    }
    /* Sidebar toggle */
    .sidebar-header { display: flex; align-items: center; gap: 10px; }
    .sidebar-toggle {
        background: none; border: none; color: rgba(255,255,255,0.7);
        font-size: 1.3rem; cursor: pointer; padding: 5px 10px;
        border-radius: 6px; transition: all 0.3s; flex-shrink: 0;
    }
    .sidebar-toggle:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .sidebar.collapsed { width: 60px; min-width: 60px; }
    .sidebar.collapsed + .main-content { margin-left: 60px; }
    .sidebar.collapsed .sidebar-header-text { display: none; }
    .sidebar.collapsed .sidebar-nav a span:not(.icon) { display: none; }
    .sidebar.collapsed .sidebar-nav a { justify-content: center; padding: 12px 0; }
    .sidebar.collapsed .sidebar-header { padding: 15px 0; justify-content: center; }
    </style>

    <script>
    function openMobileSidebar() {
        document.querySelector('.sidebar').classList.add('mobile-open');
        document.getElementById('sidebarOverlay').classList.add('active');
    }
    function closeMobileSidebar() {
        document.querySelector('.sidebar').classList.remove('mobile-open');
        document.getElementById('sidebarOverlay').classList.remove('active');
    }

    function openImageModal(src, name) {
        document.getElementById('image-modal-img').src = src;
        document.getElementById('image-modal-caption').textContent = name || '';
        document.getElementById('image-modal').classList.add('active');
    }

    function closeImageModal() {
        document.getElementById('image-modal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
    </script>

</body>
</html>
