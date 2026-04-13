# 03_best_of_best_prompt

## Назначение
Ниже — улучшенный «best of best» master prompt для генерации production-ready SaaS-проекта в области security awareness, defensive tooling и compliance. Он сохраняет сильные стороны исходной идеи, но убирает опасные формулировки, добавляет multi-tenant архитектуру, эксплуатационную зрелость и чёткие стандарты качества.

---

# MASTER PROMPT

Ты — lead architect / principal fullstack engineer.

Твоя задача: сгенерировать **полностью рабочий production-ready multi-tenant SaaS-проект** в области **security awareness, defensive cyber tooling, internal security training, privacy and compliance**.

## Абсолютные требования к ответу
- Пиши **полный код**, а не фрагменты.
- Не используй заглушки, `TODO`, `mock`, `pseudo-code`.
- Каждый файл выводи отдельно в формате:

```text
=== FILE: path ===
<full file content>
```

- Все файлы должны быть согласованы между собой.
- Код должен запускаться локально после установки зависимостей.
- Архитектура должна быть чистой, расширяемой и пригодной к сопровождению.
- Любая функция, связанная с симуляциями, обучением и сообщениями, должна реализовываться **только как defensive / awareness / consent-based feature**, без сбора чувствительных данных и без функциональности, которая может быть использована как инструмент злоупотребления.

---

## Цель продукта
Построить SaaS-платформу для организаций, которая включает:
- блог и публикации по информационной безопасности;
- календарь security-мероприятий;
- комментарии и реакции сообщества;
- набор defensive security tools;
- AI-модуль для анализа публичных URL и текстов;
- модуль security awareness training;
- модуль безопасных consent-based simulations для обучения сотрудников;
- privacy/compliance-механизмы (в том числе подход, совместимый с 152-ФЗ на уровне продукта и журналирования согласий);
- multi-tenant модель с тарифами и ограничениями функций.

---

## Технологический стек
- PHP **8.3+**
- MySQL **8+**
- PDO
- Vanilla JS или минимальный JS
- HTML/CSS без тяжёлых frontend-фреймворков
- Nginx + PHP-FPM
- Без тяжёлых backend-фреймворков

Если нужно добавить небольшие battle-tested библиотеки, это допустимо только для:
- dotenv/env loading
- UUID
- mailer
- image handling
- testing

---

## Архитектурный стиль
Используй **clean MVC + service layer + repository layer + policies + DTOs**.

Обязательные слои:
- `Controllers`
- `Requests` / validators
- `Services`
- `Repositories`
- `Models`
- `Policies`
- `Middleware`
- `Jobs`
- `Console/Cron`
- `Support` / shared utilities

Нельзя смешивать:
- HTTP-логику
- бизнес-логику
- SQL
- форматирование ответа

Контроллеры должны быть тонкими.

---

## Multi-tenant SaaS model
Система должна быть multi-tenant.

### Обязательные сущности tenancy
- `organizations`
- `organization_users`
- `plans`
- `subscriptions`
- `feature_flags`
- `api_tokens`

### Tenant rules
- Каждая бизнес-сущность должна быть привязана к `organization_id`, если это не global content.
- Доступ к данным должен проверяться через tenant-aware middleware + policy layer.
- Нельзя допускать утечку данных между организациями.
- Все выборки в repositories должны быть tenant-scoped.
- Для admin-level операций должна быть предусмотрена явная проверка роли.

---

## Роли и доступ
Реализуй RBAC.

### Роли
- `super_admin`
- `org_admin`
- `editor`
- `analyst`
- `member`

### Требования
- Admin actions доступны только уполномоченным ролям.
- Для чувствительных действий должна быть re-authentication логика.
- У сессий должна быть ротация.
- Поддержать logout-all-sessions.

---

## Аутентификация
### Обязательные варианты
- email + password
- password reset
- optional magic link
- MFA для admin-ролей

### Security requirements
- `password_hash(..., PASSWORD_ARGON2ID)`
- secure session cookies
- `HttpOnly`, `Secure`, `SameSite=Lax/Strict`
- CSRF для state-changing форм
- brute-force protection
- login rate limiting
- audit log для auth events

---

## Конфигурация и секреты
Не хранить боевые секреты в публично доступных файлах.

### Требования
- Использовать `.env` + env validation на старте.
- Создать `.env.example`.
- Конфиги разнести по доменам:
  - `/config/app.php`
  - `/config/db.php`
  - `/config/ai.php`
  - `/config/telegram.php`
  - `/config/security.php`
- Запретить web access к `/config`, `/storage`, `/vendor`, `/logs`.
- Никогда не логировать секреты.

---

## Структура проекта

```text
/app
  /Controllers
  /Requests
  /Models
  /Repositories
  /Services
  /Policies
  /Middleware
  /Jobs
  /Support
  /Views
/bootstrap
/config
/database
  /migrations
  /seeders
/public
/routes
/storage
  /cache
  /logs
  /uploads
  /queue
/cron
/tests
/docker
```

---

## База данных
Сгенерируй:
- SQL schema
- migrations
- seeders

### Обязательные таблицы
- `users`
- `organizations`
- `organization_users`
- `plans`
- `subscriptions`
- `feature_flags`
- `articles`
- `article_previews`
- `comments`
- `likes`
- `events`
- `consents`
- `consent_versions`
- `audit_logs`
- `app_logs`
- `jobs`
- `job_attempts`
- `failed_jobs`
- `api_tokens`
- `simulation_programs`
- `simulation_targets`
- `simulation_events`
- `telegram_integrations`
- `ai_requests`
- `tool_runs`

### Требования к БД
- foreign keys
- индексы
- unique constraints
- soft delete там, где нужно
- timestamps (`created_at`, `updated_at`)
- UTC в БД
- retention-ready design

---

## Формат API
Все API должны возвращать JSON.

### Success
```json
{
  "success": true,
  "data": {},
  "error": null,
  "meta": {
    "request_id": "req_123"
  }
}
```

### Error
```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "email": ["Invalid email"]
    }
  },
  "meta": {
    "request_id": "req_123"
  }
}
```

### API rules
- versioning: `/api/v1/...`
- pagination format
- consistent HTTP status codes
- idempotency support for critical POST actions
- correlation/request id

---

## Middleware
Обязательные middleware:
- `auth`
- `tenant`
- `rbac`
- `subscription`
- `csrf`
- `rate_limit`
- `request_id`
- `security_headers`

---

## Security baseline
Реализуй baseline по мотивам:
- OWASP ASVS
- OWASP Top 10
- OWASP Cheat Sheets

### Обязательные меры
- prepared statements only
- input validation whitelist-first
- output escaping
- strict file upload validation
- CSP
- CSRF
- XSS protection
- clickjacking protection
- secure session management
- audit logging
- password hashing with Argon2id
- encryption at rest for sensitive integration tokens via AES-256-GCM
- signed URLs for sensitive actions
- SSRF protection for URL-fetching features
- MIME/type/size limits
- request timeouts
- retry with backoff
- safe error handling without leaking internals

---

## Очереди и фоновые задачи
Очереди обязательны для:
- email notifications
- telegram notifications
- AI analysis
- report generation
- digest jobs

### Требования
- `jobs` table-based queue
- worker script
- retries
- exponential backoff
- dead-letter handling via `failed_jobs`
- idempotency keys
- structured job logs

### Cron
Нужны примеры cron-задач:
- queue worker runner
- cleanup expired sessions
- cleanup old logs
- subscription sync
- digest generation
- integration health check

---

## Блог
### Flow
1. `editor` или `org_admin` создаёт статью.
2. Поля:
   - `title`
   - `content`
   - `cover_image`
   - `tags`
   - `status`
3. Генерируется slug.
4. Создаётся draft.
5. Доступен preview по signed preview URL.
6. После publish создаётся публичная статья.

### При публикации
Отправлять уведомление в Telegram-канал организации и/или внутренний notification center:
- title
- excerpt
- image
- safe link

### API
- `POST /api/v1/articles`
- `PUT /api/v1/articles/{id}`
- `POST /api/v1/articles/{id}/preview`
- `POST /api/v1/articles/{id}/publish`
- `GET /api/v1/articles/search`
- `GET /blog/{slug}`

---

## Комментарии и реакции
### Комментарии
- поддержать local account comments
- optional Telegram Login Widget как дополнительный способ идентификации
- anti-spam rate limit
- content moderation hooks
- XSS-safe rendering

### Лайки
- `POST /api/v1/reactions/like`
- защита от дублирования
- учитывать user/session fingerprint безопасным способом
- не опираться только на IP

---

## Календарь
### Возможности
- события создаются в UTC
- отображаются в UTC, Europe/Moscow и local timezone пользователя
- ICS export
- upcoming reminders

### API
- `POST /api/v1/events`
- `PUT /api/v1/events/{id}`
- `GET /api/v1/events`
- `GET /api/v1/events/{id}`

---

## Security awareness and simulation module
Это **не offensive module**. Это модуль **training and awareness**.

### Правила безопасности модуля
- только для организации-владельца
- только для обучающих сценариев
- обязательные legal notice и admin acknowledgement
- никакого сбора реальных паролей, секретов или платёжных данных
- никакого хранения credential logs
- после действия пользователя показывать instant educational feedback page
- собирать только безопасную телеметрию:
  - delivery status
  - open/click awareness events, если это допустимо политикой организации
  - report-rate
  - completion-rate
  - time-to-report
- все сценарии должны иметь policy guardrails и audit trail

### Сущности
- `simulation_programs`
- `simulation_targets`
- `simulation_events`
- `simulation_templates`

### Flow
1. `org_admin` создаёт training campaign.
2. Загружает разрешённый список адресатов своей организации.
3. Выбирает template из безопасной библиотеки.
4. Система валидирует scope, quota и consent flags.
5. Отправка идёт через queue.
6. При взаимодействии пользователя показывается educational landing page.
7. Формируется отчёт для организации.

### API
- `POST /api/v1/simulations`
- `POST /api/v1/simulations/{id}/launch`
- `GET /api/v1/simulations/{id}/report`
- `POST /api/v1/simulations/{id}/close`

---

## Telegram integration
### Возможности
- интеграция Telegram для уведомлений и публикаций
- проверка токена интеграции
- encrypted storage токенов
- health checks
- disable integration on repeated failures

### API
- `POST /api/v1/integrations/telegram`
- `POST /api/v1/integrations/telegram/test`
- `POST /api/v1/integrations/telegram/send`

### Health check
Периодически проверять интеграцию и помечать статусы:
- `active`
- `degraded`
- `invalid`

---

## AI module
### Возможности
- анализ публичного URL
- извлечение текста
- очистка и нормализация
- summarization / risk hints / classification
- fallback на raw extracted text

### Требования безопасности
- SSRF protection
- deny private IP ranges
- deny localhost
- max response size
- MIME validation
- HTML sanitization
- timeout
- circuit breaker
- proxy support через env config
- prompt-injection mitigation notes
- PII minimization before model call

### API
- `POST /api/v1/ai/analyze-url`
- `POST /api/v1/ai/analyze-text`
- `GET /api/v1/ai/requests/{id}`

---

## Defensive tools
### API endpoints
- `POST /api/v1/tools/domain-reputation`
- `POST /api/v1/tools/ssl-inspect`
- `POST /api/v1/tools/email-analyze`
- `POST /api/v1/tools/password-strength`
- `POST /api/v1/tools/url-analyze`

### Требования
- валидация входных данных
- timeouts
- caching
- safe parsing
- structured result format
- audit of tool runs

---

## Privacy / 152-ФЗ / consent
### Обязательные требования
- consent checkbox там, где требуется правовое основание
- versioned consent text
- cookie banner
- privacy policy page
- retention policy page
- consent log:
  - `user_id` / `subject_id`
  - `organization_id`
  - `ip`
  - `user_agent`
  - `consent_version_id`
  - `timestamp`
- support withdrawal where applicable
- legal texts linked from UI

---

## UI/UX
Стиль:
- светлый corporate high-tech
- фон `#f6f8fb`
- карточки
- чистая типографика
- минимализм

### Обязательные состояния
- loading
- empty
- error
- success
- skeletons where useful

### UX требования
- responsive layout
- accessible forms
- server-side rendered views
- reusable UI components
- flash messages
- consistent validation messages

---

## Observability
### Обязательные компоненты
- request ID
- structured JSON logs
- app logs
- audit logs
- security logs
- integration logs
- health endpoint `/health`
- readiness endpoint `/ready`

---

## Кэширование
Реализуй кэширование для:
- frequently accessed settings
- public articles list
- tool results with TTL
- rate-limit counters

Предусмотри fallback на file-based cache при отсутствии Redis.

---

## Тесты и качество
Сгенерируй:
- unit tests
- integration tests
- basic E2E smoke paths
- coding standards config
- static analysis config

### Минимум покрыть тестами
- auth
- article publish flow
- consent logging
- tenant isolation
- AI URL validation
- queue retry behavior
- simulation guardrails

---

## DevOps / local run
Обязательно сгенерируй:
- `Dockerfile`
- `docker-compose.yml`
- nginx config
- php-fpm config if needed
- cron example
- worker entrypoint
- `.env.example`
- `README.md` с пошаговым запуском

---

## Финальный состав ответа
Сгенерируй полностью:
1. все PHP-файлы
2. все view-файлы
3. JS/CSS
4. SQL schema
5. migrations
6. seeders
7. config files
8. routes
9. cron files
10. worker files
11. docker files
12. tests
13. README

---

## Критические запреты
- не создавать offensive functionality
- не собирать реальные credentials
- не хранить чувствительные данные без необходимости
- не делать insecure defaults
- не использовать raw SQL string interpolation
- не оставлять незакрытые security holes

---

## Что делает этот prompt сильнее исходного
- превращает идею в настоящий **multi-tenant SaaS**, а не в просто набор модулей;
- задаёт **RBAC, subscriptions, feature flags, auditability**;
- вводит **операционную зрелость**: jobs, retries, health checks, logs, Docker, tests;
- заменяет опасный phishing/vishing контур на **defensive awareness and training**;
- делает требования к security не списком пожеланий, а **системным baseline**;
- делает проект более реалистичным для генерации рабочего production-grade результата.
