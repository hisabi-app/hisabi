# AGENT.md

This file provides guidance to AI coding agents when working with code in this repository.

## Project

Hisabi is a self-hosted, single-user personal finance tracker: Laravel 12 (PHP 8.2+) backend, React 19 + Inertia 2 + TypeScript + Tailwind 4 frontend (shadcn/Radix UI components in `resources/js/components/ui`). It parses bank SMS messages into transactions and shows what needs attention and where the money went.

Design context lives in `PRODUCT.md` (who it's for, tone, principles) and `DESIGN.md` (the "Wallet" visual system: tokens, type, components, motion). Read both before UI work.

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
- `routes/web.php` holds nearly everything. Inertia page routes (`/` home, `/insights`, `/transactions`, `/brands`, `/categories`, `/budgets`, `/settings`) render thin shells; the pages then fetch data from JSON endpoints under `/api/v1/*`, which are **also in `web.php`** behind session `auth` (not Sanctum). `routes/api.php` only has a Sanctum token-login endpoint.
- The home (`/`, `pages/Home.tsx`) reads `/api/v1/home` (`GetHomeQuery` → `App\Domains\Home\Services\HomeService`): the stage, Needs you (brands without a category, budgets over or ahead of pace, unread SMS), the month against the same point last month with daily running totals, insights, budgets, recent transactions and a budget suggestion ("Not now" is stored in `users.preferences` until next month).
- The app unfolds with the data: `HomeService::getStage()` is `new` (no transactions), `early` (less than a month) or `full`. It's shared with every page as the Inertia prop `nav` (with the sidebar counts); Budgets and Insights appear in the navigation as they unlock.
- `/kiosk` is a standalone full-screen page for a wall-mounted tablet (no app layout): light by day, neutral dark from 22:00 to 06:00. It reads `/api/v1/kiosk` (this month's spending vs. the same time last month with daily running totals, current budgets, the latest transaction), refreshes itself hourly and reloads daily at 4am.
- Frontend API clients live in `resources/js/Api/*.js` (fetch + CSRF token from the meta tag). Inertia pages resolve from `resources/js/pages/<Name>.tsx`.
- Components: `components/Wallet` (design-system pieces: `PageHeader`, `Panel`, `Tile`, `Ring`, `CountUp`, `EmptyState`, `format.ts`), `components/Shell` (floating sidebar, phone dock, navigation config), `components/Home` and `components/Insights` (page sections), `components/Domain` (older metric charts, budgets list).
- `Layouts/Authenticated.tsx` owns the Add dialogs (paste an SMS, add by hand). Pages open them with `requestAdd()` (Home and Transactions put `Shell/AddButton` in their header; phones use the dock's +) and refresh on `useDataChanged()` (`hooks/useDataChanged.ts`).

### Backend layering (CQRS-style)
API controllers in `app/Http/Controllers/Api/V1` are thin: they build a command/query DTO and call its handler. Each operation is a folder with three classes:

```
app/Http/Commands/<Domain>/<Name>Command/{<Name>Command, <Name>CommandHandler, <Name>CommandResponse}.php
app/Http/Queries/<Domain>/<Name>Query/{<Name>Query, <Name>QueryHandler, <Name>QueryResponse}.php
```

Handlers call domain services (command handlers wrap writes in `DB::transaction`); the Response object's `toResponse()` builds the `JsonResponse`. Follow this pattern when adding endpoints.

Domain code lives in `app/Domains/<Domain>/{Models,Services}` (Transaction, Brand, Category, Sms, Budget, User, Metrics, Home).

### Data model
Category (type: `INCOME` | `EXPENSES` | `SAVINGS` | `INVESTMENT`) → hasMany Brand → hasMany Transaction. A transaction's category is always derived through its brand. Sms optionally belongsTo Transaction. Budget belongsToMany Category. Models are global — there is no per-user scoping.

### Namespace quirks (legacy vs. current)
- `Category` lives at `App\Models\Category` (not under `app/Domains/Category/Models`, which is what the rest of the code imports) — check existing imports before using either.
- Insights metrics: `App\Domains\Metrics\Metrics\*` (extend `App\Domains\Metrics\Metric`, constructed with `from`/`to`, implement `calculate(): array`) are what `MetricsController` uses — one route per metric under `/api/v1/metrics/*`. The Insights page uses `income-allocation`, `category-breakdown`, `top-brands`, `spending-over-time`, `net-worth` and `net-worth-trend`; the rest feed the older charts under "More charts". New metrics should use `getDayRange()` so the last day counts in full. `App\Domain\Metrics\*` (singular `Domain`) is an older Nova-style metric system still covered by tests in `tests/Unit/Domain`, but not used by the current API.

### SMS parsing pipeline
Bound via contracts in `AppServiceProvider` (`app/Contracts` → `app/BusinessLogic`):
1. `SmsTransactionProcessor::process()` splits input by newline.
2. `SmsTemplateDetector` matches each line against templates in `config/hisabi.php` (`sms_templates`, placeholders like `{amount}`, `{brand}`, `{date}`, `{time}`, `{card}`, `{ignore}`) and extracts values.
3. `SmsParser` builds the `Sms` model; `Transaction::tryCreateFromSms()` creates the transaction, auto-creating the brand via `Brand::findOrCreateNew()` (new brands have no category until the user assigns one).

Supporting a new bank SMS format usually means only adding a template to `config/hisabi.php`. Currency is also set there (`hisabi.currency`).

### Brand names
SMS brand strings are ALL CAPS, truncated (~20 chars) and carry branch noise. When cleaning brands up, or creating/naming brands:
- **One brand per merchant, not per branch.** Strip branch/site/store numbers and locations (`EPPCO SITE 1063`, `KFC WARQAA DUBAI`, `1462 LIFE 135 PHY` → `Eppco`, `KFC`, `Life Pharmacy`), legal suffixes (`LLC`, `L.L.C`, `FZC`, `PJSC`, `CO`) and complete truncated words (`SUPERMA` → `Supermarket`).
- **Title Case**, keeping official stylings: McDonald's, KFC, IKEA, ADNOC, AWS, noon, eToro, DigitalOcean, 1Password.
- **Use the consumer-facing brand**, not the operator or payment rail: `GALADARI ICE CREAM` → `Baskin Robbins`, `CRAVIA INC CINNABON` → `Cinnabon`, `CHATGPT SUBSCRIPTION` → `OpenAI`, `SDG*DubaiPay` → `DubaiPay`.
- **Avoid very short or generic names.** `Brand::findOrCreateNew()` matches when an incoming SMS brand *contains* an existing brand name, so `du` or `Max` would swallow unrelated merchants; use `du Telecom`, `Max Fashion`. The same behaviour is why clean base names (`Eppco`) catch future branch variants.
- **Leave owner-created brands alone** (Salary, House Rent, Family Support, OLDTLB, trips, loans…) and never merge the per-category `Others` brands.
- **Merging:** keep the brand with the most transactions, move the others' transactions to it with `UPDATE transactions SET brand_id=…`, then delete the empty brands. Never delete a brand through Eloquent to merge it: `Brand::booted()` deletes all of a brand's transactions on `deleted`.
- **When unsure** (guessed truncations, franchise groups, merges across categories), write the proposed changes to a CSV for the owner to review before touching the DB.

### Other
- `/report` renders a Blade view built by `ReportManager` (also `app/Console/Commands/ReportCommand.php`).
- Tests: mostly PHPUnit-style classes extending `Tests\TestCase` with `RefreshDatabase`; Pest is installed and bound to `tests/Feature`.
- `DASHBOARD_IMPROVEMENTS.md`, `tasks.md` and `IMPLEMENTATION_PROMPT.md` are task backlogs/prompts for agents (e.g. the planned multi-account system), not documentation of the current behavior. `WALLET_REDESIGN_PLAN.md` records the redesign's decisions and progress, and how to preview the UI against a throwaway SQLite database.

## UI guidelines

- Follow `DESIGN.md`: white or off-white canvas only (never tint page or panel backgrounds), white rounded cards, the system font with `font-rounded` for amounts, color only from categories and status. The logo's green stays in the logo. The app is light only; the kiosk's night mode is the one dark surface.
- Navigation is the floating sidebar on laptops and the bottom dock on phones; don't add a top navbar.
- Easy first, detail on demand: summaries on the page, detail in a side `Sheet` (see `components/Home/DetailSheet.tsx`).
- No empty charts or "AED 0" cards: a section shows up once it has something real to say (see the stages above).
- Motion uses the utilities in `app.css` (`rise` with `--i`, `ring-draw`, `grow-x/y`, `line-draw`, `shimmer`) and one curve (`ease-out-quint`); every animation explains a change and falls back to a fade with reduced motion.
- Status is always written out ("Over by 310", "Ahead of pace"), never shown by color alone.

- Only show information the user will act on or asked for. Don't add derived numbers, summary cards, per-day rates, "x days left" hints, projections or stats just because the data allows them; when unsure, leave it out and suggest it instead. The owner has removed these from the budgets page as clutter.
- Every number or label must make sense at a glance without explanation. If it needs a sentence to understand, it doesn't belong.
- Prefer fewer, clearer elements over dense dashboards.
