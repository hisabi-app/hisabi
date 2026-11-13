import { BudgetStatus, getBudgetStatus, toNumber } from './budgets';

export interface KioskBudget {
    id: number;
    name: string;
    amount: number;
    remaining: number;
    spentPercentage: number;
    elapsedPercentage: number;
    status: BudgetStatus;
}

export interface Comparison {
    direction: 'less' | 'more' | 'same';
    difference: number;
}

// Budgets that need attention first, then on track, then the ones with nothing left
const STATUS_ORDER: BudgetStatus[] = ['over', 'ahead', 'on_track', 'used'];

// The hour (local time) the page reloads itself, to pick up new deploys
const DAILY_RELOAD_HOUR = 4;

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
