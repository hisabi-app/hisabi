# AGENT.md

This file provides guidance to AI coding agents when working with code in this repository.

## Project

Hisabi is a self-hosted, single-user personal finance tracker: Laravel 12 (PHP 8.2+) backend, React 19 + Inertia 2 + TypeScript + Tailwind 4 frontend (shadcn/Radix UI components in `resources/js/components/ui`). It parses bank SMS messages into transactions and shows dashboard metrics.

## Commands

```bash
composer dev                 # server + queue + pail logs + vite, concurrently
npm run build                # production frontend build (feature tests that render Inertia pages need the Vite manifest)

php artisan test                                   # all PHP tests (sqlite :memory:, see phpunit.xml)
php artisan test --filter=TransactionControllerTest
php artisan test tests/Feature/Api/V1/TransactionControllerTest.php

npm run test                                       # Jest (jsdom) — tests in resources/js/**/__tests__ or *.test.*
npx jest resources/js/Utils/__tests__/<file>
npm run types                                      # tsc --noEmit
npm run lint                                       # eslint --fix
npm run format                                     # prettier on resources/ (4-space indent, single quotes, width 150)

php artisan hisabi:install   # interactive: create the user, optionally seed default brands/categories
```

Docker (from README): `make build`, `make run` (docker-compose up -d), `make install` (migrate + `hisabi:install`), `make stop`.

CI (`.github/workflows/run-tests.yml`) runs on PHP 8.4 / Node 22: `npm run build`, `php artisan test`, `npm run test`.

## Architecture

### Request flow
- `routes/web.php` holds nearly everything. Inertia page routes (`/insights`, `/transactions`, `/brands`, `/categories`, `/budgets`, `/settings`) render thin shells; the pages then fetch data from JSON endpoints under `/api/v1/*`, which are **also in `web.php`** behind session `auth` (not Sanctum). `routes/api.php` only has a Sanctum token-login endpoint.
- Frontend API clients live in `resources/js/Api/*.js` (fetch + CSRF token from the meta tag). Inertia pages resolve from `resources/js/pages/<Name>.tsx`; domain-specific components are in `resources/js/components/Domain`.

### Backend layering (CQRS-style)
API controllers in `app/Http/Controllers/Api/V1` are thin: they build a command/query DTO and call its handler. Each operation is a folder with three classes:

```
app/Http/Commands/<Domain>/<Name>Command/{<Name>Command, <Name>CommandHandler, <Name>CommandResponse}.php
app/Http/Queries/<Domain>/<Name>Query/{<Name>Query, <Name>QueryHandler, <Name>QueryResponse}.php
```

Handlers call domain services (command handlers wrap writes in `DB::transaction`); the Response object's `toResponse()` builds the `JsonResponse`. Follow this pattern when adding endpoints.

Domain code lives in `app/Domains/<Domain>/{Models,Services}` (Transaction, Brand, Category, Sms, Budget, User, Metrics).

### Data model
Category (type: `INCOME` | `EXPENSES` | `SAVINGS` | `INVESTMENT`) → hasMany Brand → hasMany Transaction. A transaction's category is always derived through its brand. Sms optionally belongsTo Transaction. Budget belongsToMany Category. Models are global — there is no per-user scoping.

### Namespace quirks (legacy vs. current)
- `Category` lives at `App\Models\Category` (not under `app/Domains/Category/Models`, which is what the rest of the code imports) — check existing imports before using either.
- Insights metrics: `App\Domains\Metrics\Metrics\*` (extend `App\Domains\Metrics\Metric`, constructed with `from`/`to`, implement `calculate(): array`) are what `MetricsController` uses — one route per metric under `/api/v1/metrics/*`. `App\Domain\Metrics\*` (singular `Domain`) is an older Nova-style metric system still covered by tests in `tests/Unit/Domain`, but not used by the current API.

### SMS parsing pipeline
Bound via contracts in `AppServiceProvider` (`app/Contracts` → `app/BusinessLogic`):
1. `SmsTransactionProcessor::process()` splits input by newline.
2. `SmsTemplateDetector` matches each line against templates in `config/hisabi.php` (`sms_templates`, placeholders like `{amount}`, `{brand}`, `{date}`, `{time}`, `{card}`, `{ignore}`) and extracts values.
3. `SmsParser` builds the `Sms` model; `Transaction::tryCreateFromSms()` creates the transaction, auto-creating the brand via `Brand::findOrCreateNew()` (new brands have no category until the user assigns one).

Supporting a new bank SMS format usually means only adding a template to `config/hisabi.php`. Currency is also set there (`hisabi.currency`).

### Other
- `/report` renders a Blade view built by `ReportManager` (also `app/Console/Commands/ReportCommand.php`).
- Tests: mostly PHPUnit-style classes extending `Tests\TestCase` with `RefreshDatabase`; Pest is installed and bound to `tests/Feature`.
- `DASHBOARD_IMPROVEMENTS.md`, `tasks.md` and `IMPLEMENTATION_PROMPT.md` are task backlogs/prompts for agents (e.g. the planned multi-account system), not documentation of the current behavior.
