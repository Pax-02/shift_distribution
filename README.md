# Shift Distribution System

A backend for allocating transportation shifts to support workers. Inbound messages describing a shift request are parsed into structured data (via a pluggable AI parser), and a deterministic allocation engine scores and ranks eligible workers against a set of configurable, independently-weighted rules.

Full spec: `Docs/Shift_Distribution_System_Technical_Specification.docx`.

This repo currently contains the **Laravel API backend** (phase 1). A React frontend is a later phase.

## Stack

- **Backend**: Laravel 13 (PHP 8.3+), REST API, Sanctum (token auth), queues/events/jobs/notifications.
- **Database**: PostgreSQL — UUID primary keys, jsonb columns, a partial unique index. SQLite/MySQL will not work; the schema relies on Postgres-specific features.
- **AI message parsing**: pluggable behind `App\Services\AI\AIParserInterface`. Currently bound to `MockAIParser`, a deterministic regex-based stub — no external API calls, no key required. A real Claude (Anthropic)-backed implementation is a planned follow-up; the spec originally called for OpenAI, but this project uses Claude instead. Swapping parsers is a one-line `.env` change (`AI_PARSER_DRIVER`), not a code change.

## Setup

Requires PHP 8.3+, Composer, and a local PostgreSQL server.

```bash
composer install

cp .env.example .env
php artisan key:generate
```

Create the database and a dedicated role (adjust credentials to match your local Postgres, then update `.env` to match):

```bash
psql postgres -c "CREATE ROLE shift_distribution WITH LOGIN PASSWORD 'secret' CREATEDB;"
psql postgres -c "CREATE DATABASE shift_distribution OWNER shift_distribution;"
psql postgres -c "CREATE DATABASE shift_distribution_test OWNER shift_distribution;"
```

`.env` already defaults to `DB_CONNECTION=pgsql` / `DB_DATABASE=shift_distribution` matching the above. `phpunit.xml` points the test suite at `shift_distribution_test` with the same credentials — update both if yours differ.

Run migrations and seed demo data (roles, one login per role, and a handful of clients/workers/shifts):

```bash
php artisan migrate:fresh --seed
```

Seeded logins (password `password` for all three):

| Email | Role |
|---|---|
| admin@example.com | admin |
| dispatcher@example.com | dispatcher |
| viewer@example.com | viewer |

Serve the app:

```bash
php artisan serve
```

## Testing

Pest, run against the real `shift_distribution_test` Postgres database (not SQLite — see above):

```bash
php artisan test
```

## Architecture

```
React UI (future) → Laravel API → Business Services → Allocation Engine → PostgreSQL
```

Controllers stay thin; business logic lives in `app/Services/`. Key pieces:

- **`app/Services/Allocation/AllocationService.php`** — loads workers, filters out anyone failing a hard-constraint rule, scores the rest against weighted soft rules, returns a ranked list with a full score breakdown.
- **`app/Rules/Allocation/`** — one class per rule (availability, weekly-hour fairness, max hours, standby, journey/client preference, gender requirement, distance from previous shift, consecutive hours, active status). Each is independently testable; weights and enable/disable flags live in `config/allocation.php`.
- **`app/Services/AI/`** — `AIParserInterface` + `MockAIParser`, bound via `AIServiceProvider`. `ParsedMessageValidator` validates AI output before anything is persisted.
- **`app/Jobs/ParseShiftMessageJob.php`** — runs the parser against an ingested `ShiftMessage`, validates, and creates the `Shift` if the payload's client name resolves to a known `Client`.
- **Events/Listeners** (`app/Events/`, `app/Listeners/`) — `AssignmentCreated`/`AssignmentCancelled` keep `worker_hours` in sync (`RecalculateWorkerHours`) and notify the worker by email; `ShiftAllocated` writes an audit-log entry recording the full ranked candidate list.
- **`app/Concerns/Auditable.php`** — trait applied to the core models; writes an `audit_logs` row on create/update/delete with before/after diffs.

## REST API

All routes except `/login` require a Sanctum bearer token (`Authorization: Bearer <token>` from the `/login` response).

| Method | Path | Notes |
|---|---|---|
| POST | `/api/login` | Returns a bearer token |
| POST | `/api/logout` | Revokes the current token |
| GET/POST/GET/PUT/DELETE | `/api/workers`, `/api/workers/{id}` | Standard CRUD |
| GET/POST/GET/PUT/DELETE | `/api/clients`, `/api/clients/{id}` | Standard CRUD |
| GET/POST/GET/PUT/DELETE | `/api/shifts`, `/api/shifts/{id}` | Standard CRUD |
| POST | `/api/shift-messages` | Ingests a raw message; dispatches `ParseShiftMessageJob` |
| POST | `/api/allocation/run` | Body: `{ "shift_id": "..." }`. Scores workers, proposes the top-ranked one |
| GET | `/api/allocation/history` | Optional `shift_id`, `from`, `to` filters |
| PATCH | `/api/assignments/{id}/confirm` | Confirms a proposed assignment |
| PATCH | `/api/assignments/{id}/cancel` | Body: optional `{ "reason": "..." }` |
| GET | `/api/reports/hours` | Optional `?week=YYYY-MM-DD` |
| GET | `/api/reports/workers` | Roster summary |
| GET | `/api/reports/shifts` | Optional `?from=&to=` |

Authorization is role-based (`admin`/`dispatcher`/`viewer`), enforced via Policy classes keyed off permission strings on `roles.permissions` (e.g. `workers.manage`, `allocation.run`).
