<?php require_once __DIR__ . '/header.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>О нашем ресторане</h1>
            <p>История, миссия и команда Bean Scene</p>
        </div>
    </section>

    <section class="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-image fade-in">
                    <img src="images/about.jpg" alt="О ресторане">
                </div>
                <div class="about-text fade-in">
                    <h2>Наша <span>история</span></h2>
                    <p>Bean Scene открыл свои двери в 2011 году. За 15 лет мы прошли путь от небольшого кафе до ресторана премиум-класса, завоевав признание гостей и профессиональные награды.</p>
                    <p>Наша философия — использовать только свежие продукты от местных фермеров, сочетать классические рецепты с современными гастрономическими трендами и создавать атмосферу, в которую хочется возвращаться.</p>
                    <div class="about-features">
                        <div class="about-feature">
                            <span class="feature-icon">🥩</span>
                            <span>Свежие продукты</span>
                        </div>
                        <div class="about-feature">
                            <span class="feature-icon">👨‍🍳</span>
                            <span>Шеф-повара из Европы</span>
                        </div>
                        <div class="about-feature">
                            <span class="feature-icon">🍷</span>
                            <span>Винная карта</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <h2 class="section-title fade-in">Наша <span>команда</span></h2>
            <p class="section-subtitle fade-in">Профессионалы, которые создают для вас кулинарные шедевры</p>
            <div class="team-grid">
                <div class="team-card fade-in">
                    <img class="team-card-image clickable-img" src="uploads/team/1-chef.jpg" alt="Антонио Бьянки">
                    <div class="team-card-body">
                        <h3>Антонио Бьянки</h3>
                        <div class="team-role">Шеф-повар</div>
                        <p>15 лет опыта в ресторанах Мишлен</p>
                    </div>
                </div>
                <div class="team-card fade-in">
                    <img class="team-card-image clickable-img" src="uploads/team/2-sous-chef.jpg" alt="Мария Соколова">
                    <div class="team-card-body">
                        <h3>Мария Соколова</h3>
                        <div class="team-role">Су-шеф</div>
                        <p>Специалист по итальянской кухне</p>
                    </div>
                </div>
                <div class="team-card fade-in">
                    <img class="team-card-image clickable-img" src="uploads/team/3-pastry.jpg" alt="Дмитрий Волков">
                    <div class="team-card-body">
                        <h3>Дмитрий Волков</h3>
                        <div class="team-role">Кондитер</div>
                        <p>Автор уникальных десертов</p>
                    </div>
                </div>
                <div class="team-card fade-in">
                    <img class="team-card-image clickable-img" src="uploads/team/4-sommelier.jpg" alt="Елена Преображенская">
                    <div class="team-card-body">
                        <h3>Елена Преображенская</h3>
                        <div class="team-role">Сомелье</div>
                        <p>Эксперт по винным сочетаниям</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews">
        <div class="container">
            <h2 class="section-title fade-in">Часто задаваемые <span>вопросы</span></h2>
            <p class="section-subtitle fade-in">Ответы на популярные вопросы гостей</p>
            <div class="faq-list" id="faq-list">
                <div class="faq-item">
                    <div class="faq-question">Как забронировать столик?</div>
                    <div class="faq-answer">Вы можете забронировать столик через форму на сайте, по телефону +7 (999) 123-45-67 или написав нам на email.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Есть ли у вас доставка?</div>
                    <div class="faq-answer">Да, мы доставляем блюда по Москве в пределах МКАД. Минимальная сумма заказа — 1500 ₽.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Работаете ли вы в праздничные дни?</div>
                    <div class="faq-answer">Да, мы работаем ежедневно с 10:00 до 23:00, включая выходные и праздничные дни.</div>
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
    .about { padding: 80px 0; }
    .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
    .about-image img { width: 100%; border-radius: 16px; }
    .about-text h2 { font-size: 2rem; margin-bottom: 20px; color: var(--color-text-white); }
    .about-text h2 span { color: var(--color-primary); }
    .about-text p { color: var(--color-text-light); line-height: 1.8; margin-bottom: 15px; }
    .about-features { display: flex; gap: 20px; margin-top: 30px; flex-wrap: wrap; }
    .about-feature { display: flex; align-items: center; gap: 10px; background: var(--color-surface); padding: 12px 20px; border-radius: 10px; border: 1px solid var(--color-border); }
    .feature-icon { font-size: 1.5rem; }
    .team-section { padding: 80px 0; background: var(--color-surface); }
    .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; margin-top: 50px; }
    .team-card { background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 16px; overflow: hidden; text-align: center; transition: transform 0.3s; }
    .team-card:hover { transform: translateY(-5px); }
    .team-card-image { width: 100%; height: 250px; object-fit: cover; }
    .team-card-body { padding: 20px; }
    .team-card-body h3 { color: var(--color-text-white); margin-bottom: 5px; }
    .team-role { color: var(--color-primary); font-weight: 600; margin-bottom: 10px; }
    .team-card-body p { color: var(--color-text-light); font-size: 0.9rem; }
    .reviews { padding: 80px 0; }
    .faq-list { max-width: 700px; margin: 40px auto 0; }
    .faq-item { border: 1px solid var(--color-border); border-radius: 12px; margin-bottom: 12px; overflow: hidden; }
    .faq-question { padding: 18px 24px; cursor: pointer; font-weight: 600; color: var(--color-text-white); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface); }
    .faq-question::after { content: '+'; font-size: 1.3rem; color: var(--color-primary); transition: transform 0.3s; }
    .faq-item.active .faq-question::after { transform: rotate(45deg); }
    .faq-answer { padding: 0 24px; max-height: 0; overflow: hidden; transition: all 0.3s; color: var(--color-text-light); }
    .faq-item.active .faq-answer { padding: 18px 24px; max-height: 200px; }
    @media (max-width: 768px) { .about-grid { grid-template-columns: 1fr; } }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.faq-question').forEach(q => {
            q.addEventListener('click', function() {
                this.parentElement.classList.toggle('active');
            });
        });
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
