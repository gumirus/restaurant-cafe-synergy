#!/bin/bash
# =============================================
# Генерация скриншотов кода для отчёта
# =============================================
# Использование: bash generate_screenshots.sh
# Требует: brew install highlight
# =============================================

OUTPUT_DIR="screenshots"
mkdir -p "$OUTPUT_DIR"

# Цветовая схема
THEME="github"

echo "📸 Генерация скриншотов кода..."

# Frontend
echo "  → HTML..."
highlight -O html -t 4 -s $THEME ../frontend/index.html -o "$OUTPUT_DIR/index.html.html"
highlight -O html -t 4 -s $THEME ../frontend/about.html -o "$OUTPUT_DIR/about.html.html"
highlight -O html -t 4 -s $THEME ../frontend/menu.html -o "$OUTPUT_DIR/menu.html.html"

echo "  → CSS..."
highlight -O html -t 4 -s $THEME ../frontend/css/style.css -o "$OUTPUT_DIR/style.css.html"
highlight -O html -t 4 -s $THEME ../frontend/css/color.css -o "$OUTPUT_DIR/color.css.html"

echo "  → JS..."
highlight -O html -t 4 -s $THEME ../frontend/js/main.js -o "$OUTPUT_DIR/main.js.html"
highlight -O html -t 4 -s $THEME ../frontend/js/filter.js -o "$OUTPUT_DIR/filter.js.html"
highlight -O html -t 4 -s $THEME ../frontend/js/faq.js -o "$OUTPUT_DIR/faq.js.html"
highlight -O html -t 4 -s $THEME ../frontend/js/cart.js -o "$OUTPUT_DIR/cart.js.html"
highlight -O html -t 4 -s $THEME ../frontend/js/validation.js -o "$OUTPUT_DIR/validation.js.html"
highlight -O html -t 4 -s $THEME ../frontend/js/slider.js -o "$OUTPUT_DIR/slider.js.html"

# Backend
echo "  → PHP..."
highlight -O html -t 4 -s $THEME ../backend/config/db.php -o "$OUTPUT_DIR/db.php.html"
highlight -O html -t 4 -s $THEME ../backend/config/session.php -o "$OUTPUT_DIR/session.php.html"
highlight -O html -t 4 -s $THEME ../backend/login.php -o "$OUTPUT_DIR/login.php.html"
highlight -O html -t 4 -s $THEME ../backend/register.php -o "$OUTPUT_DIR/register.php.html"
highlight -O html -t 4 -s $THEME ../backend/cart_add.php -o "$OUTPUT_DIR/cart_add.php.html"
highlight -O html -t 4 -s $THEME ../backend/checkout.php -o "$OUTPUT_DIR/checkout.php.html"
highlight -O html -t 4 -s $THEME ../backend/createProduct.php -o "$OUTPUT_DIR/createProduct.php.html"
highlight -O html -t 4 -s $THEME ../backend/upload.php -o "$OUTPUT_DIR/upload.php.html"
highlight -O html -t 4 -s $THEME ../backend/found.php -o "$OUTPUT_DIR/found.php.html"

# Admin
echo "  → Admin..."
highlight -O html -t 4 -s $THEME ../backend/admin/index.php -o "$OUTPUT_DIR/admin_index.php.html"

# Employee
echo "  → Employee..."
highlight -O html -t 4 -s $THEME ../backend/employee/index.php -o "$OUTPUT_DIR/employee_index.php.html"

echo ""
echo "✅ Готово! Скриншоты в папке $OUTPUT_DIR/"
echo "📂 Откройте .html файлы в браузере и сделайте скриншоты PrintScreen"
