#!/bin/bash
# =============================================
# ЗАПУСК САЙТА Точка Кипения
# Доступен по локальной сети (Wi-Fi роутер)
# =============================================

PORT=8000
DIR="$(cd "$(dirname "$0")" && pwd)"

echo "☕ Точка Кипения — Запуск сервера"
echo "================================"
echo ""

# Определяем IP в локальной сети
IP=$(ipconfig getifaddr en0 2>/dev/null || ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | head -1)

if [ -z "$IP" ]; then
    IP="127.0.0.1"
fi

# Создаём симлинки для доступа к frontend из корня
cd "$DIR"
ln -sf frontend/css css 2>/dev/null
ln -sf frontend/js js 2>/dev/null
ln -sf frontend/images images 2>/dev/null
ln -sf frontend/uploads uploads 2>/dev/null

# Симлинки для PHP-страниц frontend
for f in frontend/*.php; do
    basename=$(basename "$f")
    [ ! -e "$basename" ] && ln -sf "$f" "$basename" 2>/dev/null
done

echo "📍 Локальный IP: $IP"
echo ""
echo "🌐 Сайт:          http://$IP:$PORT"
echo "🔧 Админ-панель:  http://$IP:$PORT/backend/admin/index.php"
echo "👔 Панель сотр.:  http://$IP:$PORT/backend/employee/index.php"
echo ""
echo "📱 На телефоне или другом ПК в той же сети:"
echo "   http://$IP:$PORT"
echo ""
echo "⚠️  Нажми Ctrl+C для остановки сервера"
echo "================================"
echo ""

php -S 0.0.0.0:$PORT -t "$DIR"
