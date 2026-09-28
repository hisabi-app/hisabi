export type BudgetStatus = 'on_track' | 'ahead' | 'used' | 'over';

// Spending more than this many percentage points ahead of the elapsed time counts as "ahead of pace".
const PACE_TOLERANCE = 3;

// Some budget fields arrive as number_format()'ed strings, e.g. "1,234.50".
export const toNumber = (value: number | string | null | undefined): number => {
    const parsed = Number(String(value ?? 0).replace(/,/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

export const getBudgetStatus = (spent: number, amount: number, elapsedPercentage: number): BudgetStatus => {
    if (amount <= 0) return spent > 0 ? 'over' : 'on_track';

    const spentPercentage = (spent / amount) * 100;

    if (spentPercentage > 100) return 'over';
    if (spentPercentage === 100) return 'used';
    if (spentPercentage > elapsedPercentage + PACE_TOLERANCE) return 'ahead';

    return 'on_track';
};

export interface HistoryPeriod {
    start_date: string;
    end_date: string;
    spent: number;
    percentage: number;
    is_current: boolean;
}

export interface BudgetHistory {
    id: number;
    name: string;
    reoccurrence: string;
    period: number;
    amount: number;
    periods: HistoryPeriod[];
}

export type HistoryBand = 'under' | 'near' | 'over';

export const getHistoryBand = (percentage: number): HistoryBand => {
    if (percentage > 100) return 'over';
    if (percentage >= 80) return 'near';

    return 'under';
};

// First day of every month from `from`'s month to `to`'s month, inclusive.
export const monthsBetween = (from: Date, to: Date): Date[] => {
    const months: Date[] = [];
    const cursor = new Date(from.getFullYear(), from.getMonth(), 1);
    const last = new Date(to.getFullYear(), to.getMonth(), 1);

    while (cursor <= last) {
        months.push(new Date(cursor));
        cursor.setMonth(cursor.getMonth() + 1);
    }

    return months;
};
