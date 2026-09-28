export type BudgetStatus = 'on_track' | 'ahead' | 'used' | 'over';

export interface DailySpending {
    date: string;
    amount: number;
}

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

export const buildBurnDown = (days: DailySpending[], totalDays: number, amount: number) => {
    let total = 0;
    const points = [{ day: 0, total: 0 }];

    days.forEach((day, index) => {
        total += day.amount;
        points.push({ day: index + 1, total });
    });

    const elapsedDays = days.length;

    return {
        points,
        spent: total,
        elapsedDays,
        projected: elapsedDays > 0 && totalDays > 0 ? (total / elapsedDays) * totalDays : 0,
        averagePerDay: elapsedDays > 0 ? total / elapsedDays : 0,
        evenPacePerDay: totalDays > 0 ? amount / totalDays : 0,
    };
};
