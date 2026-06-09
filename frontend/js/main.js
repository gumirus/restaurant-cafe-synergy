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
        { name: 'Салат с трюфелем', desc: 'Руккола, пармезан, трюфельное масло', price: 650, badge: 'Шеф-рекомендует' },
        { name: 'Утиная грудка', desc: 'Карамелизированная утка с соусом из вишни', price: 890, badge: 'Хит сезона' },
        { name: 'Чизкейк Нью-Йорк', desc: 'Классический десерт с ягодным соусом', price: 390, badge: 'Фирменный' },
    ];

    container.innerHTML = specials.map(s => `
        <div class="special-card fade-in">
            <img class="special-card-image" src="images/placeholder.svg" alt="${s.name}">
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
        { id: 1, name: 'Цезарь с курицей', price: 450, category: 'salads', desc: 'Классический салат с пармезаном' },
        { id: 2, name: 'Том Ям', price: 550, category: 'soups', desc: 'Острый тайский суп с креветками' },
        { id: 3, name: 'Стейк Рибай', price: 1200, category: 'main', desc: 'Мраморная говядина с трюфелем' },
        { id: 4, name: 'Тирамису', price: 350, category: 'desserts', desc: 'Итальянский десерт с маскарпоне' },
    ];

    container.innerHTML = dishes.map(dish => `
        <div class="dish-card fade-in">
            <img class="dish-card-image" src="images/placeholder.svg" alt="${dish.name}">
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
        { name: 'Антонио Бьянки', role: 'Шеф-повар', desc: '15 лет опыта в ресторанах Мишлен' },
        { name: 'Мария Соколова', role: 'Су-шеф', desc: 'Специалист по итальянской кухне' },
        { name: 'Дмитрий Волков', role: 'Кондитер', desc: 'Автор уникальных десертов' },
        { name: 'Елена Преображенская', role: 'Сомелье', desc: 'Эксперт по винным сочетаниям' },
    ];

    container.innerHTML = team.map(m => `
        <div class="team-card fade-in">
            <img class="team-card-image" src="images/placeholder.svg" alt="${m.name}">
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
        { label: 'Интерьер зала' },
        { label: 'Авторские блюда' },
        { label: 'Винная карта' },
        { label: 'Летняя веранда' },
        { label: 'Кухня' },
        { label: 'Десерты' },
    ];

    container.innerHTML = gallery.map(g => `
        <div class="gallery-item fade-in">
            <img src="images/placeholder.svg" alt="${g.label}">
            <div class="gallery-overlay">🔍</div>
        </div>
    `).join('');

    setTimeout(initFadeIn, 100);
}

// ========== LOAD NEWS ==========
function loadNews() {
    const container = document.getElementById('news-grid');
    if (!container) return;

    const news = [
        { title: 'Новое сезонное меню', date: '01.06.2026', desc: 'Попробуйте наши новые летние блюда из свежих сезонных продуктов' },
        { title: 'Скидка 20% на первый заказ', date: '28.05.2026', desc: 'Для новых клиентов скидка на первый заказ через сайт' },
        { title: 'Мастер-класс от шеф-повара', date: '20.05.2026', desc: 'Научитесь готовить фирменные блюда нашего ресторана' },
    ];

    container.innerHTML = news.map(n => `
        <div class="news-card fade-in">
            <img class="news-card-image" src="images/placeholder.svg" alt="${n.title}">
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
