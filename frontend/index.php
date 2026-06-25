<?php require_once __DIR__ . '/header.php'; ?>

    <!-- ========== HERO ========== -->
    <section class="hero">
        <div class="hero-content">
            <p class="hero-subtitle">Добро пожаловать</p>
            <h1>Изысканная <span>кухня</span><br>в каждой детали</h1>
            <p>Откройте для себя мир гастрономических шедевров от лучших шеф-поваров. Свежие продукты, авторские рецепты, неповторимая атмосфера.</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn">Посмотреть меню</a>
                <a href="#booking" class="btn btn-outline">Забронировать</a>
            </div>
        </div>
    </section>

    <!-- ========== ABOUT ========== -->
    <section class="about" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-image fade-in">
                    <img src="images/about.jpg" alt="О ресторане">
                </div>
                <div class="about-text fade-in collapsible" id="aboutText">
                    <h2>О <span>ресторане</span></h2>
                    <div class="about-text-collapsed">
                        <p>Точка Кипения — это не просто ресторан. Это место, где встречаются традиции и современность...</p>
                        <a href="javascript:void(0)" class="btn" onclick="openAboutModal()">Подробнее</a>
                    </div>
                    <div class="about-text-full">
                        <p>Точка Кипения — это не просто ресторан. Это место, где встречаются традиции и современность, где каждое блюдо — произведение искусства.</p>
                        <p>Мы используем только свежие продукты от местных фермеров, а наши шеф-повара постоянно экспериментируют, чтобы удивлять вас новыми вкусами.</p>
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
                        <a href="about.php" class="btn">Узнать больше</a>
                    </div>
                </div>

                <div class="about-modal-overlay" id="aboutModal" onclick="if(event.target===this)closeAboutModal()">
                    <div class="about-modal">
                        <button class="about-modal-close" onclick="closeAboutModal()">×</button>
                        <h2>О <span>ресторане</span></h2>
                        <p>Точка Кипения — это не просто ресторан. Это место, где встречаются традиции и современность, где каждое блюдо — произведение искусства.</p>
                        <p>Мы используем только свежие продукты от местных фермеров, а наши шеф-повара постоянно экспериментируют, чтобы удивлять вас новыми вкусами.</p>
                        <div class="about-features" style="margin:20px 0;">
                            <div class="about-feature" style="margin-bottom:15px;">
                                <span class="feature-icon">🥩</span>
                                <span>Свежие продукты</span>
                            </div>
                            <div class="about-feature" style="margin-bottom:15px;">
                                <span class="feature-icon">👨‍🍳</span>
                                <span>Шеф-повара из Европы</span>
                            </div>
                            <div class="about-feature" style="margin-bottom:15px;">
                                <span class="feature-icon">🍷</span>
                                <span>Винная карта</span>
                            </div>
                        </div>
                        <a href="about.php" class="btn" style="width:100%;text-align:center;">Узнать больше</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== SPECIAL DISHES (Chef's Picks) ========== -->
    <section class="menu-section" id="specials">
        <div class="container">
            <h2 class="section-title fade-in">Фирменные <span style="color: var(--color-primary);">блюда</span></h2>
            <p class="section-subtitle fade-in">Рекомендация нашего шеф-повара</p>
            <div class="specials-grid" id="specials-grid">
                <!-- Загружается через JS -->
            </div>
        </div>
    </section>

    <!-- ========== STATS ========== -->
    <section class="stats" id="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item fade-in">
                    <div class="stat-number" data-target="15">0</div>
                    <div class="stat-label">Лет опыта</div>
                </div>
                <div class="stat-item fade-in">
                    <div class="stat-number" data-target="250">0</div>
                    <div class="stat-label">Блюд в меню</div>
                </div>
                <div class="stat-item fade-in">
                    <div class="stat-number" data-target="50">0</div>
                    <div class="stat-label">Шеф-поваров</div>
                </div>
                <div class="stat-item fade-in">
                    <div class="stat-number" data-target="98">0</div>
                    <div class="stat-label">% Довольных гостей</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== MENU PREVIEW ========== -->
    <section class="menu-section" id="menu">
        <div class="container">
            <h2 class="section-title fade-in">Популярные <span style="color: var(--color-primary);">блюда</span></h2>
            <p class="section-subtitle fade-in">Попробуйте наши фирменные блюда, которые завоевали сердца гостей</p>
            <div class="dishes-grid" id="popular-dishes">
                <!-- Загружается через JS -->
            </div>
            <div style="text-align: center; margin-top: 50px;">
                <a href="menu.php" class="btn btn-outline">Полное меню</a>
            </div>
        </div>
    </section>

    <!-- ========== TEAM ========== -->
    <section class="team-section" id="team">
        <div class="container">
            <h2 class="section-title fade-in">Наша <span style="color: var(--color-primary);">команда</span></h2>
            <p class="section-subtitle fade-in">Профессионалы, которые создают для вас кулинарные шедевры</p>
            <div class="team-grid" id="team-grid">
                <!-- Загружается через JS -->
            </div>
        </div>
    </section>

    <!-- ========== BOOKING ========== -->
    <section class="booking-section" id="booking">
        <div class="container">
            <div class="booking-wrapper fade-in">
                <div class="booking-info">
                    <h2>Забронируйте <span>столик</span></h2>
                    <p>Заполните форму, и мы свяжемся с вами для подтверждения бронирования.</p>
                    <div class="booking-contacts">
                        <p>📞 <a href="tel:+79991234567">+7 (999) 123-45-67</a></p>
                        <p>📧 <a href="mailto:info@beanscene.ru">info@beanscene.ru</a></p>
                        <p>📍 г. Москва, ул. Тверская, 15</p>
                    </div>
                </div>
                <form class="booking-form" id="booking-form">
                    <div class="form-row">
                        <input type="text" name="name" placeholder="Ваше имя" required>
                        <input type="tel" name="phone" placeholder="Телефон" required>
                    </div>
                    <div class="form-row">
                        <input type="email" name="email" placeholder="Email">
                        <input type="number" name="guests" placeholder="Количество гостей" min="1" max="20" value="2" required>
                    </div>
                    <div class="form-row">
                        <input type="date" name="date" required>
                        <input type="time" name="time" required>
                    </div>
                    <textarea name="comment" placeholder="Особые пожелания (необязательно)"></textarea>
                    <button type="submit" class="btn" style="width: 100%;">Забронировать</button>
                </form>
            </div>
        </div>
    </section>

    <!-- ========== REVIEWS ========== -->
    <section class="reviews" id="reviews">
        <div class="container">
            <h2 class="section-title fade-in">Отзывы <span style="color: var(--color-primary);">гостей</span></h2>
            <p class="section-subtitle fade-in">Что говорят о нас наши посетители</p>
            <div class="reviews-grid" id="reviews-grid">
                <!-- Загружается через JS -->
            </div>
        </div>
    </section>

    <!-- ========== GALLERY ========== -->
    <section class="gallery-section" id="gallery">
        <div class="container">
            <h2 class="section-title fade-in">Наша <span style="color: var(--color-primary);">галерея</span></h2>
            <p class="section-subtitle fade-in">Атмосфера нашего ресторана в фотографиях</p>
            <div class="gallery-grid" id="gallery-grid">
                <!-- Загружается через JS -->
            </div>
        </div>
    </section>

    <!-- ========== NEWS ========== -->
    <section class="menu-section" id="news">
        <div class="container">
            <h2 class="section-title fade-in">Новости и <span style="color: var(--color-primary);">акции</span></h2>
            <p class="section-subtitle fade-in">Будьте в курсе последних событий и спецпредложений</p>
            <div class="news-grid" id="news-grid">
                <!-- Загружается через JS -->
            </div>
        </div>
    </section>

    <!-- ========== NEWSLETTER ========== -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-wrapper fade-in">
                <h2>Подпишитесь на <span>новости</span></h2>
                <p>Получайте информацию о новых блюдах, акциях и мастер-классах первыми</p>
                <form class="newsletter-form" id="newsletter-form">
                    <input type="email" placeholder="Ваш email" required>
                    <button type="submit" class="btn">Подписаться</button>
                </form>
            </div>
        </div>
    </section>

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
