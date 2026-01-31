## Help Desk System (v3) — Monorepo DevOps Lab

This repo follows the reference guide:
- `help_desk_system_v_3_monorepo_dev_ops_guide.md`

### Repo structure
- `backend/`: Laravel API (Sanctum token auth)
- `frontend/`: React (Vite) UI
- `docker/`: Dockerfiles for backend/frontend images
- `.github/workflows/`: GitHub Actions CI/CD (CI, deploy-staging, promote-production)

### Run locally (browser)

Backend (Laravel):

```bash
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

Frontend (React):

```bash
cd frontend
npm install
npm run dev
```

Open the UI:
- `http://localhost:5173`

Demo users (password: `password`):
- `user@example.com` (user)
- `agent@example.com` (agent)
- `admin@example.com` (admin)

### Tests

Backend:

```bash
cd backend
./vendor/bin/pest
```

Frontend:

```bash
cd frontend
npm test -- --run
```

### CI/CD
- **CI** (`.github/workflows/ci.yml`): runs on PRs, tests + scans backend and frontend.
- **Deploy Staging** (`deploy-staging.yml`): on push/merge to `develop`, build+scan both images, tag `:staging`, deploy via Coolify webhook.
- **Promote Production** (`promote-production.yml`): on push/merge to `main`, retag `:staging` → `:production` (no rebuild), deploy via Coolify webhook.

