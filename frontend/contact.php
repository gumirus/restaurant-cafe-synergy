<?php require_once __DIR__ . '/header.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>Контакты</h1>
            <p>Свяжитесь с нами или забронируйте столик</p>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info fade-in">
                    <h2>Наши <span>контакты</span></h2>
                    <div class="contact-item">
                        <span class="contact-icon">📍</span>
                        <div>
                            <strong>Адрес</strong>
                            <p>г. Москва, ул. Тверская, 15</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">📞</span>
                        <div>
                            <strong>Телефон</strong>
                            <p><a href="tel:+79991234567">+7 (999) 123-45-67</a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">✉️</span>
                        <div>
                            <strong>Email</strong>
                            <p><a href="mailto:info@beanscene.ru">info@beanscene.ru</a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🕐</span>
                        <div>
                            <strong>Часы работы</strong>
                            <p>Пн–Вс: 10:00 – 23:00</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form fade-in">
                    <h2>Забронировать <span>столик</span></h2>
                    <form id="booking-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Ваше имя</label>
                                <input type="text" name="name" placeholder="Иван" required>
                            </div>
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="tel" name="phone" placeholder="+7 (999) 123-45-67" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Дата</label>
                                <input type="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label>Время</label>
                                <input type="time" name="time" required>
                            </div>
                            <div class="form-group">
                                <label>Гостей</label>
                                <input type="number" name="guests" min="1" max="20" value="2">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Пожелания</label>
                            <textarea name="comment" placeholder="Особые пожелания..."></textarea>
                        </div>
                        <button type="submit" class="btn">Забронировать</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <h2 class="section-title">Как нас <span>найти</span></h2>
            <div class="map-container">
                <iframe src="https://yandex.ru/map-widget/v1/?ll=37.610000%2C55.740000&z=16&pt=37.610000%2C55.740000%2Cpm2rdl&l=map&text=Bean%20Scene%20%D1%80%D0%B5%D1%81%D1%82%D0%BE%D1%80%D0%B0%D0%BD" width="100%" height="450" style="border:0; border-radius: 16px;" allowfullscreen="" loading="lazy"></iframe>
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
    .contact-section { padding: 80px 0; }
    .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; }
    .contact-info h2, .contact-form h2 { font-size: 2rem; margin-bottom: 30px; color: var(--color-text-white); }
    .contact-info h2 span, .contact-form h2 span { color: var(--color-primary); }
    .contact-item { display: flex; gap: 15px; margin-bottom: 25px; }
    .contact-icon { font-size: 1.8rem; }
    .contact-item strong { display: block; color: var(--color-text-white); margin-bottom: 4px; }
    .contact-item p, .contact-item a { color: var(--color-text-light); text-decoration: none; }
    .contact-item a:hover { color: var(--color-primary); }
    .contact-form { background: var(--color-surface); padding: 40px; border-radius: 16px; border: 1px solid var(--color-border); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; margin-bottom: 6px; color: var(--color-text); font-weight: 500; font-size: 0.9rem; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 12px 16px; border: 1px solid var(--color-border); border-radius: 8px;
        background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.3s;
    }
    .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--color-primary); }
    .form-group textarea { min-height: 80px; resize: vertical; }
    .map-section { padding: 80px 0; }
    .map-container { margin-top: 40px; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
    .map-container iframe { display: block; }
    @media (max-width: 768px) { .contact-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } }
    </style>

    <script>
    document.getElementById('booking-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('✅ Спасибо! Мы свяжемся с вами для подтверждения брони.');
        this.reset();
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
