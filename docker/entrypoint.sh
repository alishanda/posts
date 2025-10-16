#!/bin/bash

set -e

echo "🚀 Инициализация Laravel приложения..."

# Ждем пока база данных будет готова
echo "⏳ Ожидание готовности базы данных..."
until php artisan tinker --execute="DB::connection()->getPdo();" >/dev/null 2>&1; do
    echo "База данных не готова, ожидание..."
    sleep 2
done

echo "✅ База данных готова!"

# Устанавливаем зависимости если vendor не существует
if [ ! -d "vendor" ]; then
    echo "📦 Установка PHP зависимостей..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Создаем .env файл если его нет
if [ ! -f .env ]; then
    echo "📝 Создание .env файла..."
    cp .env.example .env
    echo "✅ .env файл создан"
fi

# Генерируем ключ приложения если его нет
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Генерация ключа приложения..."
    php artisan key:generate --force
    echo "✅ Ключ приложения сгенерирован"
fi

# Запускаем миграции
echo "🗄️ Запуск миграций базы данных..."
php artisan migrate --force
echo "✅ Миграции выполнены"

# Устанавливаем правильные права доступа для storage
echo "🔧 Настройка прав доступа для storage..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Создаем символическую ссылку для storage если её нет
if [ ! -L public/storage ]; then
    echo "🔗 Создание символической ссылки для storage..."
    php artisan storage:link
    echo "✅ Символическая ссылка создана"
fi

# Генерируем Swagger документацию
echo "📚 Генерация Swagger документации..."
php artisan l5-swagger:generate >/dev/null 2>&1 || echo "⚠️ Swagger документация не сгенерирована (возможно, пакет не установлен)"
echo "✅ Swagger документация готова"

# Очищаем кэш
echo "🧹 Очистка кэша..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ Кэш очищен"

# Запускаем тесты (опционально)
if [ "$RUN_TESTS" = "true" ]; then
    echo "🧪 Запуск тестов..."
    php artisan test
    echo "✅ Тесты выполнены"
fi

echo ""
echo "🎉 Laravel приложение успешно инициализировано!"
echo ""
echo "🌐 API доступно по адресу: http://localhost:8000/api"
echo "📚 Swagger документация: http://localhost:8000/api/documentation"
echo ""

# Устанавливаем правильные права доступа для storage
echo "🔧 Настройка прав доступа для storage..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Запускаем PHP-FPM
echo "🚀 Запуск PHP-FPM..."
exec php-fpm
