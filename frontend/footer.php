    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>Bean Scene</h4>
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
                <p>&copy; 2026 Bean Scene. Все права защищены. Сделано с ❤️</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js?v=2"></script>
    <script>
    function openAboutModal() {
        document.getElementById('aboutModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeAboutModal() {
        document.getElementById('aboutModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleMobileNav() {
        document.querySelector('.nav').classList.toggle('mobile-open');
        document.getElementById('navOverlay').classList.toggle('active');
        document.body.style.overflow = document.querySelector('.nav').classList.contains('mobile-open') ? 'hidden' : '';
    }
    </script>
</body>
</html>
