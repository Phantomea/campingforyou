# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

John's Garage - webová stránka autoservisu s oddelenými frontend a backend časťami v monorepo štruktúre.

- **Frontend:** Nuxt 4 (Vue 3, TypeScript)
- **Backend:** Laravel 12 (PHP 8.2+)
- **Databáza:** MariaDB/MySQL
- **Autentifikácia:** Laravel Sanctum (SPA režim)

## Štruktúra projektu

```
johnsgarage/
├── frontend/          # Nuxt 4 aplikácia
│   ├── app/pages/     # Stránky (file-based routing)
│   ├── components/    # Vue komponenty
│   ├── composables/   # Composables (useAuth, useApi)
│   ├── layouts/       # Layouts (default, admin)
│   └── middleware/    # Route middleware (auth, guest)
├── backend/           # Laravel 12 API
│   ├── app/Models/    # Eloquent modely
│   ├── app/Http/Controllers/
│   │   ├── Api/       # Verejné API endpointy
│   │   └── Admin/     # Admin API endpointy
│   ├── database/migrations/
│   └── routes/api.php # API routes
└── CLAUDE.md
```

## Príkazy pre vývoj

### Backend (Laravel)

```bash
# Spustiť backend server
cd backend && php artisan serve

# Migrácie
cd backend && php artisan migrate
cd backend && php artisan migrate:fresh --seed  # Reset s ukážkovými dátami

# Vyčistiť cache
cd backend && php artisan config:clear && php artisan cache:clear
```

### Frontend (Nuxt)

```bash
# Spustiť dev server (port 9000)
cd frontend && npm run dev

# Build
cd frontend && npm run build

# Generate static
cd frontend && npm run generate
```

### Prvé spustenie

1. Vytvoriť databázu `johnsgarage` v MariaDB/MySQL
2. Nakonfigurovať `backend/.env` (DB credentials)
3. `cd backend && php artisan migrate --seed`
4. Spustiť backend: `cd backend && php artisan serve` (port 8000)
5. Spustiť frontend: `cd frontend && npm run dev` (port 9000)

## Používateľské role

- **owner** - Majiteľ autoservisu (správa obsahu)
- **super_admin** - Vývojár (plný prístup)

## Testovacie účty (po seedovaní)

- Super Admin: `admin@johnsgarage.sk` / `password`
- Owner: `john@johnsgarage.sk` / `password`

## API Endpointy

### Verejné
- `GET /api/services` - zoznam služieb
- `GET /api/services/{slug}` - detail služby
- `GET /api/pricing` - cenník (grouped by category)
- `GET /api/pages/{slug}` - obsah stránky
- `GET /api/settings/public` - verejné nastavenia

### Admin (auth required)
- `POST /api/login` / `POST /api/logout`
- `CRUD /api/admin/services`
- `CRUD /api/admin/pricing`
- `CRUD /api/admin/pages`
- `GET/PUT /api/admin/settings`
- `CRUD /api/admin/users` (super_admin only)

## Frontend technológie

- **CSS Framework:** Bootstrap 5
- **JavaScript komponenty:** Bootstrap JS (modály, collapse, navbar)
- **Ikony:** Bootstrap Icons (CSS/font)
- **Plugin:** `plugins/bootstrap.client.ts` poskytuje `$bootstrap` pre JavaScript komponenty

### Použitie Bootstrap Modal

```typescript
const { $bootstrap } = useNuxtApp()
const modalRef = ref<HTMLElement | null>(null)
const modalInstance = ref<any>(null)

const openModal = () => {
  if (!modalInstance.value && modalRef.value) {
    modalInstance.value = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance.value?.show()
}
```

## Konvencie

- API vracia JSON
- Frontend používa TypeScript
- Slovenský jazyk v UI textoch
- Komponenty v PascalCase (Header.vue, AdminSidebar.vue)
- Composables s prefixom `use` (useAuth, useApi)
- CSS triedy z Bootstrap 5 (`.btn`, `.card`, `.form-control`, `.row`, `.col-*`)
- Bootstrap Icons s prefixom `bi` (`<i class="bi bi-tools"></i>`)
