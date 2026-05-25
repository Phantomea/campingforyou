# John's Garage — Docker príkazy
.PHONY: up down build restart logs shell-backend shell-frontend migrate seed fresh artisan composer \
        dev dev-down dev-build dev-logs dev-migrate dev-fresh dev-shell-backend dev-shell-frontend dev-shell-db

# ─── PRODUKCIA (docker-compose.yml) ──────────────────────────────────────────

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

build-frontend:
	docker compose build --no-cache frontend && docker compose up -d frontend

restart:
	docker compose restart

logs:
	docker compose logs -f

logs-backend:
	docker compose logs -f backend

artisan:
	docker compose exec backend php artisan $(cmd)

migrate:
	docker compose exec backend php artisan migrate

seed:
	docker compose exec backend php artisan db:seed

fresh:
	docker compose exec backend php artisan migrate:fresh --seed

composer:
	docker compose exec backend composer $(cmd)

shell-backend:
	docker compose exec backend sh

shell-frontend:
	docker compose exec frontend sh

shell-db:
	docker compose exec db mariadb -u ${DB_USERNAME:-johnsgarage} -p${DB_PASSWORD:-secret} ${DB_DATABASE:-johnsgarage}

# ─── DEV (docker-compose.dev.yml) ────────────────────────────────────────────

dev:
	docker compose -f docker-compose.dev.yml up -d

dev-down:
	docker compose -f docker-compose.dev.yml down

dev-build:
	docker compose -f docker-compose.dev.yml build --no-cache

dev-restart:
	docker compose -f docker-compose.dev.yml restart

dev-logs:
	docker compose -f docker-compose.dev.yml logs -f

dev-logs-backend:
	docker compose -f docker-compose.dev.yml logs -f backend

dev-artisan:
	docker compose -f docker-compose.dev.yml exec backend php artisan $(cmd)

dev-migrate:
	docker compose -f docker-compose.dev.yml exec backend php artisan migrate

dev-fresh:
	docker compose -f docker-compose.dev.yml exec backend php artisan migrate:fresh --seed

dev-composer:
	docker compose -f docker-compose.dev.yml exec backend composer $(cmd)

dev-shell-backend:
	docker compose -f docker-compose.dev.yml exec backend sh

dev-shell-frontend:
	docker compose -f docker-compose.dev.yml exec frontend sh

dev-shell-db:
	docker compose -f docker-compose.dev.yml exec db mariadb -u ${DB_USERNAME:-johnsgarage} -p${DB_PASSWORD:-secret} ${DB_DATABASE:-johnsgarage}
