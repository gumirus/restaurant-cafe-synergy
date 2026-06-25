<?php
require_once __DIR__ . '/../backend/config/session.php';
require_once __DIR__ . '/../backend/config/db.php';

// Проверка авторизации — ДО header.php
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/header.php';

$user_id = $_SESSION['user_id'];

// ========== ОБРАБОТКА ФОРМ ==========

// Обновление профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Загрузка аватара
    $avatar = null;
    $avatarData = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), $allowedExt)) {
            // Сначала читаем в base64 (пока файл во временной папке)
            $imgData = file_get_contents($file['tmp_name']);
            if ($imgData !== false) {
                $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                $avatarData = 'data:' . $mime . ';base64,' . base64_encode($imgData);
            }
            // Потом сохраняем файл
            $avatar = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $avatar);
        }
    }

    if ($avatarData) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, avatar = ?, avatar_data = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $avatar, $avatarData, $user_id]);
    } elseif ($avatar) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $avatar, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $user_id]);
    }
    $success = '✅ Профиль обновлён';
}

// Повторение заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repeat_order'])) {
    $order_id = (int)$_POST['order_id'];

    // Получаем товары из заказа
    $stmt = $pdo->prepare("SELECT dish_id, count as quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Ищем активный заказ (корзину) или создаём новый
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
    $stmt->execute([$user_id]);
    $cartOrder = $stmt->fetch();

    if (!$cartOrder) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, status, total_price) VALUES (?, 'cart', 0)");
        $stmt->execute([$user_id]);
        $cartOrderId = $pdo->lastInsertId();
    } else {
        $cartOrderId = $cartOrder['id'];
    }

    foreach ($items as $item) {
        // Проверяем, есть ли уже это блюдо в корзине
        $stmt = $pdo->prepare("SELECT id, count FROM order_items WHERE order_id = ? AND dish_id = ?");
        $stmt->execute([$cartOrderId, $item['dish_id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE order_items SET count = count + ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, count, price) VALUES (?, ?, ?, (SELECT price FROM dishes WHERE id = ?))");
            $stmt->execute([$cartOrderId, $item['dish_id'], $item['quantity'], $item['dish_id']]);
        }
    }

    // Пересчитываем сумму
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(count * price), 0) FROM order_items WHERE order_id = ?");
    $stmt->execute([$cartOrderId]);
    $totalPrice = (float)$stmt->fetchColumn();
    $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $stmt->execute([$totalPrice, $cartOrderId]);

    $success = '✅ Заказ #' . $order_id . ' добавлен в корзину!';
}

// ========== ПОЛУЧЕНИЕ ДАННЫХ ==========

// Информация о пользователе
$stmt = $pdo->prepare("SELECT phone, name, bio, avatar, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo '<h2>Пользователь не найден</h2><p>Возможно, ваш аккаунт был удалён.</p><a href="index.php">На главную</a>';
    require __DIR__ . '/footer.php';
    exit;
}

// История заказов (исключая корзины)
$stmt = $pdo->prepare("
    SELECT o.id, o.total_price AS total, o.status, o.created_at
    FROM orders o
    WHERE o.user_id = ? AND o.status != 'cart'
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

// История бронирований (по user_id, если есть, иначе по телефону)
$stmt = $pdo->prepare("
    SELECT b.id, b.name, b.phone, b.guests, b.booking_date, b.booking_time, b.comment, b.status, b.created_at
    FROM bookings b
    WHERE b.user_id = ? OR (b.user_id IS NULL AND b.phone = ?)
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id, $user['phone']]);
$bookings = $stmt->fetchAll();

$booking_labels = [
    'pending'   => '🕐 Ожидание',
    'confirmed' => '✅ Подтверждено',
    'cancelled' => '❌ Отменено',
];

$avatar_url = $user['avatar'] ? 'uploads/' . $user['avatar'] : 'images/default-avatar.svg';
?>

    <!-- ========== PROFILE ========== -->
    <section class="profile-section">
        <div class="container">
            <h1 class="section-title">Личный <span style="color: var(--color-primary);">кабинет</span></h1>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- ===== ЛЕВАЯ КОЛОНКА — Профиль ===== -->
                <div class="profile-card">
                    <h2>Мой профиль</h2>
                    <form method="POST" enctype="multipart/form-data" class="profile-form">
                        <div class="avatar-section">
                            <div class="avatar-preview">
                                <img src="<?= $avatar_url ?>" alt="Аватар" id="avatar-preview-img">
                            </div>
                            <label class="btn btn-small avatar-upload-btn">
                                📷 Загрузить фото
                                <input type="file" name="avatar" accept="image/*" style="display:none" onchange="previewAvatar(event)">
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="text" value="<?= htmlspecialchars($user['phone']) ?>" disabled class="input-disabled">
                        </div>

                        <div class="form-group">
                            <label>Имя</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Ваше имя">
                        </div>

                        <div class="form-group">
                            <label>О себе</label>
                            <textarea name="bio" placeholder="Расскажите о себе..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" name="update_profile" class="btn">💾 Сохранить</button>
                    </form>
                </div>

                <!-- ===== ПРАВАЯ КОЛОНКА — Заказы ===== -->
                <div class="profile-orders">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <h2 style="margin:0;">📅 Мои бронирования</h2>
                        <?php if (!empty($bookings)): ?>
                            <a href="javascript:void(0)" class="btn btn-small" style="background:#e74c3c;color:#fff;" onclick="openClearModal('Очистить историю бронирований?', 'Все бронирования будут удалены без возможности восстановления.', '../backend/clearBookingHistory.php')">🗑 Очистить историю</a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($bookings)): ?>
                        <div class="orders-empty">
                            <p>📅 У вас пока нет бронирований</p>
                            <a href="contact.php" class="btn">Забронировать столик</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <div class="order-card">
                                <div class="order-card-header">
                                    <span class="order-number">Бронь #<?= $booking['id'] ?></span>
                                    <span class="order-date"><?= date('d.m.Y', strtotime($booking['booking_date'])) ?> в <?= $booking['booking_time'] ?></span>
                                    <span class="order-status-badge status-<?= $booking['status'] === 'confirmed' ? 'completed' : ($booking['status'] === 'cancelled' ? 'cancelled' : 'pending') ?>">
                                        <?= $booking_labels[$booking['status']] ?>
                                    </span>
                                </div>
                                <div class="order-card-body">
                                    <div style="display:flex; gap:20px; flex-wrap:wrap; padding:5px 0;">
                                        <span>👥 <?= $booking['guests'] ?> <?= $booking['guests'] === 1 ? 'гость' : 'гостей' ?></span>
                                        <span>📞 <?= htmlspecialchars($booking['phone']) ?></span>
                                        <?php if ($booking['comment']): ?>
                                            <span>💬 <?= htmlspecialchars($booking['comment']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($booking['status'] === 'confirmed'): ?>
                                    <?php
                                    // Проверяем, есть ли уже отзыв на бронь
                                    $stmtFbB = $pdo->prepare("SELECT rating, comment FROM booking_feedback WHERE booking_id = ?");
                                    $stmtFbB->execute([$booking['id']]);
                                    $fbBooking = $stmtFbB->fetch();
                                    ?>
                                    <?php if ($fbBooking): ?>
                                        <div class="order-feedback-existing">
                                            <span class="feedback-rating">
                                                <?= $fbBooking['rating'] === 'like' ? '👍' : '👎' ?>
                                            </span>
                                            <?php if ($fbBooking['comment']): ?>
                                                <span class="feedback-comment"><?= htmlspecialchars($fbBooking['comment']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="order-feedback-form">
                                            <form method="POST" action="../backend/submitBookingFeedback.php">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <div class="feedback-buttons">
                                                    <button type="submit" name="rating" value="like" class="feedback-btn feedback-like" title="Всё понравилось">👍</button>
                                                    <button type="submit" name="rating" value="dislike" class="feedback-btn feedback-dislike" title="Что-то не понравилось">👎</button>
                                                </div>
                                                <div class="feedback-comment-input">
                                                    <input type="text" name="comment" placeholder="Как вам обстановка, обслуживание, еда? (необязательно)..." maxlength="500">
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:40px;">
                        <h2 style="margin:0;">📋 История заказов</h2>
                        <?php if (!empty($orders)): ?>
                            <a href="javascript:void(0)" class="btn btn-small" style="background:#e74c3c;color:#fff;" onclick="openClearModal('Очистить историю заказов?', 'Все заказы будут удалены без возможности восстановления.', '../backend/clearOrderHistory.php')">🗑 Очистить историю</a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($orders)): ?>
                        <div class="orders-empty">
                            <p>📋 У вас пока нет заказов</p>
                            <a href="menu.php" class="btn">Перейти в меню</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-card-header">
                                    <span class="order-number">Заказ #<?= $order['id'] ?></span>
                                    <span class="order-date"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                                    <span class="order-status-badge status-<?= $order['status'] ?>">
                                        <?= $status_labels[$order['status']] ?>
                                    </span>
                                </div>
                                <div class="order-card-body">
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT d.name, oi.price, oi.count as quantity
                                        FROM order_items oi
                                        JOIN dishes d ON oi.dish_id = d.id
                                        WHERE oi.order_id = ?
                                    ");
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
                                <div class="order-card-footer">
                                    <span class="order-total">Итого: <strong><?= number_format($order['total'], 2) ?> ₽</strong></span>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" name="repeat_order" class="btn btn-small">🔄 Повторить заказ</button>
                                    </form>
                                </div>

                                <?php if ($order['status'] === 'completed'): ?>
                                    <?php
                                    // Проверяем, есть ли уже отзыв
                                    $stmtFb = $pdo->prepare("SELECT rating, comment FROM order_feedback WHERE order_id = ?");
                                    $stmtFb->execute([$order['id']]);
                                    $feedback = $stmtFb->fetch();
                                    ?>
                                    <?php if ($feedback): ?>
                                        <div class="order-feedback-existing">
                                            <span class="feedback-rating">
                                                <?= $feedback['rating'] === 'like' ? '👍' : '👎' ?>
                                            </span>
                                            <?php if ($feedback['comment']): ?>
                                                <span class="feedback-comment"><?= htmlspecialchars($feedback['comment']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="order-feedback-form">
                                            <form method="POST" action="../backend/submitFeedback.php">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <div class="feedback-buttons">
                                                    <button type="submit" name="rating" value="like" class="feedback-btn feedback-like" title="Всё понравилось">👍</button>
                                                    <button type="submit" name="rating" value="dislike" class="feedback-btn feedback-dislike" title="Что-то не понравилось">👎</button>
                                                </div>
                                                <div class="feedback-comment-input">
                                                    <input type="text" name="comment" placeholder="Напишите комментарий (необязательно)..." maxlength="500">
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <style>
    .profile-section { padding: 120px 0 60px; }
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

    .profile-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 40px;
        margin-top: 40px;
    }

    /* ===== КАРТОЧКА ПРОФИЛЯ ===== */
    .profile-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 30px;
    }
    .profile-card h2 {
        color: var(--color-primary);
        margin-bottom: 25px;
        font-size: 1.3rem;
    }
    .profile-form .form-group {
        margin-bottom: 18px;
    }
    .profile-form label {
        display: block;
        margin-bottom: 6px;
        color: var(--color-text);
        font-weight: 500;
        font-size: 0.9rem;
    }
    .profile-form input[type="text"],
    .profile-form textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        background: var(--color-bg);
        color: var(--color-text);
        font-size: 0.95rem;
        transition: border-color 0.3s;
    }
    .profile-form input:focus,
    .profile-form textarea:focus {
        outline: none;
        border-color: var(--color-primary);
    }
    .profile-form textarea {
        min-height: 100px;
        resize: vertical;
    }
    .input-disabled {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        background: rgba(255,255,255,0.05);
        color: var(--color-text-light);
        font-size: 0.95rem;
        opacity: 0.7;
    }

    /* ===== АВАТАР ===== */
    .avatar-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }
    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid var(--color-primary);
    }
    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-upload-btn {
        cursor: pointer;
        font-size: 0.85rem;
    }
    .btn-small {
        padding: 8px 18px;
        font-size: 0.85rem;
    }

    /* ===== ЗАКАЗЫ ===== */
    .profile-orders h2 {
        color: var(--color-primary);
        margin-bottom: 25px;
        font-size: 1.3rem;
    }
    .orders-empty { text-align: center; padding: 60px 0; }
    .orders-empty p { font-size: 1.2rem; margin-bottom: 20px; color: var(--color-text); }

    .order-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: rgba(255,255,255,0.03);
        border-bottom: 1px solid var(--color-border);
    }
    .order-number { font-weight: 700; color: var(--color-primary); }
    .order-date { color: var(--color-text-light); font-size: 0.85rem; }
    .order-status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-confirmed { background: #d4edda; color: #155724; }
    .status-preparing { background: #cce5ff; color: #004085; }
    .status-ready { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    .order-card-body { padding: 15px 20px; }
    .order-items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .order-items-table th {
        text-align: left;
        padding: 8px 10px;
        border-bottom: 2px solid var(--color-border);
        color: var(--color-primary);
        font-weight: 600;
    }
    .order-items-table td {
        padding: 8px 10px;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-text);
    }

    .order-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-top: 1px solid var(--color-border);
        background: rgba(255,255,255,0.02);
    }
    .order-total {
        font-size: 1.1rem;
        color: var(--color-text);
    }
    .order-total strong {
        color: var(--color-primary);
        font-size: 1.2rem;
    }

    /* ===== ОБРАТНАЯ СВЯЗЬ ===== */
    .order-feedback-existing {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: rgba(212, 168, 83, 0.08);
        border-top: 1px solid var(--color-border);
        font-size: 0.9rem;
    }
    .feedback-rating {
        font-size: 1.3rem;
    }
    .feedback-comment {
        color: var(--color-text-light);
        font-style: italic;
    }
    .order-feedback-form {
        padding: 12px 20px;
        background: rgba(255,255,255,0.03);
        border-top: 1px solid var(--color-border);
    }
    .feedback-buttons {
        display: flex;
        gap: 8px;
        margin-bottom: 8px;
    }
    .feedback-btn {
        padding: 6px 16px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        background: var(--color-surface);
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.2s;
    }
    .feedback-btn:hover {
        transform: scale(1.15);
    }
    .feedback-like:hover {
        border-color: #27ae60;
        background: rgba(39, 174, 96, 0.1);
    }
    .feedback-dislike:hover {
        border-color: #e74c3c;
        background: rgba(231, 76, 60, 0.1);
    }
    .feedback-comment-input input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        background: var(--color-bg);
        color: var(--color-text);
        font-size: 0.85rem;
        transition: border-color 0.3s;
    }
    .feedback-comment-input input:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    /* ===== АДАПТИВ ===== */
    @media (max-width: 768px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <!-- ===== МОДАЛКА ПОДТВЕРЖДЕНИЯ ОЧИСТКИ ===== -->
    <div id="clear-modal" class="clear-modal-overlay" onclick="if(event.target===this)closeClearModal()">
        <div class="clear-modal-content">
            <div class="clear-modal-icon" id="clear-modal-icon">🗑️</div>
            <h2 class="clear-modal-title" id="clear-modal-title">Очистить историю?</h2>
            <p class="clear-modal-text" id="clear-modal-text">Все записи будут удалены без возможности восстановления.</p>
            <div class="clear-modal-buttons">
                <button class="btn clear-btn-cancel" onclick="closeClearModal()">Нет, отмена</button>
                <a href="#" class="btn clear-btn-yes" id="clear-modal-link">Да, очистить</a>
            </div>
        </div>
    </div>

    <style>
    .clear-modal-overlay {
        position: fixed; inset: 0; z-index: 999999;
        background: rgba(0,0,0,0.6);
        display: flex; align-items: center; justify-content: center;
        visibility: hidden; opacity: 0;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
    }
    .clear-modal-overlay.active {
        visibility: visible; opacity: 1;
    }
    .clear-modal-content {
        background: #fff; border-radius: 20px;
        max-width: 400px; width: 90%;
        padding: 45px 35px 35px;
        text-align: center;
        box-shadow: 0 25px 80px rgba(0,0,0,0.4);
        transform: scale(0.85) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .clear-modal-overlay.active .clear-modal-content {
        transform: scale(1) translateY(0);
    }
    .clear-modal-icon {
        font-size: 3.5rem; margin-bottom: 15px;
    }
    .clear-modal-title {
        font-size: 1.4rem; color: #1a1a2e;
        margin-bottom: 10px;
    }
    .clear-modal-text {
        color: #666; font-size: 0.95rem;
        line-height: 1.6; margin-bottom: 28px;
    }
    .clear-modal-buttons {
        display: flex; gap: 10px; justify-content: center;
    }
    .clear-btn-cancel {
        background: #f0f0f0 !important; color: #555 !important;
        border: none; padding: 12px 24px; border-radius: 10px;
        font-size: 0.9rem; font-weight: 600; cursor: pointer;
        transition: all 0.3s;
    }
    .clear-btn-cancel:hover {
        background: #e0e0e0 !important; transform: translateY(-2px);
    }
    .clear-btn-yes {
        background: #e74c3c !important; color: #fff !important;
        border: none; padding: 12px 24px; border-radius: 10px;
        font-size: 0.9rem; font-weight: 600; cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }
    .clear-btn-yes:hover {
        background: #c0392b !important; transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
    }
    </style>

    <script>
    function previewAvatar(event) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('avatar-preview-img').src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function openClearModal(title, text, link) {
        document.getElementById('clear-modal-icon').textContent = '🗑️';
        document.getElementById('clear-modal-title').textContent = title;
        document.getElementById('clear-modal-text').textContent = text;
        document.getElementById('clear-modal-link').href = link;
        document.getElementById('clear-modal').classList.add('active');
    }

    function closeClearModal() {
        document.getElementById('clear-modal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeClearModal();
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
