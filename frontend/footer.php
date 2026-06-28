    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>Точка Кипения</h4>
                    <p>Ресторан премиум-кухни. Мы создаём незабываемые гастрономические впечатления для наших гостей. Свежие продукты, авторские рецепты, уютная атмосфера.</p>
                    <div class="social-links">
                        <a href="#" title="Instagram">📷</a>
                        <a href="#" title="VK">💬</a>
                        <a href="#" title="Telegram">✈️</a>
                        <a href="#" title="YouTube">▶️</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Навигация</h4>
                    <ul>
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="about.php">О нас</a></li>
                        <li><a href="menu.php">Меню</a></li>
                        <li><a href="promotions.php">Акции</a></li>
                        <li><a href="news.php">Новости</a></li>
                        <li><a href="contact.php">Контакты</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Меню</h4>
                    <ul>
                        <li><a href="menu.php">Салаты</a></li>
                        <li><a href="menu.php">Супы</a></li>
                        <li><a href="menu.php">Горячие блюда</a></li>
                        <li><a href="menu.php">Десерты</a></li>
                        <li><a href="menu.php">Напитки</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Контакты</h4>
                    <ul>
                        <li><a href="tel:+79991234567">+7 (999) 123-45-67</a></li>
                        <li><a href="mailto:info@beanscene.ru">info@beanscene.ru</a></li>
                        <li>г. Москва, ул. Тверская, 15</li>
                        <li>Пн–Вс: 10:00 – 23:00</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Точка Кипения. Все права защищены. Сделано с ❤️</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js?v=2"></script>
    <script>
    function toggleMobileMenu() {
        var dropdown = document.getElementById('mobileMenuDropdown');
        var btn = document.getElementById('burgerBtn');
        if (dropdown) dropdown.classList.toggle('open');
        if (btn) btn.classList.toggle('open');
        // Close footer modal if open
        var fm = document.getElementById('footerModal');
        var fb = document.getElementById('footerBurger');
        if (fm && fm.classList.contains('active')) {
            fm.classList.remove('active');
            if (fb) fb.classList.remove('open');
        }
    }
    
    function toggleFooterMenu() {
        var m = document.getElementById('footerModal');
        var b = document.getElementById('footerBurger');
        if (m) m.classList.toggle('active');
        if (b) b.classList.toggle('open');
        document.body.style.overflow = m && m.classList.contains('active') ? 'hidden' : '';
        // Close top dropdown if open
        var dd = document.getElementById('mobileMenuDropdown');
        var tb = document.getElementById('burgerBtn');
        if (dd && dd.classList.contains('open')) {
            dd.classList.remove('open');
            if (tb) tb.classList.remove('open');
        }
    }
    
    function openAboutModal() {
        document.getElementById('aboutModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeAboutModal() {
        document.getElementById('aboutModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    </script>
    <!-- ========== FOOTER MOBILE ========== -->
    <div class="footer-mobile-bar">
        <button class="footer-burger" id="footerBurger" onclick="toggleFooterMenu()" aria-label="Меню">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </button>
        <span class="footer-mobile-copy">© 2026 Точка Кипения</span>
    </div>
    <div class="footer-modal" id="footerModal" onclick="if(event.target===this) toggleFooterMenu()">
        <div class="footer-modal-panel">
            <div class="footer-modal-header">
                <span>☰ Меню</span>
                <button onclick="toggleFooterMenu()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--color-text);padding:5px;">✕</button>
            </div>

            <div class="footer-modal-section">
                <h4>Точка Кипения</h4>
                <p style="color:var(--color-text-light);font-size:0.85rem;line-height:1.5;margin-bottom:10px;">Ресторан премиум-кухни. Свежие продукты, авторские рецепты, уютная атмосфера.</p>
                <div style="display:flex;gap:10px;font-size:1.3rem;">
                    <a href="#" style="text-decoration:none;">📷</a>
                    <a href="#" style="text-decoration:none;">💬</a>
                    <a href="#" style="text-decoration:none;">✈️</a>
                    <a href="#" style="text-decoration:none;">▶️</a>
                </div>
            </div>

            <div class="footer-modal-section">
                <h4>Навигация</h4>
                <ul class="footer-modal-links">
                    <li><a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>" onclick="toggleFooterMenu()">Главная</a></li>
                    <li><a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>" onclick="toggleFooterMenu()">О нас</a></li>
                    <li><a href="menu.php" class="<?= $current_page === 'menu.php' ? 'active' : '' ?>" onclick="toggleFooterMenu()">Меню</a></li>
                    <li><a href="promotions.php" class="<?= $current_page === 'promotions.php' ? 'active' : '' ?>" onclick="toggleFooterMenu()">Акции</a></li>
                    <li><a href="news.php" class="<?= $current_page === 'news.php' ? 'active' : '' ?>" onclick="toggleFooterMenu()">Новости</a></li>
                    <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>" onclick="toggleFooterMenu()">Контакты</a></li>
                </ul>
            </div>

            <div class="footer-modal-section">
                <h4>Категории меню</h4>
                <ul class="footer-modal-links">
                    <li><a href="menu.php" onclick="toggleFooterMenu()">Салаты</a></li>
                    <li><a href="menu.php" onclick="toggleFooterMenu()">Супы</a></li>
                    <li><a href="menu.php" onclick="toggleFooterMenu()">Горячие блюда</a></li>
                    <li><a href="menu.php" onclick="toggleFooterMenu()">Десерты</a></li>
                    <li><a href="menu.php" onclick="toggleFooterMenu()">Напитки</a></li>
                </ul>
            </div>

            <div class="footer-modal-section">
                <h4>Контакты</h4>
                <ul class="footer-modal-links">
                    <li><a href="tel:+79991234567">📞 +7 (999) 123-45-67</a></li>
                    <li><a href="mailto:info@beanscene.ru">✉️ info@beanscene.ru</a></li>
                    <li style="padding:8px 0;color:var(--color-text-light);font-size:0.9rem;">📍 г. Москва, ул. Тверская, 15</li>
                    <li style="padding:8px 0;color:var(--color-text-light);font-size:0.9rem;">🕐 Пн–Вс: 10:00 – 23:00</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
