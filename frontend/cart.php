<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../backend/config/db.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ищем активный заказ (корзину)
$stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
$stmt->execute([$user_id]);
$order = $stmt->fetch();

$cart_items = [];
$total = 0;

if ($order) {
    // Получаем товары из активного заказа
    $stmt = $pdo->prepare("
        SELECT oi.id, oi.count as quantity, d.id as dish_id, d.name, d.price, d.image
        FROM order_items oi
        JOIN dishes d ON oi.dish_id = d.id
        WHERE oi.order_id = ?
        ORDER BY oi.id DESC
    ");
    $stmt->execute([$order['id']]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>

    <!-- ========== CART ========== -->
    <section class="cart-section">
        <div class="container">
            <h1 class="section-title">Ваша <span style="color: var(--color-primary);">корзина</span></h1>

            <?php if (empty($cart_items)): ?>
                <div class="cart-empty">
                    <p>🛒 Ваша корзина пуста</p>
                    <a href="menu.php" class="btn">Перейти в меню</a>
                </div>
            <?php else: ?>
                <div class="cart-table">
                    <div class="cart-header">
                        <div class="cart-col cart-col-product">Товар</div>
                        <div class="cart-col cart-col-price">Цена</div>
                        <div class="cart-col cart-col-qty">Количество</div>
                        <div class="cart-col cart-col-total">Сумма</div>
                        <div class="cart-col cart-col-action"></div>
                    </div>
                    <?php foreach ($cart_items as $item): ?>
                        <?php $subtotal = $item['price'] * $item['quantity']; ?>
                        <div class="cart-row" data-cart-id="<?= $item['id'] ?>">
                            <div class="cart-col cart-col-product">
                                <img src="<?= htmlspecialchars($item['image'] ?: 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <div class="cart-col cart-col-price"><?= number_format($item['price'], 2) ?> ₽</div>
                            <div class="cart-col cart-col-qty">
                                <button class="qty-btn qty-minus" data-cart-id="<?= $item['id'] ?>">−</button>
                                <span class="qty-value"><?= $item['quantity'] ?></span>
                                <button class="qty-btn qty-plus" data-cart-id="<?= $item['id'] ?>">+</button>
                            </div>
                            <div class="cart-col cart-col-total"><?= number_format($subtotal, 2) ?> ₽</div>
                            <div class="cart-col cart-col-action">
                                <button class="cart-remove" data-cart-id="<?= $item['id'] ?>">✕</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Итого:</span>
                        <strong id="cart-total-amount"><?= number_format($total, 2) ?> ₽</strong>
                    </div>
                    <div class="cart-actions">
                        <a href="menu.php" class="btn btn-outline">← Вернуться в меню</a>
                        <button class="btn" id="checkout-btn">Оформить заказ</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ========== МОДАЛКА ОФОРМЛЕНИЯ ========== -->
    <div id="checkout-modal" class="checkout-overlay" onclick="if(event.target===this)closeCheckoutModal()">
        <div class="checkout-content">
            <button class="checkout-close" onclick="closeCheckoutModal()">&times;</button>
            <h2>Оформление заказа</h2>
            <p style="color:var(--color-text-light);margin-bottom:20px;">Выберите способ получения</p>

            <!-- Шаг 1: Выбор типа -->
            <div id="checkout-step-1">
                <div class="checkout-types">
                    <label class="checkout-type" onclick="selectType('delivery')">
                        <input type="radio" name="order-type" value="delivery" checked>
                        <span class="checkout-type-icon">🏠</span>
                        <span class="checkout-type-title">Доставка на дом</span>
                        <span class="checkout-type-desc">Привезём заказ по вашему адресу</span>
                    </label>
                    <label class="checkout-type" onclick="selectType('pickup')">
                        <input type="radio" name="order-type" value="pickup">
                        <span class="checkout-type-icon">🚶</span>
                        <span class="checkout-type-title">Самовывоз</span>
                        <span class="checkout-type-desc">Заберёте заказ самостоятельно</span>
                    </label>
                    <label class="checkout-type" onclick="selectType('booking')">
                        <input type="radio" name="order-type" value="booking">
                        <span class="checkout-type-icon">🍽️</span>
                        <span class="checkout-type-title">Забронировать столик</span>
                        <span class="checkout-type-desc">Заказ подадут к столу</span>
                    </label>
                </div>

                <!-- Поля для доставки -->
                <div id="checkout-delivery-fields" class="checkout-fields">
                    <div class="form-group">
                        <label>📍 Адрес доставки</label>
                        <input type="text" id="checkout-address" placeholder="Улица, дом, квартира" value="<?= htmlspecialchars($_SESSION['user_address'] ?? '') ?>">
                    </div>
                </div>

                <!-- Поля для брони -->
                <div id="checkout-booking-fields" class="checkout-fields" style="display:none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>📅 Дата</label>
                            <input type="date" id="checkout-date">
                        </div>
                        <div class="form-group">
                            <label>⏰ Время</label>
                            <input type="time" id="checkout-time">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>👥 Количество гостей</label>
                        <input type="number" id="checkout-guests" value="2" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label>💬 Пожелания</label>
                        <textarea id="checkout-comment" placeholder="Особые пожелания..." rows="2"></textarea>
                    </div>
                </div>

                <button class="btn" id="checkout-next-btn" style="width:100%;margin-top:20px;" onclick="goToPayment()">Продолжить →</button>
            </div>

            <!-- Шаг 2: Оплата -->
            <div id="checkout-step-2" style="display:none;">
                <div class="checkout-summary">
                    <div class="checkout-summary-row">
                        <span>Способ получения:</span>
                        <strong id="checkout-summary-type">Доставка на дом</strong>
                    </div>
                    <div class="checkout-summary-row">
                        <span>Сумма заказа:</span>
                        <strong id="checkout-summary-total"><?= number_format($total, 2) ?> ₽</strong>
                    </div>
                    <div class="checkout-summary-row" id="checkout-summary-address-row">
                        <span>Адрес:</span>
                        <span id="checkout-summary-address"></span>
                    </div>
                    <div class="checkout-summary-row" id="checkout-summary-booking-row" style="display:none;">
                        <span>Бронь:</span>
                        <span id="checkout-summary-booking"></span>
                    </div>
                </div>

                <div class="checkout-payment">
                    <h3>💳 Оплата</h3>
                    <p style="color:var(--color-text-light);font-size:0.9rem;margin-bottom:15px;">
                        Демо-режим: нажмите «Оплатить» для имитации оплаты
                    </p>
                    <div class="checkout-card">
                        <div class="form-group">
                            <label>Номер карты</label>
                            <input type="text" value="4242 4242 4242 4242" readonly style="opacity:0.7;cursor:not-allowed;">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Срок</label>
                                <input type="text" value="12/28" readonly style="opacity:0.7;cursor:not-allowed;">
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" value="123" readonly style="opacity:0.7;cursor:not-allowed;">
                            </div>
                        </div>
                    </div>
                    <button class="btn" id="checkout-pay-btn" style="width:100%;margin-top:15px;" onclick="payOrder()">💳 Оплатить <?= number_format($total, 2) ?> ₽</button>
                    <button class="btn btn-outline" style="width:100%;margin-top:8px;" onclick="backToStep1()">← Назад</button>
                </div>
            </div>

            <!-- Шаг 3: Успех -->
            <div id="checkout-step-3" style="display:none;text-align:center;padding:30px 0;">
                <div style="font-size:4rem;margin-bottom:15px;">✅</div>
                <h2>Заказ оформлен!</h2>
                <p style="color:var(--color-text-light);margin:10px 0 5px;">Номер заказа: <strong id="checkout-order-number" style="color:var(--color-primary);font-size:1.3rem;"></strong></p>
                <p style="color:var(--color-text-light);" id="checkout-success-msg"></p>
                <div style="margin-top:25px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <a href="menu.php" class="btn">🍽 Вернуться в меню</a>
                    <a href="profile.php" class="btn btn-outline">📦 Мои заказы</a>
                </div>
            </div>
        </div>
    </div>

    <style>
    .cart-section { padding: 120px 0 60px; }
    .cart-empty { text-align: center; padding: 60px 0; }
    .cart-empty p { font-size: 1.5rem; margin-bottom: 20px; color: var(--color-text); }
    .cart-table { margin-top: 40px; }
    .cart-header {
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
        padding: 15px 0;
        border-bottom: 2px solid var(--color-border);
        font-weight: 600;
        color: var(--color-primary);
    }
    .cart-row {
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
        padding: 20px 0;
        border-bottom: 1px solid var(--color-border);
        align-items: center;
    }
    .cart-col-product {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .cart-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    .qty-btn {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text);
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.1rem;
        transition: all 0.3s;
    }
    .qty-btn:hover { background: var(--color-primary); color: #fff; }
    .qty-value {
        display: inline-block;
        min-width: 30px;
        text-align: center;
        font-weight: 600;
    }
    .cart-remove {
        background: none;
        border: none;
        color: #e74c3c;
        font-size: 1.2rem;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .cart-remove:hover { transform: scale(1.2); }
    .cart-summary {
        margin-top: 30px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
    }
    .cart-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .btn-outline {
        background: transparent;
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s;
    }
    .btn-outline:hover {
        background: var(--color-primary);
        color: #fff;
    }
    .cart-total {
        font-size: 1.3rem;
        color: var(--color-text);
    }
    .cart-total strong {
        color: var(--color-primary);
        font-size: 1.5rem;
    }

    /* ===== МОДАЛКА ОФОРМЛЕНИЯ ===== */
    .checkout-overlay {
        position: fixed; inset: 0; z-index: 99999;
        background: rgba(0,0,0,0.7);
        display: flex; align-items: center; justify-content: center;
        visibility: hidden; opacity: 0;
        transition: all 0.3s ease;
    }
    .checkout-overlay.active {
        visibility: visible; opacity: 1;
    }
    .checkout-content {
        background: #1a1a2e;
        border-radius: 20px;
        max-width: 520px; width: 92%;
        padding: 35px 30px;
        position: relative;
        box-shadow: 0 30px 80px rgba(0,0,0,0.5);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-height: 90vh; overflow-y: auto;
    }
    .checkout-overlay.active .checkout-content {
        transform: scale(1) translateY(0);
    }
    .checkout-close {
        position: absolute; top: 12px; right: 18px;
        background: rgba(255,255,255,0.1); border: none;
        font-size: 1.8rem; cursor: pointer; color: #fff;
        line-height: 1; width: 36px; height: 36px;
        border-radius: 50%; display: flex;
        align-items: center; justify-content: center;
        z-index: 2; transition: background 0.2s;
    }
    .checkout-close:hover { background: rgba(255,255,255,0.2); }
    .checkout-content h2 {
        color: var(--color-text-white);
        margin-bottom: 5px;
    }
    .checkout-types {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
    }
    .checkout-type {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: rgba(255,255,255,0.05);
        border: 2px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .checkout-type:hover {
        border-color: var(--color-primary);
        background: rgba(212,168,83,0.1);
    }
    .checkout-type.active {
        border-color: var(--color-primary);
        background: rgba(212,168,83,0.15);
    }
    .checkout-type input { display: none; }
    .checkout-type-icon { font-size: 1.8rem; }
    .checkout-type-title {
        font-weight: 600;
        color: var(--color-text-white);
        font-size: 1rem;
    }
    .checkout-type-desc {
        display: block;
        color: var(--color-text-light);
        font-size: 0.8rem;
        margin-top: 2px;
    }
    .checkout-fields {
        margin-top: 15px;
    }
    .checkout-fields .form-group {
        margin-bottom: 12px;
    }
    .checkout-fields label {
        display: block;
        color: var(--color-text-light);
        font-size: 0.85rem;
        margin-bottom: 5px;
    }
    .checkout-fields input,
    .checkout-fields textarea,
    .checkout-fields select {
        width: 100%;
        padding: 10px 14px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        color: #fff;
        font-size: 0.95rem;
        transition: border-color 0.3s;
    }
    .checkout-fields input:focus,
    .checkout-fields textarea:focus {
        border-color: var(--color-primary);
        outline: none;
    }
    .checkout-fields .form-row {
        display: flex;
        gap: 12px;
    }
    .checkout-fields .form-row .form-group {
        flex: 1;
    }
    .checkout-summary {
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }
    .checkout-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        color: var(--color-text-light);
        font-size: 0.9rem;
    }
    .checkout-summary-row:last-child { border-bottom: none; }
    .checkout-summary-row strong {
        color: var(--color-text-white);
    }
    .checkout-payment h3 {
        color: var(--color-text-white);
        margin-bottom: 5px;
    }
    .checkout-card {
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 16px;
    }
    .checkout-card input {
        width: 100%;
        padding: 10px 14px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        color: #fff;
        font-size: 0.95rem;
    }
    .checkout-card .form-row {
        display: flex;
        gap: 12px;
    }
    .checkout-card .form-row .form-group {
        flex: 1;
    }
    .checkout-card label {
        display: block;
        color: var(--color-text-light);
        font-size: 0.85rem;
        margin-bottom: 5px;
    }
    </style>

    <script>
    let currentType = 'delivery';
    let currentOrderId = null;

    function openCheckoutModal() {
        document.getElementById('checkout-modal').classList.add('active');
        // Устанавливаем сегодняшнюю дату по умолчанию
        const today = new Date();
        document.getElementById('checkout-date').value = today.toISOString().split('T')[0];
        document.getElementById('checkout-time').value = '19:00';
    }

    function closeCheckoutModal() {
        document.getElementById('checkout-modal').classList.remove('active');
        // Сброс шагов
        document.getElementById('checkout-step-1').style.display = 'block';
        document.getElementById('checkout-step-2').style.display = 'none';
        document.getElementById('checkout-step-3').style.display = 'none';
    }

    function selectType(type) {
        currentType = type;
        document.querySelectorAll('.checkout-type').forEach(el => el.classList.remove('active'));
        document.querySelector(`.checkout-type input[value="${type}"]`).closest('.checkout-type').classList.add('active');
        document.querySelector(`.checkout-type input[value="${type}"]`).checked = true;

        // Показываем нужные поля
        document.getElementById('checkout-delivery-fields').style.display = type === 'delivery' ? 'block' : 'none';
        document.getElementById('checkout-booking-fields').style.display = type === 'booking' ? 'block' : 'none';
    }

    function goToPayment() {
        // Валидация
        if (currentType === 'delivery') {
            const addr = document.getElementById('checkout-address').value.trim();
            if (!addr) {
                alert('Укажите адрес доставки');
                return;
            }
        }
        if (currentType === 'booking') {
            const date = document.getElementById('checkout-date').value;
            const time = document.getElementById('checkout-time').value;
            if (!date || !time) {
                alert('Укажите дату и время бронирования');
                return;
            }
        }

        // Заполняем сводку
        const typeLabels = {
            delivery: '🏠 Доставка на дом',
            pickup: '🚶 Самовывоз',
            booking: '🍽️ Бронь столика'
        };
        document.getElementById('checkout-summary-type').textContent = typeLabels[currentType];

        const totalEl = document.getElementById('cart-total-amount');
        document.getElementById('checkout-summary-total').textContent = totalEl.textContent;

        // Адрес
        const addrRow = document.getElementById('checkout-summary-address-row');
        if (currentType === 'delivery') {
            addrRow.style.display = 'flex';
            document.getElementById('checkout-summary-address').textContent = document.getElementById('checkout-address').value;
        } else {
            addrRow.style.display = 'none';
        }

        // Бронь
        const bookingRow = document.getElementById('checkout-summary-booking-row');
        if (currentType === 'booking') {
            bookingRow.style.display = 'flex';
            document.getElementById('checkout-summary-booking').textContent =
                document.getElementById('checkout-date').value + ' в ' + document.getElementById('checkout-time').value +
                ', ' + document.getElementById('checkout-guests').value + ' гостей';
        } else {
            bookingRow.style.display = 'none';
        }

        document.getElementById('checkout-step-1').style.display = 'none';
        document.getElementById('checkout-step-2').style.display = 'block';
    }

    function backToStep1() {
        document.getElementById('checkout-step-2').style.display = 'none';
        document.getElementById('checkout-step-1').style.display = 'block';
    }

    async function payOrder() {
        const btn = document.getElementById('checkout-pay-btn');
        btn.disabled = true;
        btn.textContent = '⏳ Обработка...';

        // Сначала создаём заказ
        const formData = new URLSearchParams();
        formData.append('type', currentType);
        formData.append('address', document.getElementById('checkout-address').value.trim());
        formData.append('booking_date', document.getElementById('checkout-date').value);
        formData.append('booking_time', document.getElementById('checkout-time').value);
        formData.append('guests', document.getElementById('checkout-guests').value);
        formData.append('comment', document.getElementById('checkout-comment').value);

        try {
            const resp = await fetch('../backend/checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
            const result = await resp.json();

            if (!result.success) {
                alert('❌ ' + result.error);
                btn.disabled = false;
                btn.textContent = '💳 Оплатить';
                return;
            }

            currentOrderId = result.order_id;

            // Фейковая оплата
            const payResp = await fetch('../backend/pay.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=' + currentOrderId
            });
            const payResult = await payResp.json();

            if (payResult.success) {
                // Показываем успех
                document.getElementById('checkout-step-2').style.display = 'none';
                document.getElementById('checkout-step-3').style.display = 'block';
                document.getElementById('checkout-order-number').textContent = '#' + currentOrderId;

                const typeMsgs = {
                    delivery: '🚚 Заказ будет доставлен по указанному адресу',
                    pickup: '🚶 Заказ можно забрать через 30-40 минут',
                    booking: '🍽️ Столик забронирован, заказ подадут к столу'
                };
                document.getElementById('checkout-success-msg').textContent = typeMsgs[currentType] || '✅ Заказ принят';

                // Обновляем счётчик корзины
                document.getElementById('cart-count').textContent = '0';
            } else {
                alert('❌ Ошибка оплаты: ' + payResult.error);
                btn.disabled = false;
                btn.textContent = '💳 Оплатить';
            }
        } catch (e) {
            alert('❌ Ошибка соединения');
            btn.disabled = false;
            btn.textContent = '💳 Оплатить';
        }
    }

    // Открытие модалки
    document.getElementById('checkout-btn')?.addEventListener('click', openCheckoutModal);

    // Обновление количества
    document.querySelectorAll('.qty-plus, .qty-minus').forEach(btn => {
        btn.addEventListener('click', async function() {
            const itemId = this.dataset.cartId;
            const delta = this.classList.contains('qty-plus') ? 1 : -1;
            const row = this.closest('.cart-row');
            const qtySpan = row.querySelector('.qty-value');
            let newQty = parseInt(qtySpan.textContent) + delta;
            if (newQty < 1) newQty = 1;

            const resp = await fetch('../backend/cart_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}&quantity=${newQty}`
            });
            const result = await resp.json();
            if (result.success) {
                qtySpan.textContent = newQty;
                row.querySelector('.cart-col-total').textContent = result.item_total + ' ₽';
                document.getElementById('cart-total-amount').textContent = result.cart_total + ' ₽';
                document.getElementById('cart-count').textContent = result.cart_count;
            }
        });
    });

    // Удаление из корзины
    document.querySelectorAll('.cart-remove').forEach(btn => {
        btn.addEventListener('click', async function() {
            const itemId = this.dataset.cartId;
            const resp = await fetch('../backend/cart_remove.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}`
            });
            const result = await resp.json();
            if (result.success) {
                this.closest('.cart-row').remove();
                document.getElementById('cart-total-amount').textContent = result.cart_total + ' ₽';
                document.getElementById('cart-count').textContent = result.cart_count;
                if (result.cart_count === 0) {
                    location.reload();
                }
            }
        });
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
