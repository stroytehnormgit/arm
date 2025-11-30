# ARM - Система управления перечнями работ по разработке ТНПА

Веб-приложение для ведения действующего и планируемого перечня работ по разработке технических нормативных правовых актов (ТНПА) в области архитектурной, градостроительной и строительной деятельности.

## Функциональность

- **Действующий перечень** - управление текущими работами с фильтрацией по типу разработки, этапу, организации
- **Планируемый перечень** - создание и редактирование предложений с расчетом стоимости, привязкой к этапам и блокам
- **Файлы** - управление загруженными файлами, файлами на сайте, МВС, интеграция с stn.by API
- **Отчеты** - этапы за период и календарные планы
- **Архив** - архивные записи по годам
- **Администрирование** - управление пользователями, этапами работ, параметрами стоимости
- **Экспорт** - экспорт планируемого перечня в Word документ

## Технологии

- Backend: Laravel 12, PHP 8.2+
- Frontend: Vue 3, TypeScript, Inertia.js, Tailwind CSS
- База данных: SQLite (по умолчанию), поддерживается MySQL/PostgreSQL
- Дополнительно: Laravel Fortify (аутентификация), Spatie Permissions (роли), PHPWord (экспорт)

## Требования

- PHP 8.2 или выше
- Composer
- Node.js 18+ и npm
- SQLite (встроен в PHP) или MySQL/PostgreSQL

## Установка

### 1. Клонирование репозитория

```bash
git clone https://github.com/stroytehnormgit/arm.git
cd arm
```

### 2. Установка зависимостей PHP

```bash
composer install
```

### 3. Настройка окружения

Создайте файл `.env` из примера:

```bash
cp .env.example .env
```

Откройте `.env` и настройте:

```env
APP_NAME="ARM"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

Для MySQL/PostgreSQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arm
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Генерация ключа приложения

```bash
php artisan key:generate
```

### 5. Создание базы данных

Для SQLite (по умолчанию):

```bash
touch database/database.sqlite
```

Для MySQL/PostgreSQL создайте базу данных вручную.

### 6. Запуск миграций и сидеров

```bash
php artisan migrate --seed
```

Сидеры создадут:
- Роли: `admin`, `employee`
- Разрешения для всех модулей
- Начальные этапы работ
- Архивные данные

### 7. Установка зависимостей Node.js

```bash
npm install
```

### 8. Сборка фронтенда

Для разработки:

```bash
npm run dev
```

Для продакшена:

```bash
npm run build
```

### 9. Запуск сервера

В отдельном терминале:

```bash
php artisan serve
```

Приложение доступно по адресу: `http://localhost:8000`

## Первый пользователь

После запуска миграций создайте первого администратора:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Администратор',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
$user->assignRole('admin');
```

Или используйте регистрацию на `/register` (если включена в `config/fortify.php`).

## Разработка

### Запуск в режиме разработки

Запустите одновременно:

1. Laravel сервер:
```bash
php artisan serve
```

2. Vite dev server:
```bash
npm run dev
```

Или используйте готовый скрипт:

```bash
composer run dev
```

### Структура проекта

- `app/Http/Controllers` - контроллеры
- `app/Models` - модели Eloquent
- `app/Services` - бизнес-логика
- `resources/js/pages` - Vue страницы
- `resources/js/components` - Vue компоненты
- `database/migrations` - миграции БД
- `database/seeders` - сидеры

### Команды

```bash
# Очистка кэша
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Запуск тестов
php artisan test

# Форматирование кода
./vendor/bin/pint
npm run format
```

## Конфигурация

### Интеграция с stn.by

В `.env` добавьте:

```env
STNBY_API_URL=http://stn.by/admin/api
STNBY_API_KEY=your_api_key
STNBY_CACHE_TTL=600
```

### Настройка почты

Для отправки уведомлений настройте в `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Разрешения

Система использует Spatie Permissions. Основные разрешения:

- `active-list.*` - управление действующим перечнем
- `planned-list.*` - управление планируемым перечнем
- `files.*` - управление файлами
- `reports.*` - управление отчетами
- `users.manage` - управление пользователями
- `stages.manage` - управление этапами

Роли:
- `admin` - полный доступ
- `employee` - ограниченный доступ по отделам и блокам

## Проблемы и решения

### Ошибка "SQLite database not found"

Убедитесь, что файл `database/database.sqlite` существует и доступен для записи.

### Ошибка "Class not found"

Выполните:

```bash
composer dump-autoload
php artisan config:clear
```

### Ошибка при сборке фронтенда

Удалите `node_modules` и переустановите:

```bash
rm -rf node_modules package-lock.json
npm install
```

### Проблемы с правами доступа

Убедитесь, что папки `storage` и `bootstrap/cache` доступны для записи:

```bash
chmod -R 775 storage bootstrap/cache
```

## Лицензия

MIT

