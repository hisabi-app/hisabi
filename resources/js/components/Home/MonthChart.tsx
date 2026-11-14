import { format, getDaysInMonth, setDate, startOfMonth, subMonths } from 'date-fns';

import ChartTip, { nearest, useChartHover } from '@/components/Wallet/ChartTip';
import { money } from '@/components/Wallet/format';

const LAST_MONTH = 'oklch(0.75 0.005 286)';

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
    const now = new Date();
    const days = Math.max(lastMonth.length, getDaysInMonth(now));
    const max = Math.max(1, ...daily, ...lastMonth) * 1.05;
    const x = (day: number) => L + ((day - 1) / (days - 1)) * (R - L);
    const y = (value: number) => B - (value / max) * (B - T);
    const path = (values: number[], from = 1) =>
        values.map((value, i) => `${i ? 'L' : 'M'}${x(from + i).toFixed(1)} ${y(value).toFixed(1)}`).join(' ');
    const endX = x(firstDay + daily.length - 1);
    const endY = y(daily[daily.length - 1] ?? 0);

    // Hovering a day shows both running totals on it, as far as each line goes
    const { index, svgProps } = useChartHover(W, days, (pointerX) => nearest(pointerX, L, R, days));
    const day = index === null ? null : index + 1;
    const current = day !== null && daily.length > 1 && day >= firstDay ? daily[day - firstDay] : undefined;
    const previous = day !== null && lastMonth.length > 1 ? lastMonth[day - 1] : undefined;
    const shown = [current, previous].filter((value) => value !== undefined);

    return (
        <div className="relative">
            <svg viewBox={`0 0 ${W} ${H}`} className="block h-auto w-full overflow-visible" role="img" aria-label={label} {...svgProps}>
                <line x1={L} x2={R} y1={B} y2={B} stroke="var(--border)" />
                {day !== null && shown.length > 0 && (
                    <line x1={x(day)} x2={x(day)} y1={T} y2={B} stroke="var(--muted-foreground)" strokeOpacity="0.35" />
                )}
                {lastMonth.length > 1 && (
                    <path className="fade-in-late" d={path(lastMonth)} fill="none" stroke={LAST_MONTH} strokeWidth="2" strokeDasharray="4 4" />
                )}
                {daily.length > 1 && (
                    <path
                        className="line-draw"
                        pathLength={1}
                        d={path(daily, firstDay)}
                        fill="none"
                        stroke="var(--foreground)"
                        strokeWidth="2.5"
                        strokeLinejoin="round"
                        strokeLinecap="round"
                    />
                )}
                <g className="fade-in-late">
                    <circle cx={endX} cy={endY} r="3.5" fill="var(--foreground)" stroke="var(--card)" strokeWidth="1.5" />
                    <text x={endX + 7} y={endY + 3.5} fontSize="9" fontWeight="700" fill="var(--foreground)">
                        Today
                    </text>
                </g>
                {day !== null && previous !== undefined && (
                    <circle cx={x(day)} cy={y(previous)} r="3.5" fill={LAST_MONTH} stroke="var(--card)" strokeWidth="1.5" />
                )}
                {day !== null && current !== undefined && (
                    <circle cx={x(day)} cy={y(current)} r="3.5" fill="var(--foreground)" stroke="var(--card)" strokeWidth="1.5" />
                )}
            </svg>
            {day !== null && shown.length > 0 && (
                <ChartTip x={x(day)} y={y(Math.max(...(shown as number[])))} width={W} height={H}>
                    <span className="grid grid-cols-[auto_auto] items-baseline gap-x-3 gap-y-0.5">
                        {current !== undefined && (
                            <>
                                <span className="text-background/70">By {format(setDate(now, day), 'd MMM')}</span>
                                <b className="text-right text-[13px] font-bold font-rounded tabular">{money(current)}</b>
                            </>
                        )}
                        {previous !== undefined && (
                            <>
                                <span className="text-background/70">By {format(setDate(subMonths(startOfMonth(now), 1), day), 'd MMM')}</span>
                                <b className="text-right text-[13px] font-bold font-rounded tabular">{money(previous)}</b>
                            </>
                        )}
                    </span>
                </ChartTip>
            )}
        </div>
    );
}
