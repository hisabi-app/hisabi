import { getDaysInMonth } from 'date-fns';

// Spending so far this month (solid) against the whole of last month (dashed), as running totals
interface MonthChartProps {
    daily: number[];
    lastMonth: number[];
    label: string;
    // Day of the month the first value of `daily` belongs to (later than 1 in the first month)
    firstDay?: number;
}

export default function MonthChart({ daily, lastMonth, label, firstDay = 1 }: MonthChartProps) {
    const W = 340;
    const H = 86;
    const L = 2;
    const R = 300;
    const T = 8;
    const B = 80;
    const days = Math.max(lastMonth.length, getDaysInMonth(new Date()));
    const max = Math.max(1, ...daily, ...lastMonth) * 1.05;
    const x = (day: number) => L + ((day - 1) / (days - 1)) * (R - L);
    const y = (value: number) => B - (value / max) * (B - T);
    const path = (values: number[], from = 1) => values.map((value, i) => `${i ? 'L' : 'M'}${x(from + i).toFixed(1)} ${y(value).toFixed(1)}`).join(' ');
    const endX = x(firstDay + daily.length - 1);
    const endY = y(daily[daily.length - 1] ?? 0);

    return (
        <svg viewBox={`0 0 ${W} ${H}`} className="block h-auto w-full overflow-visible" role="img" aria-label={label}>
            <line x1={L} x2={R} y1={B} y2={B} stroke="var(--border)" />
            {lastMonth.length > 1 && (
                <path className="fade-in-late" d={path(lastMonth)} fill="none" stroke="oklch(0.75 0.005 286)" strokeWidth="2" strokeDasharray="4 4" />
            )}
            {daily.length > 1 && (
                <path className="line-draw" pathLength={1} d={path(daily, firstDay)} fill="none" stroke="var(--foreground)" strokeWidth="2.5" strokeLinejoin="round" strokeLinecap="round" />
            )}
            <g className="fade-in-late">
                <circle cx={endX} cy={endY} r="3.5" fill="var(--foreground)" stroke="var(--card)" strokeWidth="1.5" />
                <text x={endX + 7} y={endY + 3.5} fontSize="9" fontWeight="700" fill="var(--foreground)">
                    Today
                </text>
            </g>
        </svg>
    );
}
