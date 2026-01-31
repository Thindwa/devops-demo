Got it. Here’s a fully complete, production-accurate, beginner-friendly v3 guide for your Help Desk system with monorepo, full backend + frontend CI/CD, blue-green/canary deployments, and failure drills—all outside the canvas for clarity.

Help Desk System – v3 Complete DevOps Guide (Monorepo)

This guide walks a small team through building, testing, and deploying a Help Desk system using Laravel (API) and React (UI) in a monorepo setup, with full DevOps best practices.

Team & Roles

Developer 1: Backend (Laravel API)

Developer 2: Frontend (React UI)

Developer 3: Backend + Frontend support

QA Engineer: Testing & validation, staging gatekeeper

DevOps Engineer: CI/CD, Docker, security, deployment

Tech Stack

Backend: Laravel (API only)

Frontend: React (SPA)

Repository: GitHub Monorepo

CI/CD: GitHub Actions

Containers: Docker

Security: Trivy (backend and frontend Node deps)

Deployment: Coolify

Environments: Staging & Production

Monorepo Structure
helpdesk-system/
├── backend/                # Laravel API
├── frontend/               # React UI
├── docker/
│   ├── backend.Dockerfile
│   └── frontend.Dockerfile
├── .github/workflows/
│   ├── ci.yml
│   ├── deploy-staging.yml
│   └── promote-production.yml
└── README.md


Why monorepo?

Easier coordination between frontend and backend

Single CI pipeline

Single versioned release

Beginner-friendly for small teams

Git & Branching Strategy

feature/* – development branches

develop – staging

main – production

Rules:

No direct push to develop or main

Pull Request required

CI must pass before merge

QA approval required for production

Backend Setup (Laravel API)
composer create-project laravel/laravel backend
cd backend
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

User Roles

Add in users migration:

$table->string('role')->default('user');


Roles: user, agent, admin

Ticket Model & Migration
php artisan make:model Ticket -m

Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->string('status')->default('open');
    $table->foreignId('user_id');
    $table->timestamps();
});

Role Middleware
php artisan make:middleware RoleMiddleware

public function handle($request, Closure $next, $role)
{
    if ($request->user()->role !== $role) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    return $next($request);
}


Register middleware in Kernel.php.

API Routes Example
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets', [TicketController::class, 'index']);

    Route::middleware('role:agent')
        ->patch('/tickets/{id}/status', [TicketController::class, 'updateStatus']);

    Route::middleware('role:admin')
        ->get('/users', [UserController::class, 'index']);
});

Frontend Setup (React)
npx create-react-app frontend
cd frontend


Use Axios or fetch to call API

Attach token to requests in headers

Maintain separate .env for API base URL per environment

Docker Setup
Backend Dockerfile
FROM php:8.2-fpm
WORKDIR /var/www
RUN docker-php-ext-install pdo pdo_mysql
COPY . .
RUN composer install --no-dev --optimize-autoloader
CMD ["php-fpm"]

Frontend Dockerfile
FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build
CMD ["npx", "serve", "-s", "build", "-l", "3000"]

Database Migrations & Seeders per Environment

.env.staging:

APP_ENV=staging
DB_DATABASE=helpdesk_staging


.env.production:

APP_ENV=production
DB_DATABASE=helpdesk_prod


Seeder strategy:

BaseSeeder: roles & permissions, all envs

DemoSeeder: fake tickets, only staging

if (app()->environment('staging')) {
    $this->call(DemoSeeder::class);
}


CI ensures migrations succeed before deployment.

API Testing with Pest
composer require pestphp/pest --dev
php artisan pest:install


Example:

it('allows user to create ticket', function () {
    $user = User::factory()->create(['role' => 'user']);
    $response = actingAs($user)->postJson('/api/tickets', [
        'title' => 'Login issue',
        'description' => 'Cannot login'
    ]);
    $response->assertStatus(201);
});


Tests run on every PR

Failed tests block merge

Rate Limiting

RouteServiceProvider.php:

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(
        match($request->user()?->role) {
            'admin' => 300,
            'agent' => 120,
            default => 60
        }
    )->by($request->user()?->id ?: $request->ip());
});

Audit Logging

Audit log table fields:

user_id

action

entity_type

entity_id

ip_address

user_agent

Example:

AuditLog::create([
    'user_id' => auth()->id(),
    'action' => 'TICKET_CREATED',
    'entity_type' => 'Ticket',
    'entity_id' => $ticket->id,
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);

GitHub Actions – CI

.github/workflows/ci.yml (monorepo):

name: CI
on: [pull_request]
jobs:
  backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: docker build -t helpdesk-backend:test -f docker/backend.Dockerfile backend
      - uses: aquasecurity/trivy-action@0.20.0
        with:
          image-ref: helpdesk-backend:test
          severity: CRITICAL,HIGH
          exit-code: 1
      - run: php backend/vendor/bin/pest
  frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: cd frontend && npm ci && npm run lint && npm test
      - run: docker build -t helpdesk-frontend:test -f docker/frontend.Dockerfile frontend

CD – Staging Deployment

.github/workflows/deploy-staging.yml:

Backend & Frontend build once, scan, tag :staging, push to Coolify

QA validates combined staging environment

Blue-Green strategy can be applied to both services.

Production Promotion

Merge develop → main triggers retagging :staging → :production

No rebuild

Coolify performs blue-green switch for minimal downtime

Canary can be optionally applied for early rollout.

QA Responsibilities

Test staging frontend + backend together

Validate login, ticket lifecycle, roles, rate limiting

Confirm audit logs

Sign-off before production promotion

Failure Drills

Rollback: switch traffic to previous blue version

Broken migration: halt deploy, fix via PR, never hotfix prod DB

Failed security scan: CI blocks merge until fix

Production incident simulation: trigger rollback, verify, document

1–2 Week Lab Plan

Week 1

Repo & branch setup

CI for backend & frontend

Auth & ticket CRUD

Staging deployment

Week 2

Rate limiting & audit logs

Security scans

Production promotion

Blue-Green / Canary deployment exercises

Failure drills & retrospective

Outcome

By completing this lab, the team understands:

Full CI/CD for frontend + backend

Image immutability & retagging

Blue-Green / Canary deployments

Security scanning & audit logging

Environment promotion & QA gate

Real DevOps workflows suitable for production

This is production-ready learning material for small teams.