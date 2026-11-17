import { categoryLayout, compareWithLastMonth, hasHeardSpeech, isNight, listenStep, msUntilDailyReload, startListening, toKioskBudgets, voiceLevel } from '../kiosk';

const budget = (id, amount, spent, elapsed = 43) => ({
    id,
    name: `Budget ${id}`,
    amount,
    total_transactions_amount: spent,
    elapsed_days_percentage: elapsed,
});

it('toKioskBudgets puts budgets that need attention first', () => {
    const budgets = toKioskBudgets([budget(1, 3000, 1180), budget(2, 1100, 1100), budget(3, 2000, '2,240.00'), budget(4, 1500, 920), budget(5, 600, 210)]);

    expect(budgets.map(({ id, status }) => [id, status])).toEqual([
        [3, 'over'],
        [4, 'ahead'],
        [1, 'on_track'],
        [5, 'on_track'],
        [2, 'used'],
    ]);
});

it('toKioskBudgets works out what is left', () => {
    const [over] = toKioskBudgets([budget(1, 2000, 2240)]);

    expect(over).toMatchObject({ amount: 2000, remaining: -240, spentPercentage: 112, elapsedPercentage: 43 });
});

it('compareWithLastMonth rounds the difference', () => {
    expect(compareWithLastMonth(11450, 12770)).toEqual({ direction: 'less', difference: 1320 });
    expect(compareWithLastMonth(900.4, 500)).toEqual({ direction: 'more', difference: 400 });
    expect(compareWithLastMonth(500.2, 500)).toEqual({ direction: 'same', difference: 0 });
});

it('compareWithLastMonth skips the comparison without spending last month', () => {
    expect(compareWithLastMonth(1200, 0)).toBeNull();
});

it('msUntilDailyReload waits until the next 4am', () => {
    const hour = 60 * 60 * 1000;

    expect(msUntilDailyReload(new Date(2026, 10, 13, 2, 0))).toBe(2 * hour);
    expect(msUntilDailyReload(new Date(2026, 10, 13, 18, 0))).toBe(10 * hour);
    expect(msUntilDailyReload(new Date(2026, 10, 13, 4, 0))).toBe(24 * hour);
});

it('toKioskBudgets keeps the category look for the rings', () => {
    const [ring] = toKioskBudgets([{ ...budget(1, 1000, 200), color: 'pink', icon: 'ShoppingBag' }]);

    expect(ring).toMatchObject({ color: 'pink', icon: 'ShoppingBag' });
});

it('isNight is true from 22:00 to 06:00', () => {
    expect(isNight(new Date(2026, 10, 13, 21, 59))).toBe(false);
    expect(isNight(new Date(2026, 10, 13, 22, 0))).toBe(true);
    expect(isNight(new Date(2026, 10, 14, 3, 0))).toBe(true);
    expect(isNight(new Date(2026, 10, 14, 6, 0))).toBe(false);
});

it('categoryLayout lists the categories down two columns, three past 14', () => {
    expect(categoryLayout(9, 25, 60)).toMatchObject({ columns: 2, rows: 5 });
    expect(categoryLayout(14, 25, 60)).toMatchObject({ columns: 2, rows: 7 });
    expect(categoryLayout(20, 25, 60)).toMatchObject({ columns: 3, rows: 7 });
});

it('categoryLayout grows the rows to fill the page, and shrinks them to fit', () => {
    // 7 rows need 7 * 2.3 + 6 * 0.9 = 21.5em, and 3 columns 3 * 18.4 + 2 * 2.4 = 60em
    expect(categoryLayout(20, 25, 80).scale).toBeCloseTo(25 / 21.5);
    expect(categoryLayout(20, 40, 54).scale).toBeCloseTo(54 / 60);
    expect(categoryLayout(3, 25, 60).scale).toBe(1.3);
    expect(categoryLayout(40, 25, 60).scale).toBe(0.6);
    // Not measured yet
    expect(categoryLayout(9, 0, 0).scale).toBe(1);
});

it('categoryLayout lists a few down one column in a narrow card, when they read larger that way', () => {
    // Half the page beside another card: two columns would shrink to 27 / 39.2
    expect(categoryLayout(8, 24, 27)).toMatchObject({ columns: 1, rows: 8 });
    expect(categoryLayout(8, 24, 27).scale).toBeCloseTo(24 / 24.7);
    // The whole page keeps two columns
    expect(categoryLayout(8, 24, 60)).toMatchObject({ columns: 2, rows: 4 });
    expect(categoryLayout(9, 24, 27)).toMatchObject({ columns: 2 });
});

// Feeds listenStep one level every 50ms, as the recorder does, and returns where it stopped
const listenTo = (levels) => {
    let state = startListening(0);

    for (let i = 0; i < levels.length; i++) {
        const step = listenStep(state, levels[i], (i + 1) * 50);
        state = step.state;

        if (step.decision !== 'listen') return { decision: step.decision, at: (i + 1) * 50, state };
    }

    return { decision: 'listen', state };
};

const quiet = (ms, level = 0.004) => Array(ms / 50).fill(level);
const speech = (ms, level = 0.12) => Array(ms / 50).fill(level);

it('listenStep ends the question after a short pause once something was said', () => {
    const { decision, at, state } = listenTo([...quiet(300), ...speech(1500), ...quiet(2000)]);

    expect(decision).toBe('done');
    expect(hasHeardSpeech(state)).toBe(true);
    // 300ms settling + 1.5s of speech + ~0.9s of quiet
    expect(at).toBeGreaterThanOrEqual(2650);
    expect(at).toBeLessThanOrEqual(2800);
});

it('listenStep waits through the pauses inside a question', () => {
    const { decision } = listenTo([...quiet(300), ...speech(800), ...quiet(600), ...speech(800)]);

    expect(decision).toBe('listen');
});

it('listenStep gives up when nobody speaks, and ignores a click or a cough', () => {
    expect(listenTo(quiet(7000)).decision).toBe('no-speech');
    expect(listenTo([...quiet(300), ...speech(100), ...quiet(7000)]).decision).toBe('no-speech');
});

it('listenStep takes a noisy room as the background, not as speech', () => {
    expect(listenTo(quiet(7000, 0.03)).decision).toBe('no-speech');
    expect(listenTo([...quiet(300, 0.03), ...speech(1000, 0.2), ...quiet(1200, 0.03)]).decision).toBe('done');
});

it('listenStep stops a question that runs on at 12 seconds', () => {
    const { decision, at } = listenTo([...quiet(300), ...speech(13000)]);

    expect(decision).toBe('done');
    expect(at).toBe(12000);
});

it('voiceLevel is how far the voice rises above the room', () => {
    expect(voiceLevel(0.004, 0.004)).toBe(0);
    expect(voiceLevel(0.5, 0.004)).toBe(1);
    expect(voiceLevel(0.079, 0.004)).toBeCloseTo(0.5);
});
