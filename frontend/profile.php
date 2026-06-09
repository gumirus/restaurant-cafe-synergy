<?php
require_once __DIR__ . '/header.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT phone, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Получаем историю заказов
$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.created_at
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

$status_labels = [
    'pending'    => '🕐 Ожидает',
    'confirmed'  => '✅ Подтверждён',
    'preparing'  => '👨‍🍳 Готовится',
    'ready'      => '🍽️ Готов',
    'completed'  => '✔️ Выполнен',
    'cancelled'  => '❌ Отменён',
];
?>

    <!-- ========== PROFILE ========== -->
    <section class="profile-section">
        <div class="container">
            <h1 class="section-title">Личный <span style="color: var(--color-primary);">кабинет</span></h1>

            <div class="profile-info">
                <div class="profile-card">
                    <div class="profile-icon">👤</div>
                    <div class="profile-details">
                        <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                        <p><strong>Зарегистрирован:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>
            </div>

            <h2 class="section-title" style="margin-top: 60px; font-size: 1.5rem;">История <span style="color: var(--color-primary);">заказов</span></h2>

            <?php if (empty($orders)): ?>
                <div class="orders-empty">
                    <p>📋 У вас пока нет заказов</p>
                    <a href="menu.php" class="btn">Перейти в меню</a>
                </div>
            <?php else: ?>
                <div class="orders-table">
                    <div class="orders-header">
                        <div class="orders-col">№</div>
                        <div class="orders-col">Дата</div>
                        <div class="orders-col">Сумма</div>
                        <div class="orders-col">Статус</div>
                        <div class="orders-col">Детали</div>
                    </div>
                    <?php foreach ($orders as $order): ?>
                        <div class="orders-row">
                            <div class="orders-col">#<?= $order['id'] ?></div>
                            <div class="orders-col"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
                            <div class="orders-col"><?= number_format($order['total'], 2) ?> ₽</div>
                            <div class="orders-col">
                                <span class="order-status status-<?= $order['status'] ?>">
                                    <?= $status_labels[$order['status']] ?? $order['status'] ?>
                                </span>
                            </div>
                            <div class="orders-col">
                                <button class="btn btn-small order-details-btn" data-order-id="<?= $order['id'] ?>">Подробнее</button>
                            </div>
                        </div>
                        <!-- Детали заказа (скрыты) -->
                        <div class="order-items" id="order-items-<?= $order['id'] ?>" style="display: none;">
                            <?php
                            $stmt = $pdo->prepare("SELECT name, price, quantity FROM order_items WHERE order_id = ?");
                            $stmt->execute([$order['id']]);
                            $items = $stmt->fetchAll();
                            ?>
                            <table class="order-items-table">
                                <tr>
                                    <th>Блюдо</th>
                                    <th>Цена</th>
                                    <th>Кол-во</th>
                                    <th>Сумма</th>
                                </tr>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= number_format($item['price'], 2) ?> ₽</td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ₽</td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <style>
    .profile-section { padding: 120px 0 60px; }
    .profile-info { margin-top: 40px; }
    .profile-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 20px;
        max-width: 500px;
    }
    .profile-icon { font-size: 3rem; }
    .profile-details p { margin-bottom: 8px; color: var(--color-text); }
    .profile-details strong { color: var(--color-primary); }

    .orders-empty { text-align: center; padding: 60px 0; }
    .orders-empty p { font-size: 1.3rem; margin-bottom: 20px; color: var(--color-text); }

    .orders-table { margin-top: 30px; }
    .orders-header {
        display: grid;
        grid-template-columns: 0.5fr 1.5fr 1fr 1.5fr 1fr;
        padding: 15px 0;
        border-bottom: 2px solid var(--color-border);
        font-weight: 600;
        color: var(--color-primary);
    }
    .orders-row {
        display: grid;
        grid-template-columns: 0.5fr 1.5fr 1fr 1.5fr 1fr;
        padding: 18px 0;
        border-bottom: 1px solid var(--color-border);
        align-items: center;
        color: var(--color-text);
    }
    .order-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-confirmed { background: #d4edda; color: #155724; }
    .status-preparing { background: #cce5ff; color: #004085; }
    .status-ready { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    .btn-small {
        padding: 8px 16px;
        font-size: 0.85rem;
    }

    .order-items { padding: 15px 0 15px 20px; }
    .order-items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .order-items-table th {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-primary);
    }
    .order-items-table td {
        padding: 10px;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-text);
    }
    </style>

    <script>
    document.querySelectorAll('.order-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const items = document.getElementById('order-items-' + orderId);
            if (items.style.display === 'none') {
                items.style.display = 'block';
                this.textContent = 'Скрыть';
            } else {
                items.style.display = 'none';
                this.textContent = 'Подробнее';
            }
        });
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
