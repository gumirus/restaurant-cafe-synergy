<?php require_once __DIR__ . '/header.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>Новости</h1>
            <p>События и новинки ресторана Bean Scene</p>
        </div>
    </section>

    <section class="news-section">
        <div class="container">
            <div class="news-grid">
                <article class="news-card fade-in">
                    <img src="images/placeholder.jpg" alt="Новинка">
                    <div class="news-card-body">
                        <span class="news-date">5 июня 2026</span>
                        <h3>Новое сезонное меню</h3>
                        <p>Шеф-повар Антонио Бьянки представил летнее меню с акцентом на свежие овощи и морепродукты.</p>
                        <a href="#" class="btn btn-small">Подробнее</a>
                    </div>
                </article>
                <article class="news-card fade-in">
                    <img src="images/placeholder.jpg" alt="Дегустация">
                    <div class="news-card-body">
                        <span class="news-date">28 мая 2026</span>
                        <h3>Винная дегустация</h3>
                        <p>Приглашаем на вечер виноделия. В программе — 5 сортов вин и закуски от шефа.</p>
                        <a href="#" class="btn btn-small">Подробнее</a>
                    </div>
                </article>
                <article class="news-card fade-in">
                    <img src="images/placeholder.jpg" alt="Награда">
                    <div class="news-card-body">
                        <span class="news-date">15 мая 2026</span>
                        <h3>Награда "Лучший ресторан года"</h3>
                        <p>Bean Scene признан лучшим рестораном Москвы по версии престижной премии.</p>
                        <a href="#" class="btn btn-small">Подробнее</a>
                    </div>
                </article>
                <article class="news-card fade-in">
                    <img src="images/placeholder.jpg" alt="Мастер-класс">
                    <div class="news-card-body">
                        <span class="news-date">2 мая 2026</span>
                        <h3>Мастер-класс по итальянской кухне</h3>
                        <p>Научитесь готовить настоящую пасту и пиццу под руководством нашего су-шефа.</p>
                        <a href="#" class="btn btn-small">Подробнее</a>
                    </div>
                </article>
                <article class="news-card fade-in">
                    <img src="images/placeholder.jpg" alt="Завтраки">
                    <div class="news-card-body">
                        <span class="news-date">20 апреля 2026</span>
                        <h3>Теперь работаем с 8:00</h3>
                        <p>Специальное утреннее меню завтраков с 8:00 до 11:00. Кофе в подарок к каждому завтраку!</p>
                        <a href="#" class="btn btn-small">Подробнее</a>
                    </div>
                </article>
                <article class="news-card fade-in">
                    <img src="images/placeholder.jpg" alt="Доставка">
                    <div class="news-card-body">
                        <span class="news-date">10 апреля 2026</span>
                        <h3>Запустили доставку</h3>
                        <p>Теперь наши блюда можно заказать с доставкой на дом. Бесплатно при заказе от 1500 ₽.</p>
                        <a href="#" class="btn btn-small">Подробнее</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <style>
    .page-hero {
        padding: 140px 0 60px;
        background: linear-gradient(135deg, var(--color-bg-dark) 0%, #1a0f0a 100%);
        text-align: center;
    }
    .page-hero h1 { font-size: 2.8rem; color: var(--color-text-white); margin-bottom: 10px; }
    .page-hero p { color: var(--color-text-light); font-size: 1.1rem; }
    .news-section { padding: 80px 0; }
    .news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
    .news-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px; overflow: hidden; transition: transform 0.3s; }
    .news-card:hover { transform: translateY(-5px); }
    .news-card img { width: 100%; height: 200px; object-fit: cover; }
    .news-card-body { padding: 20px; }
    .news-date { color: var(--color-primary); font-size: 0.85rem; font-weight: 500; }
    .news-card-body h3 { color: var(--color-text-white); margin: 10px 0; font-size: 1.2rem; }
    .news-card-body p { color: var(--color-text-light); font-size: 0.9rem; margin-bottom: 15px; line-height: 1.6; }
    </style>

<?php require_once __DIR__ . '/footer.php'; ?>
