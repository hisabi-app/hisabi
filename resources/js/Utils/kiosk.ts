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
// to fill the page when there are few, and shrink to fit when there are many or the columns get narrow.
// A narrow card (an answer beside another) lists up to 8 down one column when they read larger that way.
export const categoryLayout = (count: number, heightEm: number, widthEm: number): { columns: number; rows: number; scale: number } => {
    const fit = (columns: number) => {
        const rows = Math.max(1, Math.ceil(count / columns));

        // Not measured yet
        if (heightEm <= 0 || widthEm <= 0) return { columns, rows, scale: 1 };

        const tallest = heightEm / (rows * CATEGORY_ROW + (rows - 1) * CATEGORY_ROW_GAP);
        const widest = widthEm / (columns * CATEGORY_COLUMN + (columns - 1) * CATEGORY_COLUMN_GAP);

        return { columns, rows, scale: Math.max(0.6, Math.min(1.3, tallest, widest)) };
    };

    const layout = fit(count > 14 ? 3 : 2);
    const single = count <= 8 ? fit(1) : null;

    return single && single.scale > layout.scale ? single : layout;
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

// Listening for a question: a moment to hear the room first, then the question counts as over after
// ~0.9s of quiet once at least 0.3s of speech was heard (transcribers make up words from silence),
// at 12s, or with no speech after 6s
export const LISTEN = { settleMs: 300, minSpeechMs: 300, quietMs: 900, maxMs: 12000, giveUpMs: 6000 };

export interface ListenState {
    startedAt: number;
    lastAt: number;
    // The room's background level (RMS), so a humming fridge isn't taken for speech
    noise: number;
    speechMs: number;
    lastSpeechAt: number | null;
}

export type ListenDecision = 'listen' | 'done' | 'no-speech';

export const startListening = (now: number): ListenState => ({ startedAt: now, lastAt: now, noise: 0, speechMs: 0, lastSpeechAt: null });

const speechThreshold = (noise: number): number => Math.max(noise * 2.5, 0.015);

export const listenStep = (state: ListenState, rms: number, now: number): { state: ListenState; decision: ListenDecision } => {
    const elapsed = now - state.startedAt;
    const next = { ...state, lastAt: now };

    if (elapsed < LISTEN.settleMs) {
        next.noise = state.noise === 0 ? rms : state.noise * 0.8 + rms * 0.2;

        return { state: next, decision: 'listen' };
    }

    if (rms > speechThreshold(state.noise)) {
        next.speechMs = state.speechMs + (now - state.lastAt);
        next.lastSpeechAt = now;
    } else {
        // The room can change while listening (a tap running), so the background keeps adjusting in the pauses
        next.noise = state.noise * 0.98 + rms * 0.02;
    }

    const heard = next.speechMs >= LISTEN.minSpeechMs;

    if (heard && next.lastSpeechAt !== null && now - next.lastSpeechAt >= LISTEN.quietMs) return { state: next, decision: 'done' };
    if (elapsed >= LISTEN.maxMs) return { state: next, decision: heard ? 'done' : 'no-speech' };
    if (!heard && elapsed >= LISTEN.giveUpMs) return { state: next, decision: 'no-speech' };

    return { state: next, decision: 'listen' };
};

export const hasHeardSpeech = (state: ListenState): boolean => state.speechMs >= LISTEN.minSpeechMs;

// How loud the voice is above the room, 0 to 1, for the listening ring
export const voiceLevel = (rms: number, noise: number): number => Math.max(0, Math.min(1, (rms - noise) / 0.15));

// Which points get a label under a trend's line: all of up to 12, else every so many, always the last
export const trendLabelIndices = (count: number, most = 12): number[] => {
    if (count <= most) return Array.from({ length: count }, (_, i) => i);

    const step = Math.ceil(count / most);
    const indices = Array.from({ length: count }, (_, i) => i).filter((i) => i % step === 0);
    const last = count - 1;

    // The last label wins over one too close before it
    if (last - indices[indices.length - 1] <= step / 2) indices.pop();

    return [...indices, last];
};

// The values a trend's line spans: totals from zero, so their size shows; a balance close around its values, so its shape shows
export const trendDomain = (values: number[], running: boolean): [number, number] => {
    if (values.length === 0) return [0, 1];

    const lowest = Math.min(...values);
    const highest = Math.max(...values);

    if (!running) {
        const floor = Math.min(0, lowest);

        return [floor, highest > floor ? highest : floor + 1];
    }

    const room = (highest - lowest) * 0.15 || Math.abs(highest) * 0.1 || 1;

    return [lowest - room, highest + room];
};
