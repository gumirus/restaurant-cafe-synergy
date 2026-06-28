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
            <button class="menu-filters-toggle" id="menuFiltersToggle" onclick="toggleMenuFilters()">📋 Категории ▾</button>
            <div class="menu-filters" id="menuFilters">
                <button class="filter-btn active" data-category="all">Все</button>
                <button class="filter-btn" data-category="salads">Салаты</button>
                <button class="filter-btn" data-category="soups">Супы</button>
                <button class="filter-btn" data-category="main">Горячие блюда</button>
                <button class="filter-btn" data-category="sushi">Холодные блюда</button>
                <button class="filter-btn" data-category="desserts">Десерты</button>
                <button class="filter-btn" data-category="drinks">Напитки</button>
            </div>
            <div class="dishes-grid" id="menu-dishes">
                <!-- Карточки блюд через JS -->
            </div>
        </div>
    </section>

    <script>
    function toggleMenuFilters() {
        var f = document.getElementById('menuFilters');
        var b = document.getElementById('menuFiltersToggle');
        if (f) f.classList.toggle('open');
        if (b) b.textContent = f && f.classList.contains('open') ? '📋 Категории ▴' : '📋 Категории ▾';
    }
    document.addEventListener('DOMContentLoaded', function() {
        var filters = document.getElementById('menuFilters');
        var toggle = document.getElementById('menuFiltersToggle');
        if (!filters || !toggle) return;
        
        // Restore category from URL on page load
        var params = new URLSearchParams(window.location.search);
        var saved = params.get('cat');
        if (saved) {
            var btn = filters.querySelector('.filter-btn[data-category="' + saved + '"]');
            if (btn) {
                btn.click();
                var name = btn.textContent.trim();
                if (name !== 'Все') toggle.textContent = '📋 ' + name + ' ▾';
            }
        }
        
        filters.addEventListener('click', function(e) {
            var btn = e.target.closest('.filter-btn');
            if (!btn) return;
            var name = btn.textContent.trim();
            var cat = btn.dataset.category;
            // Save to URL without page reload
            var url = new URL(window.location);
            if (cat === 'all') url.searchParams.delete('cat');
            else url.searchParams.set('cat', cat);
            window.history.replaceState({}, '', url);
            // Update toggle text
            if (name === 'Все') {
                toggle.textContent = '📋 Категории ▾';
            } else {
                toggle.textContent = '📋 ' + name + ' ▾';
            }
            filters.classList.remove('open');
            toggle.textContent = toggle.textContent.replace('▴', '▾');
        });
    });
    </script>
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
        display: flex;
        flex-direction: column;
    }
    .dish-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .dish-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 16px 16px 0 0;
    }
    .dish-card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .dish-card-body h3 {
        color: var(--color-text);
        margin-bottom: 8px;
        font-size: 1.2rem;
    }
    .dish-card-body p {
        color: var(--color-text-light);
        font-size: 0.9rem;
        margin-bottom: 12px;
        flex: 1;
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
    .dish-card-body .btn:last-child {
        margin-top: auto;
    }
    .dish-card-actions {
        margin-top: auto;
        display: flex;
        gap: 8px;
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

    <!-- Подключаем данные меню из БД -->
    <?php
    require_once __DIR__ . '/../backend/config/db.php';

    // Маппинг названий категорий из БД в data-category для фильтров
    $catMap = [
        'Салаты' => 'salads',
        'Супы' => 'soups',
        'Горячие блюда' => 'main',
        'Холодные блюда' => 'sushi',
        'Десерты' => 'desserts',
        'Напитки' => 'drinks',
    ];

    // Получаем все блюда с категориями
    $stmt = $pdo->query("
        SELECT d.id, d.name, d.description, d.price, d.weight, d.image, d.ingredients,
               c.name as category_name
        FROM dishes d
        JOIN categories c ON d.category_id = c.id
        ORDER BY d.name
    ");
    $dishes = $stmt->fetchAll();
    ?>

    <!-- Модальное окно деталей блюда -->
    <div id="dish-modal" class="dish-modal-overlay" onclick="if(event.target===this)closeDishModal()">
        <div class="dish-modal-content">
            <button class="dish-modal-close" onclick="closeDishModal()">&times;</button>
            <div class="dish-modal-layout">
                <div class="dish-modal-image">
                    <div class="dish-modal-img-wrap">
                        <img src="" alt="" id="dish-modal-img">
                        <div class="dish-modal-img-zoom">🔍</div>
                    </div>
                </div>
                <div class="dish-modal-info">
                    <h2 id="dish-modal-name"></h2>
                    <div class="dish-modal-meta">
                        <span class="dish-modal-weight" id="dish-modal-weight"></span>
                        <span class="dish-modal-price" id="dish-modal-price"></span>
                    </div>
                    <div class="dish-modal-desc" id="dish-modal-desc"></div>
                    <div class="dish-modal-ingredients">
                        <h4>🧂 Состав:</h4>
                        <p id="dish-modal-ingredients"></p>
                    </div>
                    <button class="btn dish-modal-cart-btn" id="dish-modal-cart-btn">🛒 Добавить в корзину</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .dish-modal-overlay {
        position: fixed; inset: 0; z-index: 99999;
        background: rgba(0,0,0,0.7);
        display: flex; align-items: center; justify-content: center;
        visibility: hidden; opacity: 0;
        transition: all 0.3s ease;
    }
    .dish-modal-overlay.active {
        visibility: visible; opacity: 1;
    }
    .dish-modal-content {
        background: var(--color-bg-section);
        border-radius: 20px;
        max-width: 700px; width: 92%;
        position: relative;
        box-shadow: 0 30px 80px rgba(0,0,0,0.5);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        overflow: hidden;
    }
    .dish-modal-overlay.active .dish-modal-content {
        transform: scale(1) translateY(0);
    }
    .dish-modal-close {
        position: absolute; top: 12px; right: 18px;
        background: rgba(0,0,0,0.3); border: none;
        font-size: 1.8rem; cursor: pointer;
        color: #fff;
        line-height: 1; width: 36px; height: 36px;
        border-radius: 50%; display: flex;
        align-items: center; justify-content: center;
        z-index: 2; transition: background 0.2s;
    }
    .dish-modal-close:hover { background: rgba(0,0,0,0.6); }
    .dish-modal-layout {
        display: flex; flex-wrap: wrap;
    }
    .dish-modal-image {
        flex: 0 0 300px; max-width: 300px;
        overflow: hidden;
    }
    .dish-modal-img-wrap {
        position: relative;
        width: 100%; height: 100%;
    }
    .dish-modal-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        display: block; min-height: 280px;
        cursor: zoom-in;
        border-radius: 16px 0 0 16px;
    }
    .dish-modal-img-zoom {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
        background: rgba(0,0,0,0.15);
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    }
    .dish-modal-img-wrap:hover .dish-modal-img-zoom {
        opacity: 1;
    }
    .dish-modal-info {
        flex: 1; padding: 30px;
        min-width: 250px;
    }
    .dish-modal-info h2 {
        font-size: 1.6rem;
        color: var(--color-text);
        margin-bottom: 12px;
    }
    .dish-modal-meta {
        display: flex; gap: 15px; align-items: center;
        margin-bottom: 15px;
    }
    .dish-modal-weight {
        background: rgba(212, 168, 83, 0.15);
        color: var(--color-primary);
        padding: 4px 12px; border-radius: 20px;
        font-size: 0.85rem; font-weight: 600;
    }
    .dish-modal-price {
        font-size: 1.5rem; font-weight: 700;
        color: var(--color-primary);
    }
    .dish-modal-desc {
        color: var(--color-text-light);
        font-size: 0.95rem; line-height: 1.7;
        margin-bottom: 18px;
    }
    .dish-modal-ingredients {
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: 12px; padding: 16px;
        margin-bottom: 20px;
    }
    .dish-modal-ingredients h4 {
        color: var(--color-text);
        font-size: 0.9rem; margin-bottom: 8px;
    }
    .dish-modal-ingredients p {
        color: var(--color-text-light);
        font-size: 0.85rem; line-height: 1.6;
        margin: 0;
    }
    .dish-modal-cart-btn {
        width: 100%; padding: 14px;
        font-size: 1rem;
    }
    @media (max-width: 600px) {
        .dish-modal-image { flex: 0 0 100%; max-width: 100%; }
        .dish-modal-image img { min-height: 200px; max-height: 220px; border-radius: 16px 16px 0 0; }
        .dish-modal-info { padding: 20px; }
    }
    </style>

    <!-- Оверлей для увеличенного фото (открывается только из модалки) -->
    <div id="dish-img-overlay" class="dish-img-overlay" onclick="this.classList.remove('active')">
        <button class="dish-img-close" onclick="event.stopPropagation();document.getElementById('dish-img-overlay').classList.remove('active')">&times;</button>
        <img src="" alt="" id="dish-img-full">
    </div>

    <style>
    .dish-img-overlay {
        position: fixed; inset: 0; z-index: 999999;
        background: rgba(0,0,0,0.85);
        display: flex; align-items: center; justify-content: center;
        visibility: hidden; opacity: 0;
        transition: all 0.3s ease;
        cursor: zoom-out;
    }
    .dish-img-overlay.active {
        visibility: visible; opacity: 1;
    }
    .dish-img-overlay img {
        width: 45vw;
        height: 45vh;
        object-fit: contain;
        transition: transform 0.3s ease;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .dish-img-close {
        position: fixed; top: 20px; right: 25px;
        background: rgba(255,255,255,0.15);
        border: none; font-size: 2.2rem;
        cursor: pointer; color: #fff;
        width: 44px; height: 44px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        z-index: 10; transition: background 0.2s;
    }
    .dish-img-close:hover { background: rgba(255,255,255,0.3); }
    @media (max-width: 600px) {
        .dish-img-overlay img {
            width: 90vw;
            height: 60vh;
        }
    }

    /* Mobile dish cards with rounded images */
    @media (max-width: 768px) {
        .dish-card img {
            border-radius: 12px 12px 0 0;
            height: 180px;
        }
        .dish-card-body { padding: 14px; }
        .dish-card-body h3 { font-size: 1rem; }
        .dish-card-body p { font-size: 0.8rem; }
    }
    @media (max-width: 480px) {
        .dish-card img {
            border-radius: 12px 12px 0 0;
            height: 160px;
        }
    }
    </style>

    <script>
    // Данные блюд (глобально)
    const dishData = <?= json_encode(array_map(function($d) use ($catMap) {
        return [
            'id' => (int)$d['id'],
            'name' => $d['name'],
            'price' => (int)$d['price'],
            'weight' => (int)$d['weight'],
            'desc' => $d['description'] ?? '',
            'ingredients' => $d['ingredients'] ?? '',
            'category' => $catMap[$d['category_name']] ?? 'main',
            'img' => $d['image'] ? $d['image'] : 'uploads/dishes/placeholder.jpg',
        ];
    }, $dishes), JSON_UNESCAPED_UNICODE) ?>;

    function showToast(msg) {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    // ====== Добавление в корзину (универсальная функция) ======
    function addToCart(id, btn) {
        if (!id) { showToast('⚠️ Ошибка: ID блюда не найден'); return; }
        const item = dishData.find(d => d.id === id);
        if (!item) { showToast('⚠️ Ошибка: блюдо не найдено'); return; }
        
        fetch('../backend/cart_add.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'dish_id=' + id + '&quantity=1'
        })
        .then(r => r.json())
        .then(data => {
                if (data.success) {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) cartCount.textContent = data.cart_count;
                showToast('✅ ' + item.name + ' добавлен в корзину');
            } else {
                showToast('⚠️ ' + (data.error || 'Ошибка'));
            }
        })
        .catch(() => showToast('⚠️ Ошибка соединения'));
    }

    // ====== Модальное окно деталей блюда ======
    function openDishModal(id) {
        const item = dishData.find(d => d.id === id);
        if (!item) return;
        document.getElementById('dish-modal-img').src = item.img;
        document.getElementById('dish-modal-name').textContent = item.name;
        document.getElementById('dish-modal-price').textContent = item.price + ' ₽';
        document.getElementById('dish-modal-weight').textContent = item.weight ? item.weight + ' г' : 'Вес не указан';
        document.getElementById('dish-modal-desc').textContent = item.desc || 'Описание отсутствует';
        document.getElementById('dish-modal-ingredients').textContent = item.ingredients || 'Состав не указан';
        document.getElementById('dish-modal-cart-btn').dataset.id = item.id;
        document.getElementById('dish-modal').classList.add('active');
    }

    function closeDishModal() {
        document.getElementById('dish-modal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('dish-img-overlay');
            if (overlay.classList.contains('active')) {
                overlay.classList.remove('active');
            } else {
                closeDishModal();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // ====== Кнопка в корзину из модального окна ======
        document.getElementById('dish-modal-cart-btn').addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            addToCart(id);
            closeDishModal();
        });

        // ====== Увеличение фото при клике внутри модалки ======
        document.getElementById('dish-modal-img').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('dish-img-full').src = this.src;
            document.getElementById('dish-img-overlay').classList.add('active');
        });

        // ====== Фильтрация и рендер ======
        const filterBtns = document.querySelectorAll('.filter-btn');
        const dishesGrid = document.getElementById('menu-dishes');
        if (!dishesGrid) return;

        const menuItems = <?= json_encode(array_map(function($d) use ($catMap) {
            $catKey = $catMap[$d['category_name']] ?? 'other';
            return [
                'id' => (int)$d['id'],
                'name' => $d['name'],
                'price' => (int)$d['price'],
                'weight' => (int)$d['weight'],
                'categories' => [$catKey],
                'desc' => $d['description'] ?? '',
                'ingredients' => $d['ingredients'] ?? '',
                'img' => $d['image'] ? $d['image'] : 'uploads/dishes/placeholder.jpg',
            ];
        }, $dishes)) ?>;

        function renderDishes(category = 'all') {
            const filtered = category === 'all' 
                ? menuItems 
                : menuItems.filter(item => item.categories.includes(category));

            dishesGrid.innerHTML = filtered.map(item => `
                <div class="dish-card">
                    <img src="${item.img}" alt="${item.name}" onclick="openDishModal(${item.id})">
                    <div class="dish-card-body">
                        <h3>${item.name}</h3>
                        <p>${item.desc}</p>
                        <p class="price">${item.price} ₽</p>
                        <div class="dish-card-actions">
                            <button class="btn" style="flex:1;" onclick="openDishModal(${item.id})">📋 Подробнее</button>
                            <button class="btn" style="flex:1; background:var(--color-primary);" onclick="addToCart(${item.id}, this)">🛒 В корзину</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderDishes(this.dataset.category);
            });
        });

        renderDishes('all');
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
