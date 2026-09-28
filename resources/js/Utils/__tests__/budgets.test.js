import { buildBurnDown, getBudgetStatus, toNumber } from '../budgets';

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

it('buildBurnDown accumulates daily spending and projects the period total', () => {
    const result = buildBurnDown(
        [
            { date: '2026-09-01', amount: 100 },
            { date: '2026-09-02', amount: 0 },
            { date: '2026-09-03', amount: 50 },
        ],
        30,
        900,
    );

    expect(result.points.map((p) => p.total)).toEqual([0, 100, 100, 150]);
    expect(result.spent).toBe(150);
    expect(result.averagePerDay).toBe(50);
    expect(result.projected).toBe(1500);
    expect(result.evenPacePerDay).toBe(30);
});

it('buildBurnDown handles a period with no elapsed days', () => {
    const result = buildBurnDown([], 30, 900);

    expect(result.projected).toBe(0);
    expect(result.averagePerDay).toBe(0);
});
