// ========== MAIN JS ==========
// Общие функции для всех страниц

document.addEventListener('DOMContentLoaded', function() {
    console.log('Сайт ресторана загружен');
    
    // Загрузка популярных блюд на главной
    loadPopularDishes();
    
    // Загрузка отзывов
    loadReviews();
    
    // Загрузка новостей
    loadNews();
});

// ========== ЗАГРУЗКА БЛЮД ==========
function loadPopularDishes() {
    const container = document.getElementById('popular-dishes');
    if (!container) return;

    const dishes = [
        { id: 1, name: 'Цезарь с курицей', price: 450, category: 'salads', image: '' },
        { id: 2, name: 'Том Ям', price: 550, category: 'soups', image: '' },
        { id: 3, name: 'Стейк Рибай', price: 1200, category: 'main', image: '' },
        { id: 4, name: 'Тирамису', price: 350, category: 'desserts', image: '' },
    ];

    container.innerHTML = dishes.map(dish => createDishCard(dish)).join('');
}

function createDishCard(dish) {
    return `
        <div class="dish-card" data-category="${dish.category}">
            <img src="${dish.image || 'images/placeholder.jpg'}" alt="${dish.name}">
            <div class="dish-card-body">
                <h3>${dish.name}</h3>
                <p class="price">${dish.price} ₽</p>
                <button class="btn add-to-cart" data-id="${dish.id}">В корзину</button>
            </div>
        </div>
    `;
}

// ========== ЗАГРУЗКА ОТЗЫВОВ ==========
function loadReviews() {
    const container = document.getElementById('reviews-slider');
    if (!container) return;

    const reviews = [
        { name: 'Анна', text: 'Очень вкусно! Обязательно вернусь ещё!', rating: 5 },
        { name: 'Иван', text: 'Отличное место для ужина с семьёй.', rating: 4 },
        { name: 'Мария', text: 'Лучший ресторан в городе!', rating: 5 },
    ];

    container.innerHTML = reviews.map(r => `
        <div class="review-card">
            <p>"${r.text}"</p>
            <strong>— ${r.name}</strong>
            <div class="rating">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div>
        </div>
    `).join('');
}

// ========== ЗАГРУЗКА НОВОСТЕЙ ==========
function loadNews() {
    const container = document.getElementById('news-grid');
    if (!container) return;

    const news = [
        { title: 'Новое сезонное меню', date: '01.06.2026', desc: 'Попробуйте наши новые летние блюда' },
        { title: 'Скидка 20% на первый заказ', date: '28.05.2026', desc: 'Для новых клиентов' },
        { title: 'Мастер-класс от шеф-повара', date: '20.05.2026', desc: 'Научитесь готовить фирменные блюда' },
    ];

    container.innerHTML = news.map(n => `
        <div class="news-card">
            <span class="news-date">${n.date}</span>
            <h3>${n.title}</h3>
            <p>${n.desc}</p>
        </div>
    `).join('');
}
