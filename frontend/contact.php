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
                    <?php
                    // Если пользователь авторизован — подставляем данные из профиля
                    $profileName = '';
                    $profilePhone = '';
                    if (isLoggedIn()) {
                        $stmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $profile = $stmt->fetch();
                        if ($profile) {
                            $profileName = htmlspecialchars($profile['name'] ?? '');
                            $profilePhone = htmlspecialchars($profile['phone']);
                        }
                    }
                    ?>
                    <form id="booking-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Ваше имя</label>
                                <?php if (isLoggedIn() && $profileName): ?>
                                    <input type="text" name="name" value="<?= $profileName ?>" readonly class="input-disabled">
                                <?php else: ?>
                                    <input type="text" name="name" placeholder="Иван" required>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Телефон</label>
                                <?php if (isLoggedIn()): ?>
                                    <input type="tel" name="phone" value="<?= $profilePhone ?>" readonly class="input-disabled">
                                <?php else: ?>
                                    <input type="tel" name="phone" placeholder="+7 (999) 123-45-67" required>
                                <?php endif; ?>
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
                        <div class="form-group">
                            <label>Повод</label>
                            <select name="occasion" style="width:100%;padding:12px;border:1px solid var(--color-border);border-radius:8px;font-size:0.95rem;background:var(--color-bg);">
                                <option value="">— Не выбран —</option>
                                <option value="День рождения">🎂 День рождения</option>
                                <option value="Юбилей">🎉 Юбилей</option>
                                <option value="Свадьба">💍 Свадьба</option>
                                <option value="Романтический ужин">💕 Романтический ужин</option>
                                <option value="Деловая встреча">💼 Деловая встреча</option>
                                <option value="Семейный обед">👨‍👩‍👧‍👦 Семейный обед</option>
                                <option value="Корпоратив">🏢 Корпоратив</option>
                                <option value="Другая">📌 Другая</option>
                            </select>
                        </div>
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
    .form-group label { display: block; margin-bottom: 6px; color: var(--color-text-white); font-weight: 500; font-size: 0.9rem; }
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

    <!-- Модальное окно успешного бронирования -->
    <div id="booking-modal" class="booking-modal-overlay">
        <div class="booking-modal">
            <div class="booking-modal-icon">✅</div>
            <h2 class="booking-modal-title">Столик забронирован!</h2>
            <p class="booking-modal-text" id="booking-modal-text">Спасибо! Мы свяжемся с вами для подтверждения.</p>
            <button class="btn" onclick="closeBookingModal()">Отлично</button>
        </div>
    </div>

    <!-- Модальное окно ошибки -->
    <div id="booking-error-modal" class="booking-modal-overlay">
        <div class="booking-modal">
            <div class="booking-modal-icon" style="background:#e74c3c;">❌</div>
            <h2 class="booking-modal-title" style="color:#e74c3c;">Ошибка</h2>
            <p class="booking-modal-text" id="booking-error-text">Проверьте данные и попробуйте снова.</p>
            <button class="btn" onclick="closeBookingErrorModal()">Понятно</button>
        </div>
    </div>

    <style>
    .booking-modal-overlay {
        position: fixed; inset: 0; z-index: 99999;
        background: rgba(0,0,0,0.7);
        display: flex; align-items: center; justify-content: center;
        visibility: hidden; opacity: 0;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
    }
    .booking-modal-overlay.active {
        visibility: visible; opacity: 1;
    }
    .booking-modal {
        background: var(--color-surface, #1e1e2e);
        border: 1px solid var(--color-border, #333);
        border-radius: 20px;
        max-width: 400px; width: 90%;
        padding: 50px 40px 40px;
        text-align: center;
        box-shadow: 0 25px 80px rgba(0,0,0,0.5);
        transform: scale(0.85) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .booking-modal-overlay.active .booking-modal {
        transform: scale(1) translateY(0);
    }
    .booking-modal-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: #27ae60; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; margin: 0 auto 20px;
        box-shadow: 0 8px 30px rgba(39, 174, 96, 0.3);
    }
    .booking-modal-title {
        font-size: 1.6rem; color: var(--color-text-white, #fff);
        margin-bottom: 12px;
    }
    .booking-modal-text {
        color: var(--color-text-light, #aaa);
        font-size: 1rem; line-height: 1.6;
        margin-bottom: 25px;
    }
    .booking-modal .btn {
        background: var(--color-primary, #d4a853);
        color: #fff; border: none;
        padding: 12px 40px; border-radius: 10px;
        font-size: 1rem; font-weight: 600;
        cursor: pointer; transition: all 0.3s;
    }
    .booking-modal .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(212, 168, 83, 0.3);
    }
    </style>


<?php require_once __DIR__ . '/footer.php'; ?>
