<?php require_once __DIR__ . '/header.php'; ?>

    <!-- ========== PAGE HERO ========== -->
    <section class="page-hero">
        <div class="container">
            <h1>Наше меню</h1>
            <p>Изысканные блюда на любой вкус</p>
        </div>
    </section>

    <!-- ========== MENU ========== -->
    <section class="menu-section">
        <div class="container">
            <div class="menu-filters">
                <button class="filter-btn active" data-category="all">Все</button>
                <button class="filter-btn" data-category="salads">Салаты</button>
                <button class="filter-btn" data-category="soups">Супы</button>
                <button class="filter-btn" data-category="main">Горячие блюда</button>
                <button class="filter-btn" data-category="desserts">Десерты</button>
                <button class="filter-btn" data-category="drinks">Напитки</button>
            </div>
            <div class="dishes-grid" id="menu-dishes">
                <!-- Карточки блюд через JS -->
            </div>
        </div>
    </section>

    <style>
    .page-hero {
        padding: 140px 0 60px;
        background: linear-gradient(135deg, var(--color-bg-dark) 0%, #1a0f0a 100%);
        text-align: center;
    }
    .page-hero h1 {
        font-size: 2.8rem;
        color: var(--color-text-white);
        margin-bottom: 10px;
    }
    .page-hero p {
        color: var(--color-text-light);
        font-size: 1.1rem;
    }
    .menu-section { padding: 60px 0; }
    .menu-filters {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 50px;
    }
    .filter-btn {
        padding: 12px 28px;
        border: 1px solid var(--color-border);
        border-radius: 30px;
        background: transparent;
        color: var(--color-text);
        cursor: pointer;
        font-size: 0.95rem;
        transition: all 0.3s;
    }
    .filter-btn:hover,
    .filter-btn.active {
        background: var(--color-primary);
        color: #fff;
        border-color: var(--color-primary);
    }
    .dishes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    .dish-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .dish-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .dish-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .dish-card-body { padding: 20px; }
    .dish-card-body h3 {
        color: var(--color-text-white);
        margin-bottom: 8px;
        font-size: 1.2rem;
    }
    .dish-card-body p {
        color: var(--color-text-light);
        font-size: 0.9rem;
        margin-bottom: 12px;
    }
    .dish-card-body .price {
        color: var(--color-primary);
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    .dish-card-body .btn {
        width: 100%;
        text-align: center;
    }
    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: var(--color-primary);
        color: #fff;
        padding: 15px 25px;
        border-radius: 10px;
        font-weight: 500;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { transform: translateX(100px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const dishesGrid = document.getElementById('menu-dishes');
        if (!dishesGrid) return;

        // Данные меню
        const menuItems = [
            { id: 1, name: 'Цезарь с курицей', price: 450, category: 'salads', desc: 'Классический салат с курицей', img: 'uploads/dishes/1-caesar.jpg' },
            { id: 2, name: 'Греческий салат', price: 380, category: 'salads', desc: 'Свежие овощи с сыром фета', img: 'uploads/dishes/2-greek.jpg' },
            { id: 3, name: 'Том Ям', price: 550, category: 'soups', desc: 'Острый тайский суп', img: 'uploads/dishes/3-tom-yam.jpg' },
            { id: 4, name: 'Борщ', price: 320, category: 'soups', desc: 'Традиционный русский суп', img: 'uploads/dishes/4-borscht.jpg' },
            { id: 5, name: 'Стейк Рибай', price: 1200, category: 'main', desc: 'Мраморная говядина', img: 'uploads/dishes/5-steak.jpg' },
            { id: 6, name: 'Паста Карбонара', price: 480, category: 'main', desc: 'Итальянская паста', img: 'uploads/dishes/6-carbonara.jpg' },
            { id: 7, name: 'Тирамису', price: 350, category: 'desserts', desc: 'Итальянский десерт', img: 'uploads/dishes/7-tiramisu.jpg' },
            { id: 8, name: 'Чизкейк', price: 320, category: 'desserts', desc: 'Нью-йоркский чизкейк', img: 'uploads/dishes/8-cheesecake.jpg' },
            { id: 9, name: 'Лимонад', price: 180, category: 'drinks', desc: 'Домашний лимонад', img: 'uploads/dishes/9-lemonade.jpg' },
            { id: 10, name: 'Кофе', price: 200, category: 'drinks', desc: 'Эспрессо/Капучино/Латте', img: 'uploads/dishes/10-coffee.jpg' },
        ];

        // Отобразить все блюда
        function renderDishes(category = 'all') {
            const filtered = category === 'all' 
                ? menuItems 
                : menuItems.filter(item => item.category === category);

            dishesGrid.innerHTML = filtered.map(item => `
                <div class="dish-card">
                    <img src="${item.img}" alt="${item.name}">
                    <div class="dish-card-body">
                        <h3>${item.name}</h3>
                        <p>${item.desc}</p>
                        <p class="price">${item.price} ₽</p>
                        <button class="btn add-to-cart" data-id="${item.id}">В корзину</button>
                    </div>
                </div>
            `).join('');

            // Навешиваем обработчики на кнопки "В корзину"
            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', function() {
                    const dishId = this.dataset.id;
                    const dish = menuItems.find(d => d.id == dishId);
                    if (!dish) return;

                    fetch('../backend/cart_add.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'dish_id=' + dishId
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Обновляем счётчик корзины
                            const cartCount = document.getElementById('cart-count');
                            if (cartCount) cartCount.textContent = data.cart_count;
                            // Показываем уведомление
                            showToast('✅ ' + dish.name + ' добавлен в корзину');
                        } else {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                showToast('❌ ' + (data.error || 'Ошибка'));
                            }
                        }
                    })
                    .catch(() => showToast('❌ Ошибка соединения'));
                });
            });
        }

        // Уведомление
        function showToast(msg) {
            const existing = document.querySelector('.toast');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        }

        // Фильтрация
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderDishes(this.dataset.category);
            });
        });

        // Начальная загрузка
        renderDishes('all');
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
