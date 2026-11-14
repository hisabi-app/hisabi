import { formatNumber, getAppCurrency } from '@/Utils';

// Amounts arrive as numbers or number_format()'ed strings ("1,234.50")
export const toAmount = (value: number | string | null | undefined): number => {
    const parsed = Number(String(value ?? 0).replace(/,/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

// 9486 → "9,486", with decimals → "312.40"
export const amount = (value: number | string, decimals = 0): string =>
    formatNumber(toAmount(value), decimals > 0 ? `0,0.${'0'.repeat(decimals)}` : '0,0');

// 9486 → "AED 9,486"
export const money = (value: number | string, decimals = 0): string => `${getAppCurrency() ?? ''} ${amount(value, decimals)}`.trim();

// Props for an element that rises in as part of a page's arrival
export const rise = (index: number) => ({
    className: 'rise',
    style: { '--i': index } as React.CSSProperties,
});
