.PHONY: up down dev dev-build build prod-up dev-restart prod-restart migrate test lint artisan logs shell backup

up:
	docker compose -f docker/compose.dev.yaml up -d --build

# App + DB в Docker, Vite — на хосте (нужен Node.js).
dev: up
	npm run dev

down:
	docker compose -f docker/compose.dev.yaml down
	rm -f public/hot

dev-build:
	docker compose -f docker/compose.dev.yaml build

build:
	docker compose -f docker/compose.prod.yaml build

prod-up:
	docker compose -f docker/compose.prod.yaml up -d --build

dev-restart:
	@echo "Restarting dev environment..."
	docker compose -f docker/compose.dev.yaml down
	rm -f public/hot
	docker compose -f docker/compose.dev.yaml up -d --build --wait
	docker compose -f docker/compose.dev.yaml exec -T app php artisan optimize:clear
	docker run --rm -v $(CURDIR):/app -w /app node:20-alpine npm run build
	@echo ""
	@echo "--- Dev environment status ---"
	docker compose -f docker/compose.dev.yaml ps
	@curl -sf http://localhost/up && echo "Health check: OK" || echo "Health check: FAIL"

prod-restart:
	@echo "Restarting prod environment..."
	docker compose -f docker/compose.prod.yaml down
	docker compose -f docker/compose.prod.yaml up -d --build --wait
	docker compose -f docker/compose.prod.yaml exec -T app php artisan migrate --force
	docker compose -f docker/compose.prod.yaml exec -T app php artisan optimize:clear
	docker compose -f docker/compose.prod.yaml exec -T app php artisan optimize
	@echo ""
	@echo "--- Prod environment status ---"
	docker compose -f docker/compose.prod.yaml ps
	@docker compose -f docker/compose.prod.yaml exec -T app curl -sf http://localhost/up && echo "Health check: OK" || echo "Health check: FAIL"

migrate:
	docker compose -f docker/compose.prod.yaml exec app php artisan migrate --force

test:
	docker compose -f docker/compose.dev.yaml run --rm --no-deps app php artisan test

lint:
	docker run --rm -v $(CURDIR):/app -w /app composer:2 ./vendor/bin/pint
	docker run --rm -v $(CURDIR):/app -w /app node:20-alpine npm run lint

artisan:
	docker compose -f docker/compose.prod.yaml exec app php artisan $(c)

logs:
	docker compose -f docker/compose.prod.yaml logs -f app

shell:
	docker compose -f docker/compose.prod.yaml exec app sh

backup:
	bash scripts/backup.sh
