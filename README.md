# Noterdam

Self-hosted сервис проектной документации и заметок в Markdown с вложениями, разграничением доступа и шарингом проектов.

- Стек: PHP 8.3, Laravel 11, Inertia.js 2, Vue 3, TailwindCSS, FrankenPHP, MySQL 8.0.
- Сетевая модель: браузер → HTTPS → Keenetic (TLS terminate, preserve-host) → HTTP → Traefik :80 → HTTP → `noterdam-app:80`.
- Приложение **не обслуживает TLS** и не содержит сертификатов. HTTPS-достиг за счёт middleware `noterdam-proto` в Traefik.

## Содержание

- [Локальный запуск](#локальный-запуск)
- [Развёртывание на сервере](#развёртывание-на-сервере)
- [Настройка Traefik](#настройка-traefik)
- [Настройка Keenetic](#настройка-keenetic)
- [Проверка](#проверка)
- [Бэкапы](#бэкапы)
- [Обновление](#обновление)
- [Приглашения](#приглашения)

## Локальный запуск

Требуется Docker Compose и Node.js 20+ для Vite на хосте.

```bash
cp .env.example .env
# для dev значения APP_URL/APP_DOMAIN/SESSION_SECURE_COOKIE переопределены в compose.dev.yaml
make up          # app + db в Docker
npm ci           # один раз
npm run dev      # Vite на хосте, http://localhost:5173
```

Либо одной командой (app + db в Docker, Vite в текущем терминале):

```bash
make dev
```

- Приложение: http://localhost
- Vite HMR: http://localhost:5173
- БД доступна на localhost:3306

Vite намеренно не запускается в Docker: он пишет `public/hot` в общий bind-mount, и если контейнер с Vite остановлен или упал, Laravel продолжает отдавать ссылки на несуществующий dev-сервер — страница становится пустой. `make down` удаляет `public/hot`.

Одновременно может работать только один Vite. Если при запуске появляется `Port 5173 is already in use`, значит dev-сервер уже запущен в другом терминале — остановите его (`Ctrl+C`) перед повторным `make dev`.

После первого запуска:

```bash
docker compose -f docker/compose.dev.yaml exec app php artisan migrate
docker compose -f docker/compose.dev.yaml exec app php artisan storage:link
docker compose -f docker/compose.dev.yaml exec app php artisan user:create
```

## Развёртывание на сервере

Рабочая папка: `~/apps/noterdam`.

```bash
cd ~/apps/noterdam
cp .env.example .env
# заполни .env: APP_URL, APP_DOMAIN, DB_PASSWORD, DB_ROOT_PASSWORD, APP_KEY
nano .env

docker compose -f docker/compose.prod.yaml up -d --build

# первичная миграция, разрешения и создание администратора
docker compose -f docker/compose.prod.yaml exec app php artisan migrate --force
docker compose -f docker/compose.prod.yaml exec app php artisan storage:link
docker compose -f docker/compose.prod.yaml exec app php artisan user:create
```

Контейнер `app` не публикует порты, работает в сети `traefik` и доступен Traefik по `noterdam-app:80`.

## Настройка Traefik

### Вариант 1: docker-provider с labels (рекомендуется)

Убедись, что внешняя сеть `traefik` создана:

```bash
docker network create traefik
```

Labels в `docker/compose.prod.yaml` уже настроены. Главное — middleware `noterdam-proto`, который выставляет `X-Forwarded-Proto: https`:

```yaml
traefik.http.middlewares.noterdam-proto.headers.customrequestheaders.X-Forwarded-Proto: "https"
traefik.http.routers.noterdam.middlewares: "noterdam-proto"
```

**Почему это обязательно:** Traefik получает запрос по HTTP от Keenetic и по умолчанию пробрасывает `X-Forwarded-Proto: http`. Без middleware Laravel будет считать, что работает по HTTP: `Request::isSecure()` вернёт `false`, абсолютные URL в Inertia/редиректах станут `http://`, а cookie сессии не получит флаг `Secure`.

### Вариант 2: file-provider

```yaml
# traefik/dynamic/noterdam.yaml
http:
  routers:
    noterdam:
      rule: "Host(`noterdam.mukashev.netcraze.pro`)"
      entrypoints: ["web"]
      service: noterdam
      middlewares: [noterdam-proto]
  services:
    noterdam:
      loadBalancer:
        servers:
          - url: "http://noterdam-app:80"
  middlewares:
    noterdam-proto:
      headers:
        customRequestHeaders:
          X-Forwarded-Proto: "https"
```

## Настройка Keenetic

⚠️ **Блокирующий шаг.** Без него Traefik увидит `Host: 192.168.1.101` и правило `Host(...)` не сработает → 404.

Подключись по SSH к роутеру:

```bash
ssh admin@192.168.1.1
(config)> show ip http proxy
(config)> ip http proxy <имя-прокси-noterdam> preserve-host
(config)> system configuration save
```

Убедись, что HTTPS-прокси для `noterdam.<domain>` ведёт на `http://192.168.1.101:80`.

## Проверка

С хоста, где запущен Traefik:

```bash
curl -I -H 'Host: noterdam.mukashev.netcraze.pro' http://192.168.1.101
# HTTP/1.1 200 OK или 302
```

Должен быть `200`/`302`, не `404`. В логах Traefik должен появиться роутер `noterdam@docker`.

В браузере:

- Открой `https://noterdam.mukashev.netcraze.pro`.
- Login должен проходить.
- Все ссылки должны начинаться с `https://`.
- Cookie сессии должна иметь флаг `Secure`.

## Бэкапы

Ручной бэкап:

```bash
make backup
```

Создаёт:

- `backups/db-YYYYMMDD_HHMMSS.sql.gz`
- `backups/storage-YYYYMMDD_HHMMSS.tar.gz`

Ротация по `BACKUP_RETENTION_DAYS` (по умолчанию 14 дней).

Добавь в cron на хосте (например, раз в сутки в 3:00):

```cron
0 3 * * * cd ~/apps/noterdam && /usr/bin/make backup >> /var/log/noterdam-backup.log 2>&1
```

## Обновление

```bash
cd ~/apps/noterdam
git pull

docker compose -f docker/compose.prod.yaml up -d --build

docker compose -f docker/compose.prod.yaml exec app php artisan migrate --force
```

## Регистрация

Регистрация открытая — любой пользователь может создать аккаунт по адресу:

```
https://noterdam.mukashev.netcraze.pro/register
```

### Регистрация первого пользователя

```bash
docker compose -f docker/compose.prod.yaml exec app php artisan user:create
```

Пользователь получает `is_admin=true`.

### Приглашение в проект

В проекте нажми **Поделиться**, введи email существующего пользователя и выбери роль:

- `owner` — полный контроль
- `editor` — создание и редактирование заметок
- `viewer` — чтение и скачивание вложений

## Makefile

| Команда | Описание |
|---|---|
| `make up` | Поднять dev-стек |
| `make down` | Остановить dev-стек |
| `make dev-restart` | Перезапустить dev-стек с очисткой кеша и пересборкой фронтенда |
| `make build` | Собрать prod-образ |
| `make prod-up` | Поднять prod-стек с пересборкой |
| `make prod-restart` | Перезапустить prod-стек с миграциями, очисткой и оптимизацией кеша |
| `make migrate` | Выполнить миграции в prod |
| `make test` | Запустить Pest |
| `make lint` | Pint + ESLint/Prettier |
| `make artisan c="..."` | Выполнить artisan-команду в prod-контейнере |
| `make logs` | Смотреть логи app |
| `make shell` | Попасть в shell контейнера app |
| `make backup` | Запустить бэкап |

## Лицензия

MIT
