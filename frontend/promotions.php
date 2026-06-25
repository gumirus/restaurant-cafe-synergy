<?php require_once __DIR__ . '/header.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>Акции</h1>
            <p>Специальные предложения и скидки Точка Кипения</p>
        </div>
    </section>

    <section class="promotions-section">
        <div class="container">
            <div class="promo-grid">
                <div class="promo-card promo-primary fade-in">
                    <div class="promo-badge">🔥 Горячее предложение</div>
                    <h3>Счастливые часы</h3>
                    <p>Скидка 20% на все напитки с 14:00 до 16:00 в будние дни</p>
                    <div class="promo-meta">Действует до 31 июля 2026</div>
                </div>
                <div class="promo-card fade-in">
                    <div class="promo-badge">🎉 Акция</div>
                    <h3>День рождения</h3>
                    <p>Именинникам — скидка 15% на весь счёт и десерт в подарок</p>
                    <div class="promo-meta">При предъявлении паспорта</div>
                </div>
                <div class="promo-card fade-in">
                    <div class="promo-badge">🍕 Комбо</div>
                    <h3>Бизнес-ланч</h3>
                    <p>Суп + горячее + напиток всего за 590 ₽ с 12:00 до 15:00</p>
                    <div class="promo-meta">По будням</div>
                </div>
                <div class="promo-card fade-in">
                    <div class="promo-badge">🥂 Для двоих</div>
                    <h3>Романтический ужин</h3>
                    <p>Сет из 3 блюд + бутылка вина за 2990 ₽ на двоих</p>
                    <div class="promo-meta">По предварительному бронированию</div>
                </div>
                <div class="promo-card fade-in">
                    <div class="promo-badge">🎓 Студентам</div>
                    <h3>Студенческая скидка</h3>
                    <p>Скидка 10% при предъявлении студенческого билета</p>
                    <div class="promo-meta">Круглый год</div>
                </div>
                <div class="promo-card fade-in">
                    <div class="promo-badge">☕ Завтраки</div>
                    <h3>Утреннее меню</h3>
                    <p>Кофе в подарок к каждому завтраку с 8:00 до 11:00</p>
                    <div class="promo-meta">Ежедневно</div>
                </div>
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
    .promotions-section { padding: 80px 0; }
    .promo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
    .promo-card {
        background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px;
        padding: 30px; transition: transform 0.3s, box-shadow 0.3s; position: relative; overflow: hidden;
    }
    .promo-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    .promo-primary {
        background: linear-gradient(135deg, var(--color-primary) 0%, #8B4513 100%);
        border-color: var(--color-primary);
    }
    .promo-primary h3, .promo-primary p, .promo-primary .promo-meta { color: #fff; }
    .promo-badge {
        display: inline-block; padding: 4px 14px; border-radius: 20px;
        background: rgba(255,255,255,0.2); color: #fff; font-size: 0.8rem; font-weight: 600;
        margin-bottom: 15px;
    }
    .promo-primary .promo-badge { background: rgba(0,0,0,0.2); }
    .promo-card h3 { color: var(--color-text-white); font-size: 1.4rem; margin-bottom: 12px; }
    .promo-card p { color: var(--color-text-light); line-height: 1.6; margin-bottom: 15px; }
    .promo-meta { color: var(--color-primary); font-size: 0.85rem; font-weight: 500; }
    .promo-primary .promo-meta { color: rgba(255,255,255,0.8); }
    </style>

<?php require_once __DIR__ . '/footer.php'; ?>
