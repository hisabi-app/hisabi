import {
    addMonths,
    addQuarters,
    addYears,
    endOfMonth,
    endOfQuarter,
    endOfYear,
    format,
    getQuarter,
    isSameYear,
    startOfMonth,
    startOfQuarter,
    startOfYear,
} from 'date-fns';

export type PeriodKind = 'month' | 'quarter' | 'year' | 'all';

export interface Period {
    kind: PeriodKind;
    // Any date inside the period
    anchor: Date;
}

export interface QueryRange {
    from: string;
    to: string;
}

export const PERIOD_KINDS: { kind: PeriodKind; label: string }[] = [
    { kind: 'month', label: 'Month' },
    { kind: 'quarter', label: 'Quarter' },
    { kind: 'year', label: 'Year' },
    { kind: 'all', label: 'All time' },
];

export const periodDates = ({ kind, anchor }: Period): { from: Date; to: Date } | null => {
    switch (kind) {
        case 'month':
            return { from: startOfMonth(anchor), to: endOfMonth(anchor) };
        case 'quarter':
            return { from: startOfQuarter(anchor), to: endOfQuarter(anchor) };
        case 'year':
            return { from: startOfYear(anchor), to: endOfYear(anchor) };
        default:
            return null;
    }
};

export const toQuery = (period: Period): QueryRange | null => {
    const dates = periodDates(period);

    return dates ? { from: format(dates.from, 'yyyy-MM-dd'), to: format(dates.to, 'yyyy-MM-dd') } : null;
};

export const shiftPeriod = (period: Period, step: 1 | -1): Period => {
    const shift = { month: addMonths, quarter: addQuarters, year: addYears, all: (date: Date) => date }[period.kind];

    return { ...period, anchor: shift(period.anchor, step) };
};

// The current period can't move forward: there's nothing there yet
export const isCurrent = (period: Period, now = new Date()) => {
    const dates = periodDates(period);

    return !dates || dates.to >= now;
};

export const periodTitle = (period: Period, now = new Date()): string => {
    const { kind, anchor } = period;
    const current = isCurrent(period, now);

    switch (kind) {
        case 'month':
            return isSameYear(anchor, now) ? format(anchor, 'MMMM') : format(anchor, 'MMMM yyyy');
        case 'quarter':
            return `Q${getQuarter(anchor)} ${format(anchor, 'yyyy')}${current ? ' so far' : ''}`;
        case 'year':
            return `${format(anchor, 'yyyy')}${current ? ' so far' : ''}`;
        default:
            return 'All time';
    }
};
