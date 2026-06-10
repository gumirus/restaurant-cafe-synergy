<?php require_once __DIR__ . '/header.php';

$news = [
    [
        'date' => '5 июня 2026',
        'title' => 'Новое сезонное меню',
        'text' => 'Шеф-повар Антонио Бьянки представил летнее меню с акцентом на свежие овощи и морепродукты.',
        'full' => 'Шеф-повар Антонио Бьянки представил долгожданное летнее меню. В него вошли блюда из свежих сезонных овощей, морепродуктов и ягод. Особого внимания заслуживает новое десертное предложение — лимонный тарт с малиновым сорбетом. Летнее меню будет действовать до конца августа.',
        'img' => 'images/placeholder.jpg',
    ],
    [
        'date' => '28 мая 2026',
        'title' => 'Винная дегустация',
        'text' => 'Приглашаем на вечер виноделия. В программе — 5 сортов вин и закуски от шефа.',
        'full' => 'Приглашаем вас на уникальный вечер виноделия! В программе дегустация 5 сортов вин из разных регионов Италии и Франции. Каждое вино будет сопровождаться специально подобранной закуской от нашего шеф-повара. Вечер проведёт профессиональный сомелье.',
        'img' => 'images/placeholder.jpg',
    ],
    [
        'date' => '15 мая 2026',
        'title' => 'Награда "Лучший ресторан года"',
        'text' => 'Bean Scene признан лучшим рестораном Москвы по версии престижной премии.',
        'full' => 'Мы рады сообщить, что Bean Scene признан лучшим рестораном Москвы по версии престижной ресторанной премии. Эта награда — результат труда всей нашей команды и любви наших гостей. Спасибо, что вы с нами!',
        'img' => 'images/placeholder.jpg',
    ],
    [
        'date' => '2 мая 2026',
        'title' => 'Мастер-класс по итальянской кухне',
        'text' => 'Научитесь готовить настоящую пасту и пиццу под руководством нашего су-шефа.',
        'full' => 'Наш су-шеф Мария Соколова проведёт мастер-класс по итальянской кухне. Вы научитесь готовить настоящую пасту феттучини, пиццу маргариту и тирамису. Все ингредиенты предоставляются. Количество мест ограничено.',
        'img' => 'images/placeholder.jpg',
    ],
    [
        'date' => '20 апреля 2026',
        'title' => 'Теперь работаем с 8:00',
        'text' => 'Специальное утреннее меню завтраков с 8:00 до 11:00. Кофе в подарок к каждому завтраку!',
        'full' => 'С этого месяца мы начинаем работать с 8:00 утра! Для ранних пташек мы подготовили специальное утреннее меню завтраков, которое подаётся с 8:00 до 11:00. Кофе в подарок к каждому завтраку. Приходите начинать день с Bean Scene!',
        'img' => 'images/placeholder.jpg',
    ],
    [
        'date' => '10 апреля 2026',
        'title' => 'Запустили доставку',
        'text' => 'Теперь наши блюда можно заказать с доставкой на дом. Бесплатно при заказе от 1500 ₽.',
        'full' => 'Теперь наши блюда можно заказать с доставкой на дом! Доставка осуществляется по Москве в пределах МКАД с 10:00 до 22:00. Бесплатная доставка при заказе от 1500 ₽. Заказывайте через сайт или по телефону.',
        'img' => 'images/placeholder.jpg',
    ],
];
?>

    <section class="page-hero">
        <div class="container">
            <h1>Новости</h1>
            <p>События и новинки ресторана Bean Scene</p>
        </div>
    </section>

    <section class="news-section">
        <div class="container">
            <div class="news-grid">
                <?php foreach ($news as $i => $item): ?>
                <article class="news-card fade-in">
                    <img src="<?= $item['img'] ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div class="news-card-body">
                        <span class="news-date"><?= $item['date'] ?></span>
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p><?= htmlspecialchars($item['text']) ?></p>
                        <button class="btn btn-small news-btn" data-index="<?= $i ?>">Подробнее</button>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Модальное окно -->
    <div class="modal-overlay" id="news-modal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <span class="news-date" id="modal-date"></span>
            <h2 id="modal-title"></h2>
            <p id="modal-text"></p>
        </div>
    </div>

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
    .news-card {
        background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 16px;
        overflow: hidden; transition: transform 0.3s;
        display: flex; flex-direction: column;
    }
    .news-card:hover { transform: translateY(-5px); }
    .news-card img { width: 100%; height: 200px; object-fit: cover; flex-shrink: 0; }
    .news-card-body {
        padding: 20px;
        display: flex; flex-direction: column; flex: 1;
    }
    .news-card-body p { flex: 1; }
    .news-date { color: var(--color-primary); font-size: 0.85rem; font-weight: 500; }
    .news-card-body h3 { color: var(--color-text); margin: 10px 0; font-size: 1.2rem; }
    .news-card-body p { color: var(--color-text-light); font-size: 0.9rem; margin-bottom: 15px; line-height: 1.6; }
    .news-card-body .btn { color: #fff; align-self: flex-start; cursor: pointer; }

    /* Модальное окно */
    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.7); align-items: center; justify-content: center;
        padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-content {
        background: var(--color-surface); border: 1px solid var(--color-border);
        border-radius: 16px; padding: 40px; max-width: 600px; width: 100%;
        position: relative; max-height: 80vh; overflow-y: auto;
    }
    .modal-close {
        position: absolute; top: 15px; right: 20px;
        background: none; border: none; color: var(--color-text-light);
        font-size: 2rem; cursor: pointer; line-height: 1;
    }
    .modal-close:hover { color: var(--color-primary); }
    .modal-content .news-date { display: block; margin-bottom: 10px; }
    .modal-content h2 { color: var(--color-text-white); margin-bottom: 20px; font-size: 1.6rem; }
    .modal-content p { color: var(--color-text-light); line-height: 1.8; font-size: 1rem; }
    </style>

    <script>
    const newsData = <?= json_encode($news, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('news-modal');
        const modalDate = document.getElementById('modal-date');
        const modalTitle = document.getElementById('modal-title');
        const modalText = document.getElementById('modal-text');
        const modalClose = modal.querySelector('.modal-close');

        document.querySelectorAll('.news-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.dataset.index;
                const item = newsData[idx];
                modalDate.textContent = item.date;
                modalTitle.textContent = item.title;
                modalText.textContent = item.full;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        modalClose.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
