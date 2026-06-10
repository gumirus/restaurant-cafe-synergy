// =============================================
// MAIN JS — Bean Scene Restaurant
// Анимации, счётчики, загрузка данных
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🍽 Bean Scene — загружен');

    initHeaderScroll();
    initFadeIn();
    initCounters();
    loadSpecials();
    loadPopularDishes();
    loadTeam();
    loadReviews();
    loadGallery();
    loadNews();
    initBookingForm();
    initNewsletterForm();
    initCart();
    updateCartCount();
    initImageModal();
});

// ========== HEADER SCROLL ==========
function initHeaderScroll() {
    const header = document.getElementById('header');
    if (!header) return;

    window.addEventListener('scroll', function() {
        if (window.scrollY > 80) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

// ========== FADE-IN ANIMATION ==========
function initFadeIn() {
    const elements = document.querySelectorAll('.fade-in');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    });

    elements.forEach(el => observer.observe(el));
}

// ========== COUNTER ANIMATION ==========
function initCounters() {
    const counters = document.querySelectorAll('.stat-number');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target);
                animateCounter(counter, target);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => observer.observe(c));
}

function animateCounter(element, target) {
    let current = 0;
    const increment = Math.ceil(target / 60);
    const stepTime = Math.floor(2000 / 60);

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = current + (target === 98 ? '%' : '+');
    }, stepTime);
}

// ========== LOAD SPECIAL DISHES (Chef's Picks) ==========
function loadSpecials() {
    const container = document.getElementById('specials-grid');
    if (!container) return;

    const specials = [
        { name: 'Стейк Рибай', desc: 'Мраморная говядина с трюфельным соусом', price: 1200, badge: 'Шеф-рекомендует', img: 'uploads/dishes/5-steak.jpg' },
        { name: 'Тирамису', desc: 'Итальянский десерт с маскарпоне', price: 350, badge: 'Фирменный', img: 'uploads/dishes/7-tiramisu.jpg' },
        { name: 'Том Ям', desc: 'Острый тайский суп с креветками', price: 550, badge: 'Хит сезона', img: 'uploads/dishes/3-tom-yam.jpg' },
    ];

    container.innerHTML = specials.map(s => `
        <div class="special-card fade-in">
            <img class="special-card-image clickable-img" src="${s.img}" alt="${s.name}">
            <div class="special-card-body">
                <span class="special-badge">${s.badge}</span>
                <h3>${s.name}</h3>
                <p>${s.desc}</p>
                <span class="price">${s.price} ₽</span>
            </div>
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== LOAD POPULAR DISHES ==========
function loadPopularDishes() {
    const container = document.getElementById('popular-dishes');
    if (!container) return;

    const dishes = [
        { id: 1, name: 'Цезарь с курицей', price: 450, category: 'salads', desc: 'Классический салат с пармезаном', img: 'uploads/dishes/1-caesar.jpg' },
        { id: 2, name: 'Греческий салат', price: 380, category: 'salads', desc: 'Свежие овощи с сыром фета', img: 'uploads/dishes/2-greek.jpg' },
        { id: 3, name: 'Том Ям', price: 550, category: 'soups', desc: 'Острый тайский суп с креветками', img: 'uploads/dishes/3-tom-yam.jpg' },
        { id: 4, name: 'Борщ', price: 320, category: 'soups', desc: 'Традиционный русский суп', img: 'uploads/dishes/4-borscht.jpg' },
        { id: 5, name: 'Стейк Рибай', price: 1200, category: 'main', desc: 'Мраморная говядина с трюфелем', img: 'uploads/dishes/5-steak.jpg' },
        { id: 6, name: 'Паста Карбонара', price: 480, category: 'main', desc: 'Итальянская паста с беконом', img: 'uploads/dishes/6-carbonara.jpg' },
        { id: 7, name: 'Тирамису', price: 350, category: 'desserts', desc: 'Итальянский десерт с маскарпоне', img: 'uploads/dishes/7-tiramisu.jpg' },
        { id: 8, name: 'Чизкейк', price: 320, category: 'desserts', desc: 'Нью-йоркский чизкейк', img: 'uploads/dishes/8-cheesecake.jpg' },
        { id: 9, name: 'Лимонад', price: 180, category: 'drinks', desc: 'Домашний лимонад', img: 'uploads/dishes/9-lemonade.jpg' },
        { id: 10, name: 'Кофе', price: 200, category: 'drinks', desc: 'Эспрессо/Капучино/Латте', img: 'uploads/dishes/10-coffee.jpg' },
    ];

    container.innerHTML = dishes.map(dish => `
        <div class="dish-card fade-in">
            <img class="dish-card-image clickable-img" src="${dish.img}" alt="${dish.name}">
            <div class="dish-card-body">
                <h3>${dish.name}</h3>
                <p>${dish.desc}</p>
                <div class="dish-card-footer">
                    <span class="price">${dish.price} ₽</span>
                    <button class="btn add-to-cart" data-id="${dish.id}">В корзину</button>
                </div>
            </div>
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== LOAD TEAM ==========
function loadTeam() {
    const container = document.getElementById('team-grid');
    if (!container) return;

    const team = [
        { name: 'Антонио Бьянки', role: 'Шеф-повар', desc: '15 лет опыта в ресторанах Мишлен', img: 'uploads/team/1-chef.jpg' },
        { name: 'Анастасия Костюренко', role: 'Су-шеф', desc: 'Специалист по итальянской кухне', img: 'uploads/team/2-sous-chef.jpg' },
        { name: 'Дмитрий Волков', role: 'Кондитер', desc: 'Автор уникальных десертов', img: 'uploads/team/3-pastry.jpg' },
        { name: 'Елена Преображенская', role: 'Сомелье', desc: 'Эксперт по винным сочетаниям', img: 'uploads/team/4-sommelier.jpg' },
    ];

    container.innerHTML = team.map(m => `
        <div class="team-card fade-in">
            <img class="team-card-image clickable-img" src="${m.img}" alt="${m.name}">
            <div class="team-card-body">
                <h3>${m.name}</h3>
                <div class="team-role">${m.role}</div>
                <p>${m.desc}</p>
            </div>
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== LOAD REVIEWS ==========
function loadReviews() {
    const container = document.getElementById('reviews-grid');
    if (!container) return;

    const reviews = [
        { name: 'Анна', text: 'Потрясающее место! Обслуживание на высшем уровне, а стейк — просто божественный. Обязательно вернусь!', rating: 5 },
        { name: 'Иван', text: 'Отличный ресторан для ужина с семьёй. Уютная атмосфера, внимательный персонал, вкусная еда.', rating: 4 },
        { name: 'Мария', text: 'Лучший ресторан в городе! Каждое блюдо — шедевр. Особенно рекомендую десерты.', rating: 5 },
    ];

    container.innerHTML = reviews.map(r => `
        <div class="review-card fade-in">
            <div class="quote">"</div>
            <p>${r.text}</p>
            <div class="reviewer-name">— ${r.name}</div>
            <div class="rating">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== LOAD GALLERY ==========
function loadGallery() {
    const container = document.getElementById('gallery-grid');
    if (!container) return;

    const gallery = [
        { label: 'Интерьер зала', img: 'uploads/gallery/1-interior.jpg' },
        { label: 'Авторские блюда', img: 'uploads/gallery/2-dishes.jpg' },
        { label: 'Винная карта', img: 'uploads/gallery/3-wine.jpg' },
        { label: 'Летняя веранда', img: 'uploads/gallery/4-terrace.jpg' },
        { label: 'Кухня', img: 'uploads/gallery/5-kitchen.jpg' },
        { label: 'Десерты', img: 'uploads/gallery/6-desserts.jpg' },
        { label: 'Авторский десерт', img: 'uploads/gallery/7-dessert.jpg' },
    ];

    container.innerHTML = gallery.map(g => `
        <div class="gallery-item fade-in clickable-img" data-src="${g.img}" data-alt="${g.label}">
            <img src="${g.img}" alt="${g.label}">
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== LOAD NEWS ==========
function loadNews() {
    const container = document.getElementById('news-grid');
    if (!container) return;

    const news = [
        { title: 'Новое сезонное меню', date: '01.06.2026', desc: 'Попробуйте наши новые летние блюда из свежих сезонных продуктов', img: 'uploads/news/1-seasonal-menu.jpg' },
        { title: 'Скидка 20% на первый заказ', date: '28.05.2026', desc: 'Для новых клиентов скидка на первый заказ через сайт', img: 'uploads/news/2-discount.jpg' },
        { title: 'Мастер-класс от шеф-повара', date: '20.05.2026', desc: 'Научитесь готовить фирменные блюда нашего ресторана', img: 'uploads/news/3-masterclass.jpg' },
    ];

    container.innerHTML = news.map(n => `
        <div class="news-card fade-in">
            <img class="news-card-image clickable-img" src="${n.img}" alt="${n.title}">
            <div class="news-card-body">
                <span class="news-date">${n.date}</span>
                <h3>${n.title}</h3>
                <p>${n.desc}</p>
            </div>
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== BOOKING FORM ==========
function initBookingForm() {
    const form = document.getElementById('booking-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('✅ Спасибо! Мы свяжемся с вами для подтверждения бронирования.');
        form.reset();
    });
}

// ========== CART ==========
function initCart() {
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.add-to-cart');
        if (!btn) return;

        const dishId = btn.dataset.id;
        if (!dishId) return;

        btn.textContent = '...';
        btn.disabled = true;

        try {
            const resp = await fetch('../backend/cart_add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'dish_id=' + dishId
            });
            const result = await resp.json();
            if (result.success) {
                btn.textContent = '✅ В корзине';
                updateCartCount();
                // Показываем уведомление
                showToast('✅ Добавлено в корзину');
                // Диспатчим событие для menu.php и других страниц
                document.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: result.cart_count } }));
            } else {
                btn.textContent = '❌ Ошибка';
                if (result.error === 'Необходимо авторизоваться') {
                    setTimeout(() => { window.location.href = 'login.php'; }, 1000);
                }
            }
        } catch (err) {
            btn.textContent = '❌ Ошибка';
        }

        setTimeout(() => {
            btn.textContent = 'В корзину';
            btn.disabled = false;
        }, 2000);
    });
}

function updateCartCount() {
    const badge = document.getElementById('cart-count');
    if (!badge) return;

    // Получаем количество из куки или через fetch
    fetch('../backend/cart_count.php')
        .then(r => r.json())
        .then(data => {
            badge.textContent = data.count;
        })
        .catch(() => {});
}

// ========== NEWSLETTER FORM ==========
function initNewsletterForm() {
    const form = document.getElementById('newsletter-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('✅ Вы подписались на новости! Спасибо.');
        form.reset();
    });
}

// ========== IMAGE MODAL (click to enlarge) ==========
function initImageModal() {
    // Удаляем старый модал, если есть
    const oldModal = document.getElementById('img-modal');
    if (oldModal) oldModal.remove();

    const modal = document.createElement('div');
    modal.id = 'img-modal';
    modal.className = 'img-modal-overlay';
    modal.innerHTML = '<button class="img-modal-close">&times;</button><img class="img-modal-content" id="img-modal-img" src="" alt="">';
    document.body.appendChild(modal);

    const img = document.getElementById('img-modal-img');
    const close = modal.querySelector('.img-modal-close');

    // Открытие по клику на .clickable-img
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.clickable-img');
        if (!target) return;
        // Если клик по картинке внутри модалки — закрываем
        if (target.closest('.img-modal-overlay')) {
            closeModal();
            return;
        }
        e.preventDefault();
        // Поддержка data-src (для div-контейнеров) и src (для img)
        img.src = target.dataset.src || target.src;
        img.alt = target.dataset.alt || target.alt;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    close.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === this || e.target === img) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
}

// ========== TOAST NOTIFICATION ==========
function showToast(msg) {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = msg;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '30px', right: '30px',
        background: '#1a1a2e', color: '#fff',
        padding: '16px 28px', borderRadius: '12px',
        fontSize: '1rem', zIndex: '100000',
        boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
        opacity: '0', transform: 'translateY(20px)',
        transition: 'opacity 0.3s, transform 0.3s'
    });
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}
