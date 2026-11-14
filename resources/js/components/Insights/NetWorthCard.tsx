import { endOfMonth, format, parseISO, startOfMonth, subMonths } from 'date-fns';

import { Skeleton } from '@/components/ui/skeleton';
import CountUp from '@/components/Wallet/CountUp';
import Panel from '@/components/Wallet/Panel';
import { amount } from '@/components/Wallet/format';
import { getNetWorth, getNetWorthTrend } from '@/Api/insights';
import { getAppCurrency } from '@/Utils';
import { useMetric } from './useMetric';

interface Point {
    label: string;
    value: number;
}

function NetWorthChart({ points }: { points: Point[] }) {
    const W = 760;
    const H = 170;
    const L = 46;
    const R = 740;
    const T = 14;
    const B = 140;
    const values = points.map((point) => point.value);
    const min = Math.min(...values);
    const max = Math.max(...values);
    const pad = (max - min || Math.abs(max) || 1) * 0.15;
    const lo = min - pad;
    const hi = max + pad;
    const x = (i: number) => L + (points.length === 1 ? 0 : (i / (points.length - 1)) * (R - L));
    const y = (value: number) => B - ((value - lo) / (hi - lo)) * (B - T);
    const line = points.map((point, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)} ${y(point.value).toFixed(1)}`).join(' ');
    const area = `${line} L${x(points.length - 1)} ${B} L${x(0)} ${B} Z`;
    const ticks = [0, 1, 2, 3].map((i) => lo + ((hi - lo) * (i + 0.5)) / 4);
    const short = (value: number) => (Math.abs(value) >= 1000 ? `${Math.round(value / 1000)}k` : String(Math.round(value)));

    return (
        <svg viewBox={`0 0 ${W} ${H}`} className="block h-auto w-full overflow-visible" role="img" aria-label="Net worth by month">
            {ticks.map((tick) => (
                <g key={tick}>
                    <line x1={L} x2={R} y1={y(tick)} y2={y(tick)} stroke="var(--border)" />
                    <text x={L - 8} y={y(tick) + 4} textAnchor="end" fontSize="11" fill="var(--muted-foreground)">
                        {short(tick)}
                    </text>
                </g>
            ))}
            {points.map((point, i) =>
                i % 2 === 0 || i === points.length - 1 ? (
                    <text key={point.label} x={x(i)} y={B + 20} textAnchor="middle" fontSize="11" fill="var(--muted-foreground)">
                        {format(parseISO(`${point.label}-01`), 'MMM')}
                    </text>
                ) : null,
            )}
            <path className="fade-in-late" d={area} fill="oklch(0.2 0.004 286 / 0.05)" />
            <path className="line-draw" pathLength={1} d={line} fill="none" stroke="var(--foreground)" strokeWidth="2.5" strokeLinejoin="round" strokeLinecap="round" />
            {points.map((point, i) => (
                <circle key={point.label} cx={x(i)} cy={y(point.value)} r="10" fill="transparent">
                    <title>
                        {format(parseISO(`${point.label}-01`), 'MMMM yyyy')}: {getAppCurrency()} {amount(point.value)}
                    </title>
                </circle>
            ))}
            <circle className="fade-in-late" cx={x(points.length - 1)} cy={y(values[values.length - 1])} r="5" fill="var(--foreground)" stroke="var(--card)" strokeWidth="2" />
        </svg>
    );
}

// Net worth is a long-term number, so it always looks back over the last 12 months
export default function NetWorthCard({ index, version }: { index: number; version: number }) {
    const now = new Date();
    const range = { from: format(startOfMonth(subMonths(now, 11)), 'yyyy-MM-dd'), to: format(endOfMonth(now), 'yyyy-MM-dd') };
    const total = useMetric<{ value: number }>(getNetWorth, `net-worth-${version}`);
    const trend = useMetric<Point[]>(() => getNetWorthTrend(range), `net-worth-trend-${version}`);
    const points = (trend.data ?? []).map((point) => ({ label: point.label, value: Number(point.value) }));
    const change = points.length > 1 ? points[points.length - 1].value - points[0].value : null;

    return (
        <Panel title="Net worth" index={index}>
            {total.error && trend.error && <p className="py-2 text-muted-foreground">Couldn’t load this. Try again in a moment.</p>}
            {total.data === null ? (
                !total.error && <Skeleton className="mt-2 h-10 w-52" />
            ) : (
                <p className="text-[40px] leading-[1.1] font-bold tracking-[-0.03em] font-rounded">
                    <small className="mr-1 text-[0.45em] font-semibold tracking-normal text-muted-foreground">{getAppCurrency()}</small>
                    <CountUp value={Number(total.data.value)} format={(value) => amount(value)} />
                </p>
            )}
            {change !== null && (
                <p className="mt-1 text-[13px] text-muted-foreground">
                    <span className="mr-1 inline-block rounded-full bg-secondary px-2 font-bold text-foreground font-rounded tabular">
                        {change >= 0 ? '↑' : '↓'} {amount(Math.abs(change))}
                    </span>
                    over the last 12 months
                </p>
            )}
            <div className="mt-4">{trend.data === null ? !trend.error && <Skeleton className="h-36 w-full" /> : points.length > 1 && <NetWorthChart points={points} />}</div>
        </Panel>
    );
}
