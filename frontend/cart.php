<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../backend/config/db.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем корзину пользователя
$stmt = $pdo->prepare("
    SELECT c.id, c.count as quantity, d.id as dish_id, d.name, d.price, d.image
    FROM shopping_cart c
    JOIN dishes d ON c.dish_id = d.id
    WHERE c.user_id = ?
    ORDER BY c.id DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
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
                    <button class="btn" id="checkout-btn">Оформить заказ</button>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
    }
    .cart-total {
        font-size: 1.3rem;
        color: var(--color-text);
    }
    .cart-total strong {
        color: var(--color-primary);
        font-size: 1.5rem;
    }
    </style>

    <script>
    // Обновление количества
    document.querySelectorAll('.qty-plus, .qty-minus').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cartId = this.dataset.cartId;
            const delta = this.classList.contains('qty-plus') ? 1 : -1;
            const row = this.closest('.cart-row');
            const qtySpan = row.querySelector('.qty-value');
            let newQty = parseInt(qtySpan.textContent) + delta;
            if (newQty < 1) newQty = 1;

            const resp = await fetch('../backend/cart_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cart_id=${cartId}&quantity=${newQty}`
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
            const cartId = this.dataset.cartId;
            const resp = await fetch('../backend/cart_remove.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cart_id=${cartId}`
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

    // Оформление заказа
    document.getElementById('checkout-btn')?.addEventListener('click', async function() {
        const resp = await fetch('../backend/checkout.php', { method: 'POST' });
        const result = await resp.json();
        if (result.success) {
            alert('✅ Заказ оформлен! Номер заказа: ' + result.order_id);
            location.reload();
        } else {
            alert('❌ ' + result.error);
        }
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
