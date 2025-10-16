#!/bin/bash

# Posts API - Команды для разработки

case "$1" in
    "start")
        echo "🚀 Запуск приложения..."
        docker-compose up -d
        ;;
    "stop")
        echo "🛑 Остановка приложения..."
        docker-compose down
        ;;
    "restart")
        echo "🔄 Перезапуск приложения..."
        docker-compose restart
        ;;
    "build")
        echo "🔨 Пересборка контейнеров..."
        docker-compose up -d --build
        ;;
    "logs")
        echo "📋 Просмотр логов..."
        docker-compose logs -f app
        ;;
    "shell")
        echo "🐚 Вход в контейнер приложения..."
        docker-compose exec app bash
        ;;
    "migrate")
        echo "🗄️ Запуск миграций..."
        docker-compose exec app php artisan migrate
        ;;
    "fresh")
        echo "🗄️ Пересоздание базы данных..."
        docker-compose exec app php artisan migrate:fresh
        ;;
    "seed")
        echo "🌱 Заполнение базы данных тестовыми данными..."
        docker-compose exec app php artisan db:seed
        ;;
    "test")
        echo "🧪 Запуск тестов..."
        docker-compose exec app php artisan test
        ;;
    "test-coverage")
        echo "📊 Запуск тестов с покрытием..."
        docker-compose exec app php artisan test --coverage
        ;;
    "swagger")
        echo "📚 Генерация Swagger документации..."
        docker-compose exec app php artisan l5-swagger:generate
        ;;
    "clear")
        echo "🧹 Очистка кэша..."
        docker-compose exec app php artisan cache:clear
        docker-compose exec app php artisan config:clear
        docker-compose exec app php artisan route:clear
        docker-compose exec app php artisan view:clear
        ;;
    "install")
        echo "📦 Установка зависимостей..."
        docker-compose exec app composer install
        docker-compose exec app npm install
        ;;
    "update")
        echo "🔄 Обновление зависимостей..."
        docker-compose exec app composer update
        ;;
    "status")
        echo "📊 Статус контейнеров..."
        docker-compose ps
        ;;
    "db")
        echo "🗄️ Подключение к базе данных..."
        docker-compose exec db mysql -u root -p posts_tz
        ;;
    "redis")
        echo "📨 Подключение к Redis..."
        docker-compose exec redis redis-cli
        ;;
    *)
        echo "Posts API - Команды для разработки"
        echo ""
        echo "Использование: ./commands.sh [команда]"
        echo ""
        echo "Доступные команды:"
        echo "  start        - Запуск приложения"
        echo "  stop         - Остановка приложения"
        echo "  restart      - Перезапуск приложения"
        echo "  build        - Пересборка контейнеров"
        echo "  logs         - Просмотр логов приложения"
        echo "  shell        - Вход в контейнер приложения"
        echo "  migrate      - Запуск миграций"
        echo "  fresh        - Пересоздание базы данных"
        echo "  seed         - Заполнение базы тестовыми данными"
        echo "  test         - Запуск тестов"
        echo "  test-coverage- Запуск тестов с покрытием"
        echo "  swagger      - Генерация Swagger документации"
        echo "  clear        - Очистка кэша"
        echo "  install      - Установка зависимостей"
        echo "  update       - Обновление зависимостей"
        echo "  status       - Статус контейнеров"
        echo "  db           - Подключение к базе данных"
        echo "  redis        - Подключение к Redis"
        echo ""
        echo "Примеры:"
        echo "  ./commands.sh start"
        echo "  ./commands.sh logs"
        echo "  ./commands.sh test"
        ;;
esac
