# Deployment Guide — Coolify + Cloudflare + Docker Compose

## Cloudflare

- SSL/TLS nastaviť na **Flexible**
- DNS záznamy: A record → IP servera, **Proxied** (oranžový mrak) pre obe domény
- Platí pre `frontend.domena.sk` aj `backend.domena.sk`

## docker-compose.yml

**Žiadny vlastný Traefik** — Coolify má `coolify-proxy`, stačí:

```yaml
labels:
  - traefik.http.services.PROJEKTNAME-backend.loadbalancer.server.port=8000
  - traefik.docker.network=coolify
```

### Pravidlá

- **Unikátne Traefik service mená** pre každý projekt — nikdy len `backend`/`frontend`, vždy s prefixom projektu (`johnsgarage-backend`, `buildz-backend`)
- **Žiadne TLS/certresolver labels** — Cloudflare rieši SSL
- `NUXT_API_BASE_SERVER: http://backend:8000/api` — SSR volania idú cez Docker sieť, nie cez verejnú doménu
- MariaDB healthcheck vždy so `start_period: 60s`

### Vzor pre nový projekt

```yaml
services:

  db:
    image: mariadb:11
    restart: unless-stopped
    environment:
      MARIADB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MARIADB_DATABASE: ${DB_DATABASE}
      MARIADB_USER: ${DB_USERNAME}
      MARIADB_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mariadb-admin", "ping", "-h", "localhost", "--silent"]
      interval: 10s
      timeout: 5s
      retries: 10
      start_period: 60s

  backend:
    build:
      context: .
      dockerfile: docker/php/Dockerfile.prod
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    labels:
      - traefik.http.services.PROJEKTNAME-backend.loadbalancer.server.port=8000
      - traefik.docker.network=coolify
    environment:
      APP_KEY: ${APP_KEY}
      # ... ostatné env vars

  frontend:
    build:
      context: .
      dockerfile: docker/node/Dockerfile.prod
    restart: unless-stopped
    depends_on:
      - backend
    labels:
      - traefik.http.services.PROJEKTNAME-frontend.loadbalancer.server.port=3000
      - traefik.docker.network=coolify
    environment:
      NUXT_API_BASE_SERVER: http://backend:8000/api
      NUXT_PUBLIC_API_BASE: https://backend.domena.sk/api
      NUXT_PUBLIC_BACKEND_URL: https://backend.domena.sk

volumes:
  db_data:
```

## Coolify UI

- Domény nastaviť ako **`http://`** (nie `https://`) — Cloudflare pridá HTTPS
- Env premenné nastaviť v **Production Environment Variables**
- Post-deployment container name = názov service z `docker-compose.yml` (napr. `backend`)

### Povinné env premenné

| Premenná | Poznámka |
|---|---|
| `APP_KEY` | Vygenerovať: `php artisan key:generate --show` |
| `DB_ROOT_PASSWORD` | Silné heslo |
| `DB_DATABASE` | Názov databázy |
| `DB_USERNAME` | DB používateľ |
| `DB_PASSWORD` | Silné heslo |
| `SENDGRID_API_KEY` | Pre odosielanie emailov |

## Čo NIKDY nerobiť

- Nepoužívať generické service mená (`backend`, `frontend`) v Traefik labels — spôsobí konflikt medzi projektmi na tom istom serveri
- Nedávať `https://` v Coolify doméne ak Cloudflare SSL je Flexible
- Nepridávať vlastný Traefik service do `docker-compose.yml`
- Nenastavovať `ACME_EMAIL` / Let's Encrypt — Cloudflare rieši SSL
