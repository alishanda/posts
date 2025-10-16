#!/bin/bash

echo "🚀 Запуск Posts API приложения..."

# Проверяем, что Docker запущен
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker не запущен. Пожалуйста, запустите Docker и попробуйте снова."
    exit 1
fi

# Создаем .env файл если его нет
if [ ! -f .env ]; then
    echo "📝 Создание .env файла..."
    cp .env.example .env
fi

# Останавливаем контейнеры если они запущены
echo "🛑 Остановка существующих контейнеров..."
docker-compose down

# Собираем и запускаем контейнеры
echo "🔨 Сборка и запуск Docker контейнеров..."
docker-compose up -d --build

# Ждем пока база данных будет готова
echo "⏳ Ожидание готовности базы данных..."
sleep 10

# Устанавливаем зависимости
echo "📦 Установка PHP зависимостей..."
docker-compose exec app composer install --no-interaction

# Генерируем ключ приложения
echo "🔑 Генерация ключа приложения..."
docker-compose exec app php artisan key:generate --force

# Запускаем миграции
echo "🗄️ Запуск миграций базы данных..."
docker-compose exec app php artisan migrate --force

# Создаем символическую ссылку для storage
echo "🔗 Создание символической ссылки для storage..."
docker-compose exec app php artisan storage:link

# Генерируем Swagger документацию
echo "📚 Генерация Swagger документации..."
docker-compose exec app php artisan l5-swagger:generate

# Запускаем тесты
echo "🧪 Запуск тестов..."
docker-compose exec app php artisan test

echo ""
echo "✅ Приложение успешно запущено!"
echo ""
echo "🌐 API доступно по адресу: http://localhost:8000/api"
echo "📚 Swagger документация: http://localhost:8000/api/documentation"
echo "🗄️ База данных MySQL: localhost:3306"
echo "📨 Redis: localhost:6379"
echo ""
echo "📋 Полезные команды:"
echo "  docker-compose logs -f app     # Просмотр логов приложения"
echo "  docker-compose exec app bash   # Вход в контейнер приложения"
echo "  docker-compose exec app php artisan test  # Запуск тестов"
echo "  docker-compose down            # Остановка контейнеров"
echo ""
echo "📁 Импортируйте файл postman_collection.json в Postman для тестирования API"
echo ""
