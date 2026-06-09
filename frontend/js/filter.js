// ========== FILTER MENU ==========
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const dishesGrid = document.getElementById('menu-dishes');
    if (!dishesGrid) return;

    // Данные меню
    const menuItems = [
        { id: 1, name: 'Цезарь с курицей', price: 450, category: 'salads', desc: 'Классический салат с курицей' },
        { id: 2, name: 'Греческий салат', price: 380, category: 'salads', desc: 'Свежие овощи с сыром фета' },
        { id: 3, name: 'Том Ям', price: 550, category: 'soups', desc: 'Острый тайский суп' },
        { id: 4, name: 'Борщ', price: 320, category: 'soups', desc: 'Традиционный русский суп' },
        { id: 5, name: 'Стейк Рибай', price: 1200, category: 'main', desc: 'Мраморная говядина' },
        { id: 6, name: 'Паста Карбонара', price: 480, category: 'main', desc: 'Итальянская паста' },
        { id: 7, name: 'Тирамису', price: 350, category: 'desserts', desc: 'Итальянский десерт' },
        { id: 8, name: 'Чизкейк', price: 320, category: 'desserts', desc: 'Нью-йоркский чизкейк' },
        { id: 9, name: 'Лимонад', price: 180, category: 'drinks', desc: 'Домашний лимонад' },
        { id: 10, name: 'Кофе', price: 200, category: 'drinks', desc: 'Эспрессо/Капучино/Латте' },
    ];

    // Отобразить все блюда
    function renderDishes(category = 'all') {
        const filtered = category === 'all' 
            ? menuItems 
            : menuItems.filter(item => item.category === category);

        dishesGrid.innerHTML = filtered.map(item => `
            <div class="dish-card">
                <img src="images/placeholder.svg" alt="${item.name}">
                <div class="dish-card-body">
                    <h3>${item.name}</h3>
                    <p>${item.desc}</p>
                    <p class="price">${item.price} ₽</p>
                    <button class="btn add-to-cart" data-id="${item.id}">В корзину</button>
                </div>
            </div>
        `).join('');
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
