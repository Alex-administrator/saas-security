# SaaS Security

Портируемый базовый проект на PHP 8.3 + MySQL 8.4 + Nginx/PHP-FPM.

Проект готов к переносу на другой сервер:
- настройки среды хранятся в `.env`
- публичный веб-корень ограничен папкой `public/`
- запуск через Docker Compose
- воркер запускается отдельным контейнером
- эндпоинты проверки доступности: `/health` и `/ready`

## Деплой одной командой

Для Linux-сервера основной процесс автоматизирован скриптом [`scripts/deploy.sh`](scripts/deploy.sh).

Запуск из корня проекта:

```bash
bash scripts/deploy.sh --app-url http://your-server:8080
```

Пример для реального домена по HTTP на порту `8080`:

```bash
bash scripts/deploy.sh --app-url http://localhost.ru:8080
```

Пример для деплоя за внешним HTTPS-прокси:

```bash
bash scripts/deploy.sh --app-url https://example.com --port 8080 --env production --debug false
```

## Что делает скрипт деплоя

Скрипт автоматически:
- создаёт `.env` из `.env.example` (если есть), иначе создаёт минимальный `.env`
- заполняет `APP_URL`, `APP_PORT`, `APP_ENV`, `DB_*`, `SESSION_SECURE_COOKIE`, `APP_KEY`
- генерирует случайные `DB_PASSWORD` и `SEED_ADMIN_PASSWORD`, если они отсутствуют или слабые
- перезаписывает `docker/nginx/default.conf` до заведомо рабочей конфигурации
- нормализует права доступа к файловой системе, включая корень проекта
- запускает стек через Docker Compose
- ожидает готовности `db`, `app`, `nginx` и `worker`
- выполняет миграции
- выполняет сидеры (если не отключено)
- запускает smoke-тест
- пересоздаёт воркер после миграций
- проверяет `/health` и `/ready`

Скрипт покрывает проблемы, с которыми уже сталкивались при реальном деплое:
- несовместимость MySQL 8.4 с `default-authentication-plugin=mysql_native_password`
- сломанный nginx-конфиг после ручного редактирования
- ошибка `File not found.` из PHP-FPM из-за прав доступа на примонтированный каталог
- падение воркера до миграций — таблица `jobs` ещё не существует

## Параметры скрипта деплоя

```bash
bash scripts/deploy.sh [параметры]
```

Доступные параметры:
- `--app-url URL` — публичный URL приложения, например `http://server:8080`
- `--port PORT` — порт хоста для nginx, по умолчанию `8080`
- `--env NAME` — значение `APP_ENV`, по умолчанию `production`
- `--debug true|false` — значение `APP_DEBUG`, по умолчанию `false`
- `--db-name NAME` — имя базы данных, по умолчанию `saas_security`
- `--db-user NAME` — пользователь БД, по умолчанию `saas`
- `--db-password VALUE` — пароль БД; если не указан, скрипт генерирует случайный
- `--reset-db` — пересоздать контейнеры и удалить том с базой перед деплоем
- `--no-seed` — пропустить `php console.php seed`
- `--no-build` — пропустить пересборку образа
- `--help` — показать справку

## Ручной запуск

Если нужно запустить всё вручную:

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php console.php migrate
docker compose exec app php console.php seed
```

Затем открыть:

```text
http://localhost:8080
```

## Проверка работоспособности

После успешного запуска:

- [http://localhost:8080/health](http://localhost:8080/health)
- [http://localhost:8080/ready](http://localhost:8080/ready)

## Учётные данные администратора

- email по умолчанию: `admin@example.com`, или значение `SEED_ADMIN_EMAIL` из `.env`
- пароль берётся из `SEED_ADMIN_PASSWORD`; скрипт деплоя генерирует случайное значение и сохраняет его в `.env`
- после первого входа смените учётные данные

### Установка своего пароля администратора

Задайте любую строку прямо в `.env`:

```env
SEED_ADMIN_EMAIL=admin@example.com
SEED_ADMIN_PASSWORD=МойПароль2024!
```

После изменения `SEED_ADMIN_PASSWORD` примените:

```bash
docker compose exec app php console.php seed
```

Для входа используйте тот же пароль, что написан в `SEED_ADMIN_PASSWORD`.

## Остановка приложения

Временная остановка без удаления контейнеров:

```bash
docker compose stop
```

Полная остановка с удалением контейнеров:

```bash
docker compose down
```

Полная остановка с удалением тома базы данных:

```bash
docker compose down -v
```

## Сервисы

- `app` — PHP-FPM, среда выполнения приложения
- `nginx` — точка входа для веб-трафика
- `db` — MySQL 8.4
- `worker` — воркер очереди задач

## HTTPS через Nginx + Let's Encrypt

Внутренний стек работает по HTTP. Для HTTPS нужен внешний Nginx как обратный прокси.

### 1. Установка Nginx и Certbot

```bash
apt install -y nginx certbot python3-certbot-nginx
```

### 2. Конфигурация Nginx

Создай файл `/etc/nginx/sites-available/saas-security`:

```nginx
server {
    listen 80;
    server_name твой-домен.ru;

    location /.well-known/acme-challenge/ { root /var/www/certbot; }
    location / { return 301 https://$host$request_uri; }
}

server {
    listen 443 ssl;
    server_name твой-домен.ru;

    ssl_certificate     /etc/letsencrypt/live/твой-домен.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/твой-домен.ru/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    location / {
        proxy_pass         http://127.0.0.1:8080;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto https;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/saas-security /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

### 3. Получение сертификата

```bash
certbot --nginx -d твой-домен.ru
```

Certbot сам вставит пути к сертификату в конфиг.

### 4. Обновить `.env`

```env
APP_URL=https://твой-домен.ru
SESSION_SECURE_COOKIE=true
```

Применить:

```bash
docker compose up -d --force-recreate app
```

### 5. Автообновление сертификата

Certbot добавляет таймер systemd автоматически. Проверить:

```bash
systemctl status certbot.timer
```

---

## Тесты

Запустить все тесты (стек должен быть запущен):

```bash
bash tests/run-tests.sh
```

Или отдельный тест:

```bash
docker compose exec app php tests/Unit/ValidatorTest.php
docker compose exec app php tests/Unit/PasswordStrengthTest.php
docker compose exec app php tests/Unit/RateLimiterTest.php
docker compose exec app php tests/Integration/LoginCsrfTest.php
```

---

## Примечания

- Если `APP_URL` использует `https://`, TLS должен терминироваться внешним обратным прокси (Traefik, Caddy, Nginx). Внутренний стек работает по HTTP на указанном порту.
- При публичном доступе к приложению используйте HTTPS — без него логин и куки сессий передаются в открытом виде.
- Для продакшен-деплоя установите:

```env
APP_ENV=production
APP_DEBUG=false
```

- Если приложение работает за HTTPS, также добавьте:

```env
SESSION_SECURE_COOKIE=true
```
