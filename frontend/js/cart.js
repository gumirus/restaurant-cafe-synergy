// ========== CART (Корзина) ==========
let cart = JSON.parse(localStorage.getItem('cart')) || [];

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    renderCart();
});

// Добавление в корзину
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;

    const id = parseInt(btn.dataset.id);
    const card = btn.closest('.dish-card');
    const name = card.querySelector('h3').textContent;
    const priceText = card.querySelector('.price').textContent;
    const price = parseInt(priceText.replace(/[^\d]/g, ''));

    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.count++;
    } else {
        cart.push({ id, name, price, count: 1 });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    alert('Товар добавлен в корзину!');
});

// Обновление счётчика корзины
function updateCartCount() {
    const total = cart.reduce((sum, item) => sum + item.count, 0);
    document.querySelectorAll('.cart-link').forEach(el => {
        el.textContent = `Корзина (${total})`;
    });
}

// Отображение корзины
function renderCart() {
    const container = document.getElementById('cart-items');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p>Корзина пуста</p>';
        document.getElementById('cart-total-price').textContent = '0';
        return;
    }

    container.innerHTML = cart.map(item => `
        <div class="cart-item">
            <span>${item.name}</span>
            <span>${item.price} ₽ × ${item.count}</span>
            <span>${item.price * item.count} ₽</span>
            <button class="btn remove-item" data-id="${item.id}">Удалить</button>
        </div>
    `).join('');

    const total = cart.reduce((sum, item) => sum + item.price * item.count, 0);
    document.getElementById('cart-total-price').textContent = total;
}

// Удаление из корзины
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.remove-item');
    if (!btn) return;

    const id = parseInt(btn.dataset.id);
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    renderCart();
});
