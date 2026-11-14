# Wallet redesign: plan and progress

Working plan for the Hisabi UI rebrand ("Wallet"). It's written so any agent can pick the work up where it stopped:
read this file, check the boxes, run the tests, continue with the first unchecked item.

- Branch: `wallet-redesign` (PR into `main`; CI runs on pull requests)
- Approved mockups: https://claude.ai/artifact/XykMkVedwGf7kSHM2hoAdU (round four is the reference)
- Strategy and tone: `PRODUCT.md`. Visual system: `DESIGN.md` (written in phase 1).
- Commit and push after each phase once `php artisan test` and `npm run test` pass. Keep `npm run build` working.
- `npm run types` already had errors before this work; don't add new ones in files you touch.

## Decisions (from the owner)

- Look: "Wallet". White or off-white canvas only, white rounded cards, system font (SF on Apple), SF Rounded for amounts, category-colored icon tiles and budget rings. No tinted page backgrounds.
- The app is light only. Dark is used by the kiosk at night (22:00 to 06:00), in neutral greys with no tint.
- Navigation: a floating sidebar (a white card inset from the edges, Add at the top, counts only when something waits, a quieter Manage group). Phones get a bottom dock with Add in the middle. No top navbar.
- Home (new, at `/`): Needs you first, then this month (spent vs. the same point last month, a cumulative chart, three sentence insights), then budgets (rings) and recent transactions. Every card opens more detail (a panel on laptop, a pushed screen on phone).
- First run: the home grows with the data. Day one shows only the SMS box; early days add Spent so far, Needs you, Recent and one budget suggestion; comparisons, insights and the Insights page appear after a full month. No empty charts or "AED 0" cards.
- The budget suggestion's "Not now" hides it until next month (stored on the server).
- Motion: one ease-out curve; cards rise in once per visit, numbers count up, rings and bars draw in, panels slide from the right, finishing a Needs you item checks it off and closes the gap, loading uses a shimmer. Everything collapses to a short fade with reduced motion.
- Insights page: period control (Month, Quarter, Year, All time); net worth; where your income went (one bar); spending by category; spending by month; top brands; "More charts" keeps the older charts including the circle pack.
- Kiosk: same cards scaled up; spent vs. last month, budget rings, latest transaction; light by day, neutral dark at night.
- Landing page (`../marketing`, separate repo) gets the new branding once the app is done.

## Phases

### Phase 0: setup
- [x] Branch `wallet-redesign`
- [x] `PRODUCT.md` and this plan committed
- [x] Draft PR opened (#115)

### Phase 1: foundation
- [x] `resources/css/app.css`: Wallet tokens mapped onto the existing shadcn token names (so every component follows), category colors (`--cat-<color>` for red, blue, green, orange, purple, pink, indigo, gray), neutral dark tokens under `.dark`, radius scale
- [x] Fonts: system stack for UI, `ui-rounded` stack for amounts (`.font-rounded` / `tabular-nums`); drop Geist from `app.blade.php`; stop applying `.dark` from the OS setting
- [x] Motion utilities: keyframes and classes for rise (with `--i` stagger), ring draw, bar grow, line draw, shimmer, pop; reduced-motion fallbacks
- [x] Shared primitives in `resources/js/components/Wallet/`: `Tile` (category icon tile), `Ring`, `CountUp`, `PageHeader`, `Section`/card helpers, `Skeleton`
- [x] Restyle base shadcn components: card, button, input, dialog, sheet, dropdown, badge
- [x] New logo (app icon: black rounded square with three white ascending bars, plus the "hisabi" wordmark): `public/images/logo.svg`, `public/favicon.svg`, PNG favicons, `ApplicationLogo`
- [x] `DESIGN.md` documenting tokens, type, components and motion

### Phase 2: shell
- [x] Shared Inertia prop `nav` (from a `NavigationService`): uncategorized brands count, budgets needing attention, whether Budgets and Insights are unlocked
- [x] `Layouts/Authenticated.tsx`: floating sidebar on `md+`, bottom dock on phones, Add menu (Paste a bank SMS / Add by hand), More sheet on phones; drop the shadcn `Sidebar` usage
- [x] Feature tests for the shared props

### Phase 3: home
- [x] `/` renders `Home` (`HomeController`); `/dashboard` redirects to `/`; `RouteServiceProvider::HOME = '/'`
- [x] `GET /api/v1/home` (`GetHomeQuery`): stage, needs you (uncategorized brands, budgets over or ahead, unparsed SMS), month summary with cumulative daily series for this and last month, insights, current budgets, recent transactions, budget suggestion
- [x] `users.preferences` JSON column and `POST /api/v1/home/dismissals` for "Not now" (hidden until next month)
- [x] `GET /api/v1/budgets/{id}/detail` (`GetBudgetDetailQuery`): spending by brand and transactions in the current window
- [x] `pages/Home.tsx` with the three stages, Needs you (assign categories inline, review a budget), detail panels, the first-run SMS card with highlighted tokens
- [x] PHP feature tests for the new endpoints; Jest tests for stage and formatting helpers

### Phase 4: Insights page
- [x] Period control and the new sections (net worth, where your income went, spending by category, spending by month, top brands)
- [x] New metrics with tests: `income-allocation`, `category-breakdown`, `top-brands`, `spending-over-time` (by day or month, gaps filled)
- [x] "More charts" disclosure keeping the existing trend charts and the circle pack
- [x] Before a full month: only spending by category and top brands

### Phase 5: kiosk
- [ ] Wallet layout (spent card with chart, budget rings, latest transaction pill), light by day, neutral dark 22:00 to 06:00
- [ ] Kiosk API adds the latest transaction and the cumulative series; tests updated

### Phase 6: remaining pages
- [ ] Transactions, Brands, Categories, Budgets, Settings, Login, and the dialogs (SMS parser, create/edit forms) in the Wallet look

### Phase 7: wrap up
- [ ] `AGENT.md` architecture notes (home, shell, tokens, Wallet components)
- [ ] README showcase image refreshed
- [ ] CI green, PR ready for review

### Phase 8: landing page (`../marketing`)
- [ ] New branding and look on the Next.js marketing site, on a branch with a PR

## Local preview (for screenshots)

A throwaway SQLite database keeps the owner's data out of it:

```bash
touch /tmp/demo.sqlite
DB_CONNECTION=sqlite DB_DATABASE=/tmp/demo.sqlite php artisan migrate --force
DB_CONNECTION=sqlite DB_DATABASE=/tmp/demo.sqlite php artisan db:seed --class=DemoAccountSeeder --force
# add a user with php artisan tinker, then serve from public/ (artisan serve drops DB_* variables):
cd public && DB_CONNECTION=sqlite DB_DATABASE=/tmp/demo.sqlite DEBUGBAR_ENABLED=false php -S 127.0.0.1:8123 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

## Where things live

- Tokens and motion: `resources/css/app.css`
- Shell: `resources/js/Layouts/Authenticated.tsx`, `resources/js/components/Wallet/*`
- Home: `app/Http/Controllers/HomeController.php`, `app/Http/Queries/Home/*`, `resources/js/pages/Home.tsx`, `resources/js/components/Home/*`
- Budget statuses: `resources/js/Utils/budgets.ts` (client) mirrored in PHP for counts (`over` when spent > amount, `ahead` when spent% > elapsed% + 3)
