import { format, parseISO } from 'date-fns';

// "2026-11-14" → "14 Nov"
export const shortDate = (date: string) => format(parseISO(date), 'd MMM');

// "2026-11-14" → "November"
export const monthName = (date: Date | string) => format(typeof date === 'string' ? parseISO(date) : date, 'MMMM');
