import { BudgetStatus, getBudgetStatus, toNumber } from './budgets';

export interface KioskBudget {
    id: number;
    name: string;
    amount: number;
    remaining: number;
    spentPercentage: number;
    elapsedPercentage: number;
    status: BudgetStatus;
    color: string | null;
    icon: string | null;
}

export interface KioskCategory {
    id: number;
    name: string;
    color: string | null;
    icon: string | null;
    total: number;
}

export interface Comparison {
    direction: 'less' | 'more' | 'same';
    difference: number;
}

// Budgets that need attention first, then on track, then the ones with nothing left
const STATUS_ORDER: BudgetStatus[] = ['over', 'ahead', 'on_track', 'used'];

// The hour (local time) the page reloads itself, to pick up new deploys
const DAILY_RELOAD_HOUR = 4;

// The kiosk goes dark from 22:00 to 06:00 so it doesn't light up the kitchen at night
const NIGHT_STARTS = 22;
const NIGHT_ENDS = 6;

export const isNight = (now: Date): boolean => now.getHours() >= NIGHT_STARTS || now.getHours() < NIGHT_ENDS;

// A category row on the kiosk is 2.3em tall, with 0.9em between rows. A column needs 18.4em to fit a
// 14-letter name and a five-digit amount, with 2.4em between columns
const CATEGORY_ROW = 2.3;
const CATEGORY_ROW_GAP = 0.9;
const CATEGORY_COLUMN = 18.4;
const CATEGORY_COLUMN_GAP = 2.4;

// The categories page lists them all down two columns (three past 14). The rows grow, up to 1.3 times,
// to fill the page when there are few, and shrink to fit when there are many or the columns get narrow
export const categoryLayout = (count: number, heightEm: number, widthEm: number): { columns: number; rows: number; scale: number } => {
    const columns = count > 14 ? 3 : 2;
    const rows = Math.max(1, Math.ceil(count / columns));

    // Not measured yet
    if (heightEm <= 0 || widthEm <= 0) return { columns, rows, scale: 1 };

    const tallest = heightEm / (rows * CATEGORY_ROW + (rows - 1) * CATEGORY_ROW_GAP);
    const widest = widthEm / (columns * CATEGORY_COLUMN + (columns - 1) * CATEGORY_COLUMN_GAP);

    return { columns, rows, scale: Math.max(0.6, Math.min(1.3, tallest, widest)) };
};

export const toKioskBudgets = (budgets: any[]): KioskBudget[] =>
    budgets
        .map((budget) => {
            const amount = toNumber(budget.amount);
            const spent = toNumber(budget.total_transactions_amount);
            const elapsedPercentage = toNumber(budget.elapsed_days_percentage);

            return {
                id: budget.id,
                name: budget.name,
                amount,
                remaining: amount - spent,
                spentPercentage: amount > 0 ? Math.round((spent / amount) * 100) : 0,
                elapsedPercentage,
                status: getBudgetStatus(spent, amount, elapsedPercentage),
                color: budget.color ?? null,
                icon: budget.icon ?? null,
            };
        })
        .sort((a, b) => STATUS_ORDER.indexOf(a.status) - STATUS_ORDER.indexOf(b.status));

export const compareWithLastMonth = (spent: number, spentLastMonth: number): Comparison | null => {
    // Nothing to compare with, e.g. the first month of using the app
    if (spentLastMonth <= 0) return null;

    const difference = Math.round(spent - spentLastMonth);

    if (difference === 0) return { direction: 'same', difference: 0 };

    return { direction: difference < 0 ? 'less' : 'more', difference: Math.abs(difference) };
};

export const msUntilDailyReload = (now: Date): number => {
    const next = new Date(now);
    next.setHours(DAILY_RELOAD_HOUR, 0, 0, 0);

    if (next <= now) next.setDate(next.getDate() + 1);

    return next.getTime() - now.getTime();
};
