# Design: Wallet

The visual system behind Hisabi. Strategy and tone live in `PRODUCT.md`; the tokens live in `resources/css/app.css`.

## Visual theme

White rounded cards on an off-white canvas, ink for text and primary actions, and color only where it means something: a category's tile, a budget ring, a status. Calm by default, precise with numbers, warm in the wording. It should feel like a native Apple app (Wallet, Fitness) more than a dashboard.

- The canvas is always white or off-white. Never tint the page or panel backgrounds.
- The app is light only. Dark (neutral greys, no tint) is used by the kiosk at night.
- Easy first, deeper on demand: the home answers "what needs me, how is the month going, where is it going"; detail opens in a panel or a pushed screen.

## Color

| Token | Value | Use |
|---|---|---|
| `--background` | `oklch(0.972 0.002 286)` | The canvas |
| `--card` | `oklch(1 0 0)` | Cards, sidebar, panels, dialogs |
| `--foreground` / `--primary` | `oklch(0.2 0.004 286)` | Text, primary buttons |
| `--brand` / `--brand-foreground` | `oklch(0.834 0.251 143)` / ink | Primary buttons, the Add button (the logo's green, #42EF44) |
| `--muted-foreground` | `oklch(0.52 0.006 286)` | Secondary text |
| `--muted` / `--secondary` | `oklch(0.958 0.003 286)` | Pills, active nav item, soft buttons |
| `--border` | `oklch(0.93 0.003 286)` | Row dividers inside cards |
| `--track` | `oklch(0.93 0.004 286)` | Empty part of rings and bars |
| `--destructive` | `oklch(0.58 0.21 25)` | Over budget, errors |
| `--warning` / `--warning-bar` | `oklch(0.6 0.15 55)` / `oklch(0.74 0.16 60)` | Ahead of pace (text / bars) |
| `--success` | `oklch(0.6 0.15 150)` | Done, saved |
| `--info` | `oklch(0.6 0.18 255)` | Links that ask for an action ("Choose a category"), focus |

Category colors come from the `color` stored on each category: `--cat-red`, `--cat-blue`, `--cat-green`, `--cat-orange`, `--cat-purple`, `--cat-pink`, `--cat-indigo`, `--cat-gray`. Use them for tiles, rings and category bars only, never for text. Status is always written out ("Over by 310", "Ahead of pace"), never shown by color alone.

## Logo

The logo is the original Hisabi wordmark with three green bars: `public/images/logo.svg`, used as an image by `components/Wallet/Logo.tsx` (sidebar) and the login page. The favicons are the three bars on their own (`public/favicon.svg`, `favicon.ico` and the PNGs). The logo's green is also the primary button color (`--brand`).

## Typography

- UI: the system font (`-apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, …`).
- Amounts: `font-rounded` (`ui-rounded`, SF Pro Rounded on Apple devices) with tabular figures (`tabular`).
- Page title 28 to 30px bold, tracking -0.028em, with a 13px muted eyebrow above it. Card titles 16px bold. Body 13 to 15px.
- Big amounts: 34 to 40px bold rounded, currency as a small muted prefix ("AED 9,486").

## Shape and depth

- Cards: `rounded-2xl` (22px), `bg-card`, `shadow-card` (a 1px soft shadow). No borders on cards.
- Rows inside a card are split by 1px `border` lines, not separate cards.
- Floating chrome (sidebar, dock, menus, panels) uses `shadow-float` / `shadow-panel`.
- Buttons are pills: primary is the logo green (`bg-brand`) with ink text (white on that green is unreadable), secondary is a soft grey pill. Selected tabs and count pills stay ink; green is for actions only.
- The sidebar's Add is a small green icon button beside the logo; on phones it's the green + in the middle of the dock.
- Category tiles: 36px rounded squares (10px radius) with a white icon.

## Layout

- Laptop: a floating sidebar card (inset 12px from the window edges) and a single content column (max ~1100px) of cards, in one or two columns.
- Phone: the sidebar becomes a bottom dock with Add in the middle; content is one column.
- Budgets are rings. Charts are thin lines (2 to 2.5px) in ink, with last period dashed grey.

## Components (`resources/js/components/Wallet`)

- `PageHeader`: eyebrow, title and actions.
- `Panel`: a white card with a title row, an optional count pill and a "See all" link.
- `Tile`: a category's icon on its color, or a quiet "?" tile without a category.
- `Ring`: a budget ring that draws in.
- `CountUp`: numbers that count up on arrival and roll to new values.
- `EmptyState`: what a list shows before it has anything in it.
- `format.ts`: `money()`, `amount()`, `toAmount()`, `rise()`.

## Motion

One easing curve everywhere: `--ease-out-quint` (`cubic-bezier(0.22, 1, 0.36, 1)`). Every animation explains a change.

| Moment | Class / tool | Timing |
|---|---|---|
| Arriving on a page | `.rise` with `--i` for the stagger | 440ms, 55ms apart |
| Numbers | `CountUp` | 700ms, ease-out-expo |
| Rings, bars, lines | `.ring-draw`, `.grow-x`, `.grow-y`, `.line-draw` | 700 to 1000ms |
| Finishing a task | button turns into a check, row slides out, the list closes the gap | 300ms + 280ms |
| Detail panel | `Sheet` sliding in from the right | 300ms in, 200ms out |
| Loading | `.shimmer` placeholders (`Skeleton`), no spinners | 1.4s loop |

Nothing loops or bounces apart from loading. With `prefers-reduced-motion`, every animation becomes a 150ms fade.
