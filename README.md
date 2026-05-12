# Каталог товаров (Laravel + Inertia + Vue)

## Требования

- PHP **8.3+**
- [Composer](https://getcomposer.org/)
- Node.js **18+** и npm
- База данных: **PostgreSQL** (или используйте [Laravel Sail](https://laravel.com/docs/sail))


В `.env` указать параметры подключения к БД (`DB_*`).

**Создать таблицы и заполнить демо-данными** (миграции + сидеры):

```bash
php artisan db:refresh-seed
```

Команда выполняет `migrate:fresh --seed`: все таблицы пересоздаются, затем вызывается `DatabaseSeeder` (тестовый пользователь, категории, товары).


## Тестовый вход (после `db:refresh-seed`)

Сидер `DatabaseSeeder` создаёт пользователя:

| Поле    | Значение           |
|---------|--------------------|
| Email   | `test@example.com` |
| Пароль  | `password`         |

Подходит для:

- входа через веб-интерфейс;
- API: `POST /api/login` с телом `{"email":"test@example.com","password":"password"}`.


## Docker (Laravel Sail)

Если используете Sail:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan db:refresh-seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
./vendor/bin/sail artisan serve
```

Порты и хост БД смотрите в `docker-compose.yml` и `.env`.
