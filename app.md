# ТЗ: Noterdam — self-hosted сервис заметок (v4, финал)

> **Что в v4 относительно v3**
>
> 1. **Фронт зафиксирован: Inertia.js** (не классический SPA, не Livewire). Первый рендер — HTML с сервера, навигация — XHR с `X-Inertia` + JSON-ответом компонента. Роутинг на Laravel.
> 2. **Auth упрощён**: для браузера — обычная Laravel-сессия (Sanctum-мидлвара как fallback). `SANCTUM_STATEFUL_DOMAINS`, `/sanctum/csrf-cookie`, `SESSION_DOMAIN` — **исключены из ТЗ как лишние**. Sanctum используется **только** для personal access tokens на `/api/*`.
> 3. **Traefik**: entrypoint `web`, без `tls`/`certresolver`. Middleware `noterdam-proto` (`X-Forwarded-Proto: https`) — **обязателен**, обоснование ниже.
> 4. **Keenetic**: шаг `ip http proxy <name> preserve-host` вынесен в README как блокирующий чек-лист развёртывания.
> 5. **Раздел 15 «Принятые по умолчанию решения»** — все пункты, которые были вопросами, зафиксированы с явными значениями и указанием, где их менять.

---

## 1. Общее описание

**Noterdam** — self-hosted сервис проектной документации и заметок в Markdown с вложениями, разграничением доступа и шарингом проектов между пользователями.

- Название: **Noterdam**, репозиторий `noterdam`, папка на сервере `~/apps/noterdam`
- Язык интерфейса: русский (код и БД — английский)
- Тема: тёмная по умолчанию, светлая опционально, режим «системная»
- **Сетевая модель** (ваша инфраструктура, уже работает):

  ```
  Браузер ──HTTPS──► Keenetic (TLS terminate, preserve-host)
                     │
                     ──HTTP──► Traefik :80, entrypoint=web
                                │
                                ──HTTP──► noterdam-app:80 (FrankenPHP, worker)
  ```

  Приложение **не обслуживает TLS**, не содержит сертификатов, не делает ACME. Считает себя HTTPS-приложением за счёт `X-Forwarded-Proto: https`, выставляемого Traefik-middleware.

---

## 2. Технологический стек и архитектура фронта

**Backend**: PHP 8.3, Laravel 11, Octane + FrankenPHP (worker mode), Breeze (Inertia-стек), MySQL 8.0, Filesystem `local`, Pest. Sanctum — только для PAT на `/api/*`.

**Frontend**: Inertia.js 2, Vue 3 `<script setup>`, TypeScript, TailwindCSS 3 + typography, shadcn-vue, Lucide, `md-editor-v3` (или CodeMirror 6 + markdown-it), `markdown-it` + `highlight.js` + `DOMPurify`, Vite 5.

### 2.1 Архитектура фронта — Inertia (modern monolith)

- **Первый запрос** → Laravel отдаёт Blade-корень `app.blade.php` с `<div id="app" data-page="{...}">`.
- **Переходы** → Inertia перехватывает, шлёт XHR с `X-Inertia: true`, Laravel возвращает JSON `{component, props, url}`, Vue **заменяет только страницу**.
- **Роутинг** — только в `routes/web.php`. Vue Router **не используется**.
- **Auth** — Laravel session cookie (`SESSION_SECURE_COOKIE=true`, HttpOnly, SameSite=Lax). CSRF — стандартный `VerifyCsrfToken`.
- **Никаких**: `vue-router`, `pinia` для серверных данных (только локальный UI-стейт), Bearer-токенов в браузере, `/sanctum/csrf-cookie`, отдельного REST-слоя для браузера.
- **`/api/*`** — отдельный namespace, те же контроллеры или ресурсные обёртки, auth через `auth:sanctum` + PAT. Используется для скриптов и будущих интеграций.

---

## 3. Роли и права

`project_user`:

| Роль | Права |
|---|---|
| `owner` | Полный контроль: редактирование/удаление проекта, управление участниками, CRUD заметок и вложений |
| `editor` | CRUD заметок и вложений |
| `viewer` | Чтение заметок, скачивание вложений |

Доступ к заметке наследуется от проекта. Гость — только `/login` и `/register` (регистрация открытая).

---

## 4. Модель данных

**users** — id, name, email (unique), password, remember_token, settings (json, nullable), is_admin (bool, default false), timestamps

**projects** — id, owner_id (FK users, cascade), name(120), slug(unique), description, color, is_archived(bool), timestamps, softDeletes

**project_user** — id, project_id, user_id, role enum('owner','editor','viewer'), timestamps, unique(project_id, user_id)

**notes** — id, project_id (FK cascade), author_id (FK users, set null), title(200), content(longtext), content_html(longtext nullable), is_pinned(bool), timestamps, softDeletes; индекс `(project_id, updated_at)`

**note_attachments** — id, note_id (FK cascade), uploaded_by (FK users, nullable), original_name, storage_path, mime_type, size, checksum_sha256(64), timestamps

**tags** — id, name(unique), color, timestamps

**note_tag** — note_id, tag_id, unique(note_id, tag_id)

**personal_access_tokens** — стандартная Sanctum.

**invites** — id, code (unique), email (nullable), created_by (FK users), used_at, expires_at, timestamps (устарела; больше не используется для регистрации)

---

## 5. Функционал

### 5.1 Аутентификация

- **Регистрация открытая**: любой гость может создать аккаунт по URL `/register`. Первый пользователь создаётся artisan-командой `php artisan user:create` (name, email, password интерактивно или через options) и получает `is_admin=true`.
- Login (email/пароль, rate limit 5/мин), logout.
- Профиль: имя, email, пароль.
- Sanctum PAT — в настройках (`/settings/tokens`), используются только для `/api/*`.
- Восстановление пароля — не требуется.

### 5.2 Проекты

CRUD, список (владелец + участник), фильтр «активные/архив», карточка (имя, описание, автор, кол-во заметок, дата обновления), архивация, soft-delete с каскадом (файлы вычищаются cron-очисткой раз в сутки), смена акцентного цвета.

### 5.3 Участники

Приглашение по email (пользователь должен существовать), смена роли, удаление, самостоятельный выход (не для owner), список в UI.

### 5.4 Заметки

CRUD внутри проекта. Редактор split/preview (на мобильных — только редактор). Toolbar: bold/italic/strike/inline-code/codeblock/h1–h3/lists/quote/link/image/table. Хоткеи: `Ctrl/Cmd+B`, `I`, `K`, `S`, `Shift+Enter`. Автосейв 30 с + индикатор «Сохранено / Есть несохранённые». Sidebar с поиском, сортировка, pin. Теги + фильтр. Вставка изображений из буфера → авто-загрузка. Soft-delete. Хлебные крошки.

### 5.5 Вложения

Drag-and-drop + кнопка. Лимиты: 50 МБ/файл, 500 МБ/заметка (настраивается через env). Blacklist расширений: `php, phtml, phar, exe, sh, bat, cmd, html`. MIME через `finfo`. Скачивание — только участникам проекта. Удаление — автор/owner. Блок под заметкой: имя, размер, дата, кнопка скачать.

### 5.6 Поиск

`LIKE '%q%'` по `notes.title` + `notes.content`, scoped по проектам пользователя. Глобальный и внутри проекта. Подсветка сниппета. (При росте до >5k заметок — миграция на FULLTEXT ngram, вне MVP.)

### 5.7 Прочее

Тема: dark/light/system (persisted в `users.settings` + `localStorage`). Toast (`vue-sonner`). 404 в стиле приложения.

---

## 6. Маршруты

### Web (Inertia, `web` middleware)

```
GET  /register  POST /register    — открытая регистрация
GET  /login             POST /login       — rate limit 5/мин
POST /logout

GET  /                                    — dashboard
GET  /projects
POST /projects
GET  /projects/{project}                  — проект + первая заметка / пустое состояние
PUT  /projects/{project}
DELETE /projects/{project}
POST /projects/{project}/archive
POST /projects/{project}/members
PUT  /projects/{project}/members/{user}
DELETE /projects/{project}/members/{user}
POST /projects/{project}/leave

GET  /projects/{project}/notes            — список + выбранная
POST /projects/{project}/notes
GET  /projects/{project}/notes/{note}
GET  /projects/{project}/notes/{note}/edit
PUT  /projects/{project}/notes/{note}
DELETE /projects/{project}/notes/{note}

POST   /projects/{project}/notes/{note}/attachments
DELETE /attachments/{attachment}
GET    /attachments/{attachment}/download
GET    /attachments/{attachment}/inline    — для изображений в markdown

GET  /search?q=
GET  /settings/profile   PUT
PUT  /settings/password
GET  /settings/tokens
POST /settings/tokens
DELETE /settings/tokens/{token}

# admin (is_admin=true)
GET  /admin/invites      POST /admin/invites
DELETE /admin/invites/{invite}
```

### API (JSON, `auth:sanctum` + PAT)

```
/api/projects               — список/создание
/api/projects/{id}          — чтение/обновление/удаление
/api/projects/{id}/notes    — список/создание
/api/notes/{id}             — чтение/обновление/удаление
/api/attachments/{id}       — скачивание
```

---

## 7. UI/UX

### 7.1 Визуал

Минимализм, тонкие границы, много воздуха.

**Тёмная (default)**: фон `#0B0F14`, поверхности `#11161D`/`#161C24`, границы `#232A33`, текст `#E6EAF0`, muted `#8A94A6`, акцент **emerald-400 `#34D399`**. Семантика: rose-400 / emerald-400 / amber-400.

**Светлая**: фон `#FAFAFA`, поверхности `#FFFFFF`, текст `slate-900`, акцент emerald-600.

Скругления: `rounded-lg` (кнопки), `rounded-xl` (карточки), `rounded-2xl` (модалки). Шрифты: Inter + JetBrains Mono (код). Иконки Lucide 16–20 px, stroke 1.75. Анимации 150 ms.

### 7.2 Экраны

1. **Login/Register** — центрированная карточка, логотип «Noterdam», CTA emerald.
2. **Dashboard** — grid карточек, шапка с поиском, «Новый проект», аватар.
3. **Project view** — sidebar 280 px (список + поиск) + контент заметки; сверху название, аватары участников, «Поделиться», `⋯`.
4. **Note editor** — split (редактор | превью), топбар «Сохранить» + статус, нижняя панель с тегами/вложениями.
5. **Settings** — Профиль / Безопасность / Токены / Внешний вид.
6. **Share modal** — участники + форма «email + роль».
7. **Admin** — только для `is_admin`: инвайты.

### 7.3 Адаптив

Tailwind брейкпоинты. Мобильные: sidebar в drawer, редактор в режиме «только редактор» + вкладка «Превью».

### 7.4 A11y

`ring-emerald-400/40` на фокусе, `aria-label` для иконочных кнопок, контраст ≥ WCAG AA.

---

## 8. Работа за Keenetic + Traefik (критично)

### 8.1 Поток запроса

Браузер → `https://noterdam.<domain>` → Keenetic (TLS terminate, `preserve-host`) → `http://192.168.1.101:80` → Traefik (entrypoint `web`, docker-provider) → `http://noterdam-app:80` в сети `traefik` → FrankenPHP.

Traefik видит входящий запрос **как `http`** (роутер уже расшифровал) и по умолчанию пробрасывает `X-Forwarded-Proto: http`. Без исправления:

- `Request::isSecure()` → `false`
- Абсолютные URL в Inertia/редиректах → `http://`
- Куки без `Secure`, CSRF-проверка на `https`-origin может поехать

**Обязательный middleware** в Traefik-labels сервиса `app`:

```yaml
- traefik.http.middlewares.noterdam-proto.headers.customrequestheaders.X-Forwarded-Proto=https
- traefik.http.routers.noterdam.middlewares=noterdam-proto
```

**Не использовать** `URL::forceScheme('https')` в приложении как единственное решение — `Request::isSecure()` всё равно останется `false`, Inertia получит неверный origin. Middleware в Traefik — основной и единственный механизм.

### 8.2 TrustProxies

```php
// app/Http/Middleware/TrustProxies.php
protected $proxies = null; // читается из config через env TRUSTED_PROXIES
protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO;
```

`TRUSTED_PROXIES` в `.env` — подсеть docker-сети `traefik` или fallback `172.16.0.0/12,10.0.0.0/8,192.168.0.0/16` (покрывает все типичные Docker/LAN подсети). Парсинг в `AppServiceProvider::boot()`.

### 8.3 Конфиги приложения

- `APP_URL=https://noterdam.<domain>`
- `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax`
- **Не задавать** `SESSION_DOMAIN` (совпадает с host, дефолт ок), **не задавать** `SANCTUM_STATEFUL_DOMAINS` (не нужен — мы не делаем cross-origin SPA), **не задавать** `ASSET_URL`
- `SANCTUM_TOKEN_PREFIX` — пусто

### 8.4 Keenetic `preserve-host` (обязательный шаг развёртывания)

```
ssh admin@192.168.1.1
(config)> show ip http proxy
(config)> ip http proxy <имя-прокси-noterdam> preserve-host
(config)> system configuration save
```

Без этого Traefik увидит `Host: 192.168.1.101`, правило `Host(\`noterdam.<domain>\`)` не сматчится → **404**. README должен это чётко блокировать как обязательный пункт.

---

## 9. Docker и инфраструктура

### 9.1 Структура репозитория

```
noterdam/
├── .docker/
│   ├── Caddyfile
│   ├── php/{php.ini,opcache.ini}
│   └── entrypoint.sh
├── app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ tests/
├── docker/
│   ├── Dockerfile
│   ├── compose.dev.yaml
│   └── compose.prod.yaml
├── scripts/backup.sh
├── .env.example
├── Makefile
├── composer.json  package.json  vite.config.ts
└── README.md
```

### 9.2 Dockerfile (multi-stage)

Стадии:

1. `base` — `dunglas/frankenphp:php8.3-alpine` + icu, mysql-client, zip, git.
2. `vendor` — `composer install --no-dev --optimize-autoloader --classmap-authoritative`.
3. `assets` — `node:20-alpine`, `npm ci && npm run build`.
4. `dev` — `FROM base`, bind-mount, Xdebug, `frankenphp run` (без worker).
5. `prod` — `FROM base`, копирование + `vendor` + `public/build`, non-root `app`, `ENTRYPOINT ["/usr/local/bin/frankenphp"]`, `CMD ["run","--config","/etc/frankenphp/Caddyfile","--worker","/app/public/index.php"]`.

Prod: non-root, `HEALTHCHECK curl -f http://localhost/up`, `php artisan config:cache route:cache view:cache event:cache` на entrypoint, `opcache.validate_timestamps=0`.

### 9.3 Caddyfile

```
{
  auto_https off
  admin off
  frankenphp {
    worker /app/public/index.php { num {$FRANKENPHP_WORKERS:8} }
  }
}

:80 {
  encode zstd gzip
  root * /app/public
  php_server
  log { output stdout, format console }
}
```

### 9.4 `docker/compose.prod.yaml`

```yaml
services:
  app:
    build:
      context: ..
      dockerfile: docker/Dockerfile
      target: prod
    image: noterdam:prod
    container_name: noterdam-app
    restart: unless-stopped
    env_file: ../.env
    expose: [80]
    volumes:
      - ../storage:/app/storage
    networks: [internal, traefik]
    depends_on:
      db: { condition: service_healthy }
    labels:
      traefik.enable: "true"
      traefik.docker.network: "traefik"
      traefik.http.routers.noterdam.rule: "Host(`${APP_DOMAIN}`)"
      traefik.http.routers.noterdam.entrypoints: "web"
      traefik.http.routers.noterdam.middlewares: "noterdam-proto"
      traefik.http.services.noterdam.loadbalancer.server.port: "80"
      traefik.http.middlewares.noterdam-proto.headers.customrequestheaders.X-Forwarded-Proto: "https"

  db:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes: [db_data:/var/lib/mysql]
    networks: [internal]
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-p${DB_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 10

  scheduler:
    image: noterdam:prod
    container_name: noterdam-scheduler
    restart: unless-stopped
    command: php artisan schedule:work
    env_file: ../.env
    volumes: [../storage:/app/storage]
    networks: [internal]
    depends_on: [db, app]

networks:
  internal:
  traefik:
    external: true
    name: traefik

volumes:
  db_data:
```

**Запрещено**: `ports:`, проброс `:80/:443` на хост, `tls=true`, `certresolver`, `websecure`. Всё это — сфера Keenetic/Traefik.

> **Альтернатива file-provider**: если позже перейдёшь с labels на файлы Traefik, роутер должен указывать на `http://noterdam-app:80` в сети `traefik` + middleware с тем же `X-Forwarded-Proto`. README описывает оба варианта.

### 9.5 `docker/compose.dev.yaml`

- `app` — bind-mount `.:/app`, Xdebug, `ports: 8000:80` (прямой доступ в обход Traefik).
- `vite` — `node:20-alpine`, `npm run dev -- --host 0.0.0.0`, `ports: 5173:5173`.
- `db` — MySQL, `ports: 3306:3306` (опционально).

Без TLS, без Traefik, без `mailpit` (email в MVP не используется).

### 9.6 Makefile

```
make up / down / build / prod-up / migrate / test / lint / artisan c="..." / logs / shell / backup
```

`make backup` — вызывает `scripts/backup.sh`.

### 9.7 `.env.example`

```
APP_NAME=Noterdam
APP_ENV=production
APP_KEY=
APP_URL=https://noterdam.mukashev.netcraze.pro
APP_DOMAIN=noterdam.mukashev.netcraze.pro

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=noterdam
DB_USERNAME=noterdam
DB_PASSWORD=
DB_ROOT_PASSWORD=

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Подсеть docker-сети traefik или fallback:
TRUSTED_PROXIES=172.16.0.0/12,10.0.0.0/8,192.168.0.0/16

FILESYSTEM_DISK=local
ATTACHMENT_MAX_SIZE=52428800
ATTACHMENT_MAX_TOTAL=524288000

REGISTRATION_MODE=open
BACKUP_RETENTION_DAYS=14

OCTANE_SERVER=frankenphp
FRANKENPHP_WORKERS=8

LOG_CHANNEL=stack
LOG_LEVEL=info
```

---

## 10. Разработка, CI/CD, документация

- Pint (PHP), ESLint + Prettier (TS/Vue). Pest.
- Feature-тесты: auth (открытая регистрация), policies (owner/editor/viewer), upload (limits, blacklist, finfo), share, search, **`X-Forwarded-Proto: https` → `Request::isSecure() === true`, `URL::to('/')` начинается с `https://`, Inertia-редирект отдаёт `https://`-URL**.
- CI-заготовки: `.github/workflows/ci.yml` (lint + test + build assets), `.github/workflows/build.yml` (сборка/пуш `noterdam:prod` в registry).
- `.editorconfig`, `.gitignore` (исключить `storage/app/attachments/*`, `storage/logs/*`, `.env`, `node_modules`, `vendor`, `public/build`).
- **`scripts/backup.sh`**: `mysqldump` (через `docker compose exec -T db`) → `tar czf` по `storage/app/attachments` → в `./backups/` с датой, ротация `BACKUP_RETENTION_DAYS`. Возвращает ненулевой код при ошибке — чтобы на хосте повесить на cron.

### README (русский) — обязательные разделы

1. Локальный запуск (`compose.dev.yaml`).
2. **Развёртывание на сервере** — `~/apps/noterdam`, `docker compose -f docker/compose.prod.yaml up -d --build`, первичный `php artisan user:create` для админа.
3. **Настройка Traefik** — entrypoint `web`, middleware `noterdam-proto` (обязателен, с обоснованием), второй вариант через file-provider.
4. **Настройка Keenetic** — `ip http proxy <name> preserve-host` для `noterdam.<domain>` с примером команд из `services.md`. **Пометить как блокирующий шаг**.
5. **Проверка**: `curl -I -H 'Host: noterdam.mukashev.netcraze.pro' http://192.168.1.101` → `200/302`, не `404`; `docker logs traefik` показывает роутер `noterdam@docker`.
6. **Бэкапы** — `make backup` + cron на хосте.
7. **Обновление** — pull → build → up, `octane:reload`.
8. **Регистрация** — открытая регистрация по `/register`; первый админ через `php artisan user:create`; приглашение в проект существующих пользователей.

---

## 11. Нефункциональные требования

- Отклик < 300 мс при 1k заметок.
- FrankenPHP старт < 1 с, лимит памяти контейнера app — 512 MB.
- Файлы > 50 MB отклоняются до отправки (frontend pre-check + серверный `max:`).
- Изображения из вложений — через markdown-ссылки на `/attachments/{id}/inline`, `Cache-Control: private, max-age=86400`.
- `octane:reload` перезапускает воркеры при деплое.
- Приложение не содержит TLS-сертификатов, не слушает `:443`, не делает ACME.

---

## 12. Deliverables

1. Laravel-проект со всеми фичами § 5.
2. `docker/Dockerfile` (multi-stage: `dev` / `prod`).
3. `docker/compose.dev.yaml`, `docker/compose.prod.yaml` (external `traefik`, `expose: [80]`, без портов и TLS).
4. `.docker/Caddyfile`, `.docker/entrypoint.sh`, `.docker/php/*.ini`.
5. `.env.example`.
6. `Makefile`, `scripts/backup.sh`.
7. README на русском с блоками Traefik/Keenetic/бэкапы.
8. Seeder: демо-пользователь + 1 проект + 3 заметки + теги.
9. Pest-тесты (включая `X-Forwarded-Proto` → https, auth-flow, policies).
10. Artisan-команды: `user:create`, `invite:create`.
11. Скриншоты/описание UI по § 7.

---

## 13. Вне MVP

Per-note ACL, версионирование заметок, realtime, OAuth, 2FA, восстановление пароля по email, комментарии, публичные read-only ссылки, S3, WYSIWYG, отправка писем.

---

## 14. Критерии приёмки

1. `docker compose -f docker/compose.prod.yaml up -d --build` поднимает стек; `app` не публикует порты, в сети `traefik`, доступен Traefik по `app:80`.
2. FrankenPHP работает в worker mode (видно по логам).
3. `curl -I -H 'Host: noterdam.mukashev.netcraze.pro' http://192.168.1.101` → `200/302`, не `404`.
4. `https://noterdam.mukashev.netcraze.pro`: login проходит, ссылки `https://`, кука сессии `Secure`.
5. Контейнер `app` не слушает `:443`, сертификатов внутри нет.
6. Регистрация доступна без инвайта; после регистрации пользователь попадает на dashboard.
7. Два пользователя в общем проекте: owner приглашает editor, editor создаёт заметку, viewer видит, но не редактирует.
8. Загрузка 40 МБ проходит, 60 МБ — отклоняется с понятной ошибкой.
9. Markdown с внедрённым `<script>` не исполняется в превью.
10. Тёмная тема соответствует § 7.
11. `make test` — зелёный; `make build` — образ < 300 MB.
12. `make backup` создаёт `backups/db-YYYYMMDD.sql.gz` и `backups/storage-YYYYMMDD.tar.gz`.

---

## 15. Принятые по умолчанию решения

Перечислены все пункты, которые были открытыми вопросами. Если что-то не подходит — правка точечная, ТЗ перегенерировать не нужно.

| # | Вопрос | Принятое значение | Где менять |
|---|---|---|---|
| 1 | Домен | `noterdam.mukashev.netcraze.pro` (по аналогии с `hello.*` из ваших доков) | `APP_URL`, `APP_DOMAIN` в `.env`; Traefik `Host(...)`; `preserve-host` на Keenetic |
| 2 | Политика регистрации | **open**: любой гость может зарегистрироваться по `/register`; первый админ создаётся через `php artisan user:create` | `REGISTRATION_MODE=open` в `.env` (переменная оставлена для совместимости) |
| 3 | Traefik-провайдер | **docker-provider с labels** (как в вашем `hello`) | README описывает file-provider как альтернативу |
| 4 | Подсеть `traefik` | неизвестна; дефолт `TRUSTED_PROXIES=172.16.0.0/12,10.0.0.0/8,192.168.0.0/16` — покрывает все типичные Docker/LAN | сузить до конкретной подсети после `docker network inspect traefik` |
| 5 | Масштаб | 1–10 пользователей, сотни заметок | при росте — миграция поиска на FULLTEXT ngram, индексы уже заложены |
| 6 | Email | **не используется** — нет писем, нет `mailpit`, нет SMTP в `.env` | — |
| 7 | Вложения | **bind mount** `../storage:/app/storage` в prod; volume — только `db_data` | compose.prod.yaml |
| 8 | Sanctum PAT | **оставляем в MVP** — удобно для скриптов | удалить `/settings/tokens` из UI и `/api/*` из роутов |
| 9 | Роль `viewer` | **оставляем** | удалить из enum + UI Share modal |
| 10 | i18n | **только русский**, строки через `lang/ru/*.php`, переключателя языков нет | — |
| 11 | Бэкапы | **включить `scripts/backup.sh`** + `make backup`, cron на хосте — на стороне пользователя | `BACKUP_RETENTION_DAYS` в `.env` |
| 12 | Тема | **dark / light / system**, по умолчанию dark | — |
| 13 | Лимиты файлов | 50 МБ/файл, 500 МБ/заметка | `ATTACHMENT_MAX_SIZE`, `ATTACHMENT_MAX_TOTAL` в `.env` |
| 14 | Поиск | **`LIKE '%q%'`** для MVP, FULLTEXT ngram — вне MVP | замена `Note::scopeSearch` |

Всё остальное — как в § 1–14.
