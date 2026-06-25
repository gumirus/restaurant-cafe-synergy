// =============================================
// MAIN JS — Точка Кипения Restaurant
// Анимации, счётчики, загрузка данных
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🍽 Точка Кипения — загружен');

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

// ========== LOAD SPECIAL DISHES (Chef's Picks — из БД) ==========
function loadSpecials() {
    const container = document.getElementById('specials-grid');
    if (!container) return;

    fetch('../backend/getSpecialDishes.php')
        .then(r => r.json())
        .then(dishes => {
            if (!dishes || dishes.length === 0) {
                container.innerHTML = '<p style="text-align:center;color:var(--color-text-light);grid-column:1/-1;">Фирменные блюда пока не выбраны шеф-поваром</p>';
                return;
            }

            container.innerHTML = dishes.map(dish => `
                <div class="special-card fade-in">
                    <img class="special-card-image clickable-img" src="${dish.image || 'uploads/dishes/placeholder.jpg'}" alt="${dish.name}">
                    <div class="special-card-body">
                        <span class="special-badge">👨‍🍳 Шеф-рекомендует</span>
                        <h3>${dish.name}</h3>
                        <p>${dish.description || ''}</p>
                        <span class="price">${Number(dish.price).toLocaleString()} ₽</span>
                        <a href="menu.php" class="btn" style="margin-top:auto;font-size:11px;padding:8px 16px;">🍽 В меню</a>
                    </div>
                </div>
            `).join('');

            setTimeout(initFadeIn, 100);
        })
        .catch(() => {
            container.innerHTML = '<p style="text-align:center;color:var(--color-text-light);">Не удалось загрузить фирменные блюда</p>';
        });
}

// ========== LOAD POPULAR DISHES (из БД) ==========
function loadPopularDishes() {
    const container = document.getElementById('popular-dishes');
    if (!container) return;

    fetch('../backend/getPopularDishes.php')
        .then(r => r.json())
        .then(dishes => {
            if (!dishes || dishes.length === 0) {
                container.innerHTML = '<p style="text-align:center;color:var(--color-text-light);grid-column:1/-1;">Популярные блюда пока не выбраны шеф-поваром</p>';
                return;
            }

            container.innerHTML = dishes.map(dish => `
                <div class="dish-card fade-in">
                    <img class="dish-card-image clickable-img" src="${dish.image || 'uploads/dishes/placeholder.jpg'}" alt="${dish.name}">
                    <div class="dish-card-body">
                        <h3>${dish.name}</h3>
                        <p>${dish.description || ''}</p>
                        <div class="dish-card-footer">
                            <span class="price">${Number(dish.price).toLocaleString()} ₽</span>
                            <a href="menu.php" class="btn">🍽 В меню</a>
                        </div>
                    </div>
                </div>
            `).join('');

            setTimeout(initFadeIn, 100);
        })
        .catch(() => {
            container.innerHTML = '<p style="text-align:center;color:var(--color-text-light);">Не удалось загрузить популярные блюда</p>';
        });
}

// ========== LOAD TEAM (horizontal slider) ==========
function loadTeam() {
    const container = document.getElementById('team-grid');
    if (!container) return;

    const team = [
        { name: 'Антонио Бьянки', role: 'Шеф-повар', desc: '15 лет опыта в ресторанах Мишлен', img: 'uploads/team/1-chef.jpg' },
        { name: 'Анастасия Костюренко', role: 'Су-шеф', desc: 'Специалист по итальянской кухне', img: 'uploads/team/2-sous-chef.jpg' },
        { name: 'Дмитрий Волков', role: 'Кондитер', desc: 'Автор уникальных десертов', img: 'uploads/team/3-pastry.jpg' },
        { name: 'Елена Преображенская', role: 'Сомелье', desc: 'Эксперт по винным сочетаниям', img: 'uploads/team/4-sommelier.jpg' },
    ];

    // Создаём слайдер с портретными карточками
    container.innerHTML = `
        <div class="team-slider">
            ${team.map(m => `
                <div class="team-card">
                    <img class="team-card-image clickable-img" src="${m.img}" alt="${m.name}">
                    <div class="team-card-body">
                        <h3>${m.name}</h3>
                        <div class="team-role">${m.role}</div>
                        <p>${m.desc}</p>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="team-slider-dots"></div>
    `;

    setTimeout(() => initSlider(container, '.team-slider', '.team-slider-dots', 'team-dot'), 50);
    setTimeout(initFadeIn, 100);
}

// ========== LOAD REVIEWS (slider) ==========
function loadReviews() {
    const container = document.getElementById('reviews-grid');
    if (!container) return;
    if (container.querySelector('.reviews-slider')) return;

    const reviews = [
        { name: 'Анна', text: 'Потрясающее место! Обслуживание на высшем уровне, а стейк — просто божественный. Обязательно вернусь!', rating: 5 },
        { name: 'Иван', text: 'Отличный ресторан для ужина с семьёй. Уютная атмосфера, внимательный персонал, вкусная еда.', rating: 4 },
        { name: 'Мария', text: 'Лучший ресторан в городе! Каждое блюдо — шедевр. Особенно рекомендую десерты.', rating: 5 },
    ];

    container.innerHTML = `
        <div class="reviews-slider">
            ${reviews.map(r => `
                <div class="review-card">
                    <div class="quote">"</div>
                    <p>${r.text}</p>
                    <div class="reviewer-name">— ${r.name}</div>
                    <div class="rating">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
                </div>
            `).join('')}
        </div>
        <div class="reviews-slider-dots"></div>
    `;

    setTimeout(() => initSlider(container, '.reviews-slider', '.reviews-slider-dots', 'review-dot'), 50);

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
    if (container.querySelector('.news-slider')) return;

    const news = [
        { title: 'Новое сезонное меню', date: '01.06.2026', desc: 'Попробуйте наши новые летние блюда из свежих сезонных продуктов', img: 'uploads/news/1-seasonal-menu.jpg' },
        { title: 'Скидка 20% на первый заказ', date: '28.05.2026', desc: 'Для новых клиентов скидка на первый заказ через сайт', img: 'uploads/news/2-discount.jpg' },
        { title: 'Мастер-класс от шеф-повара', date: '20.05.2026', desc: 'Научитесь готовить фирменные блюда нашего ресторана', img: 'uploads/news/3-masterclass.jpg' },
    ];

    container.innerHTML = `
        <div class="news-slider">
            ${news.map(n => `
                <div class="news-card">
                    <img class="news-card-image clickable-img" src="${n.img}" alt="${n.title}">
                    <div class="news-card-body">
                        <span class="news-date">${n.date}</span>
                        <h3>${n.title}</h3>
                        <p>${n.desc}</p>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="news-slider-dots"></div>
    `;

    setTimeout(() => initSlider(container, '.news-slider', '.news-slider-dots', 'news-dot'), 50);
    setTimeout(initFadeIn, 100);
}

// ========== BOOKING FORM (модальное окно + fetch) ==========
function initBookingForm() {
    const form = document.getElementById('booking-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.textContent = '⏳ Отправка...';
        btn.disabled = true;

        try {
            const response = await fetch('/backend/createBooking.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                openBookingModal(result.message);
                this.reset();
            } else {
                openBookingErrorModal(result.message);
            }
        } catch (err) {
            openBookingErrorModal('Ошибка соединения. Проверьте подключение к интернету.');
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });
}

// Функции модального окна бронирования
function openBookingModal(message) {
    const el = document.getElementById('booking-modal-text');
    const modal = document.getElementById('booking-modal');
    if (el) el.textContent = message || 'Спасибо! Мы свяжемся с вами для подтверждения.';
    if (modal) modal.classList.add('active');
}
function closeBookingModal() {
    const modal = document.getElementById('booking-modal');
    if (modal) modal.classList.remove('active');
}
function openBookingErrorModal(message) {
    const el = document.getElementById('booking-error-text');
    const modal = document.getElementById('booking-error-modal');
    if (el) el.textContent = message || 'Проверьте данные и попробуйте снова.';
    if (modal) modal.classList.add('active');
}
function closeBookingErrorModal() {
    const modal = document.getElementById('booking-error-modal');
    if (modal) modal.classList.remove('active');
}

// Закрытие модалок по Escape и клику вне окна
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBookingModal();
        closeBookingErrorModal();
    }
});
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('booking-modal-overlay')) {
        e.target.classList.remove('active');
    }
});
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

// ========== СЛАЙДЕР (адаптивный, несколько карточек) ==========
function initSlider(container, sliderSelector, dotsSelector, dotClass) {
    const wrap = container.querySelector(sliderSelector);
    const dotsEl = container.querySelector(dotsSelector);
    if (!wrap || wrap.children.length === 0) return;

    wrap.style.cssText = 'overflow:hidden';

    const track = document.createElement('div');
    track.style.cssText = 'display:flex;transition:transform 0.5s ease';
    while (wrap.firstChild) track.appendChild(wrap.firstChild);
    wrap.appendChild(track);

    function getCardsPerView() {
        const w = wrap.clientWidth;
        if (w < 600) return 1;
        if (w < 900) return 2;
        if (w < 1200) return 3;
        return 4;
    }

    function layoutCards() {
        const perView = getCardsPerView();
        const w = wrap.clientWidth;
        const cardW = w / perView;
        for (let c of track.children) {
            c.style.cssText = 'min-width:' + cardW + 'px;flex-shrink:0;box-sizing:border-box';
        }
        return { perView, cardW };
    }

    let { perView, cardW } = layoutCards();

    const originals = [...track.children];
    const total = originals.length;
    if (total <= 1) return;

    // Клоны для бесконечности (копируем perView штук с каждой стороны)
    for (let i = 0; i < perView; i++) track.appendChild(originals[i].cloneNode(true));
    for (let i = perView - 1; i >= 0; i--) track.insertBefore(originals[total - 1 - i].cloneNode(true), track.firstChild);

    const allItems = track.children;
    let current = perView; // начинаем с первого реального
    let animating = false;

    // Стрелки
    function btn(cl, html) {
        const b = document.createElement('button');
        b.className = 'slider-arrow ' + cl;
        b.innerHTML = html;
        container.appendChild(b);
        return b;
    }
    btn('slider-prev', '‹');
    btn('slider-next', '›');

    // Точки
    for (let i = 0; i < total; i++) {
        const d = document.createElement('button');
        d.className = dotClass + (i === 0 ? ' active' : '');
        d.onclick = () => slideTo(i + perView);
        dotsEl.appendChild(d);
    }

    function slideTo(index) {
        if (animating) return;
        animating = true;

        const { cardW: cw } = layoutCards();
        cardW = cw;

        current = index;
        track.style.transform = 'translateX(-' + (current * cardW) + 'px)';

        // Точки
        const realIdx = (current - perView + total) % total;
        dotsEl.querySelectorAll('.' + dotClass).forEach((d, i) => d.classList.toggle('active', i === realIdx));

        // Проверка клонов
        setTimeout(() => {
            animating = false;
            const maxClone = allItems.length - perView;
            if (current >= maxClone) {
                track.style.transition = 'none';
                current = perView;
                track.style.transform = 'translateX(-' + (current * cardW) + 'px)';
                requestAnimationFrame(() => requestAnimationFrame(() => { track.style.transition = 'transform 0.5s ease'; }));
            } else if (current < perView) {
                track.style.transition = 'none';
                current = total + perView - 1;
                track.style.transform = 'translateX(-' + (current * cardW) + 'px)';
                requestAnimationFrame(() => requestAnimationFrame(() => { track.style.transition = 'transform 0.5s ease'; }));
            }
        }, 550);
    }

    // Обработка ресайза
    window.addEventListener('resize', () => {
        const newPerView = getCardsPerView();
        if (newPerView !== perView) {
            perView = newPerView;
            layoutCards();
            // Пересоздаём клоны
            // Удаляем старые клоны
            while (track.children.length > total) track.removeChild(track.lastChild);
            while (track.children.length > total) track.removeChild(track.firstChild);
            track.style.transition = 'none';
            // Добавляем новые
            for (let i = 0; i < perView; i++) track.appendChild(originals[i].cloneNode(true));
            for (let i = perView - 1; i >= 0; i--) track.insertBefore(originals[total - 1 - i].cloneNode(true), track.firstChild);
            current = perView;
            track.style.transform = 'translateX(-' + (current * cardW) + 'px)';
            requestAnimationFrame(() => requestAnimationFrame(() => { track.style.transition = 'transform 0.5s ease'; }));
        }
    });

    container.querySelector('.slider-prev').onclick = () => slideTo(current - 1);
    container.querySelector('.slider-next').onclick = () => slideTo(current + 1);

    slideTo(perView);
}
