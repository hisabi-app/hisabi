import { getBudgetStatus, getHistoryBand, getPeriodStart, monthsBetween, toNumber } from '../budgets';

it('toNumber parses formatted strings', () => {
    expect(toNumber('1,234.50')).toBe(1234.5);
    expect(toNumber(-65)).toBe(-65);
    expect(toNumber('abc')).toBe(0);
    expect(toNumber(null)).toBe(0);
});

it('getBudgetStatus compares spending with elapsed time', () => {
    expect(getBudgetStatus(865, 800, 90)).toBe('over');
    expect(getBudgetStatus(300, 300, 90)).toBe('used');
    expect(getBudgetStatus(1140, 1200, 90)).toBe('ahead');
    expect(getBudgetStatus(2050, 2500, 90)).toBe('on_track');
    expect(getBudgetStatus(930, 1000, 90)).toBe('on_track');
});

it('getHistoryBand groups percentages', () => {
    expect(getHistoryBand(79)).toBe('under');
    expect(getHistoryBand(80)).toBe('near');
    expect(getHistoryBand(100)).toBe('near');
    expect(getHistoryBand(101)).toBe('over');
});

it('monthsBetween lists the first day of each month, inclusive', () => {
    const months = monthsBetween(new Date(2025, 10, 15), new Date(2026, 1, 3));

    expect(months.map((month) => [month.getFullYear(), month.getMonth(), month.getDate()])).toEqual([
        [2025, 10, 1],
        [2025, 11, 1],
        [2026, 0, 1],
        [2026, 1, 1],
    ]);
    expect(monthsBetween(new Date(2026, 1, 1), new Date(2025, 1, 1))).toEqual([]);
});

it('getPeriodStart returns the start of the calendar period containing the date', () => {
    // Wednesday, Nov 18 2026
    const date = new Date(2026, 10, 18, 15, 30);

    expect(getPeriodStart('WEEKLY', date)).toBe('2026-11-16');
    expect(getPeriodStart('MONTHLY', date)).toBe('2026-11-01');
    expect(getPeriodStart('YEARLY', date)).toBe('2026-01-01');
    // A Sunday belongs to the week that started the Monday before
    expect(getPeriodStart('WEEKLY', new Date(2026, 10, 22))).toBe('2026-11-16');
});
