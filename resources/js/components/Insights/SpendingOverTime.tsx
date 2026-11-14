import { format, parseISO } from 'date-fns';

import { Skeleton } from '@/components/ui/skeleton';
import ChartTip, { useChartHover } from '@/components/Wallet/ChartTip';
import Panel from '@/components/Wallet/Panel';
import { money } from '@/components/Wallet/format';
import { getSpendingOverTime } from '@/Api/insights';
import type { PeriodKind, QueryRange } from './period';
import { useMetric } from './useMetric';

interface Bar {
    period: string;
    total: number;
}

const short = (value: number) => (value >= 1000 ? `${(value / 1000).toFixed(value >= 10000 ? 0 : 1)}k` : String(Math.round(value)));

function Bars({ bars, byDay }: { bars: Bar[]; byDay: boolean }) {
    const W = 380;
    const H = 170;
    const L = 4;
    const R = 376;
    const T = 20;
    const B = 140;
    const max = Math.max(1, ...bars.map((bar) => bar.total)) * 1.08;
    const slot = (R - L) / bars.length;
    const gap = Math.min(8, slot * 0.25);
    const width = Math.max(2, slot - gap);
    const peak = bars.reduce((best, bar, i) => (bar.total > bars[best].total ? i : best), 0);
    const label = (bar: Bar) => (byDay ? format(parseISO(bar.period), 'd') : format(parseISO(`${bar.period}-01`), 'MMM').charAt(0));
    const everyNth = byDay ? 5 : bars.length > 14 ? 3 : 1;
    const top = (bar: Bar) => B - (bar.total / max) * (B - T);
    const { index: hovered, svgProps } = useChartHover(W, bars.length, (x) => Math.min(bars.length - 1, Math.max(0, Math.floor((x - L) / slot))));

    return (
        <div className="relative">
            <svg viewBox={`0 0 ${W} ${H}`} className="block h-auto w-full overflow-visible" role="img" aria-label="Spending over time" {...svgProps}>
                <line x1={L} x2={R} y1={B} y2={B} stroke="var(--border)" />
                {bars.map((bar, i) => {
                    const height = (bar.total / max) * (B - T);
                    const x = L + i * slot + gap / 2;
                    const last = i === bars.length - 1;
                    const showValue = last || (i === peak && bar.total > 0);

                    return (
                        <g key={bar.period}>
                            <rect
                                className="grow-y transition-[fill] duration-150"
                                style={{ '--j': i } as React.CSSProperties}
                                x={x.toFixed(1)}
                                y={(B - height).toFixed(1)}
                                width={width.toFixed(1)}
                                height={Math.max(0, height).toFixed(1)}
                                rx={Math.min(4, width / 2)}
                                fill={last ? 'var(--foreground)' : i === hovered ? 'oklch(0.62 0.006 286)' : 'oklch(0.86 0.004 286)'}
                            />
                            {(i % everyNth === 0 || last) && (
                                <text x={(x + width / 2).toFixed(1)} y={B + 18} textAnchor="middle" fontSize="10.5" fill="var(--muted-foreground)">
                                    {label(bar)}
                                </text>
                            )}
                            {showValue && (
                                <text
                                    x={(x + width / 2).toFixed(1)}
                                    y={(B - height - 7).toFixed(1)}
                                    textAnchor="middle"
                                    fontSize="11"
                                    fontWeight={last ? 700 : 400}
                                    fill={last ? 'var(--foreground)' : 'var(--muted-foreground)'}
                                    // The tooltip takes over while it's up
                                    opacity={hovered === null ? 1 : 0}
                                    className="transition-opacity duration-150"
                                >
                                    {short(bar.total)}
                                </text>
                            )}
                        </g>
                    );
                })}
            </svg>
            {hovered !== null && (
                <ChartTip x={L + hovered * slot + slot / 2} y={top(bars[hovered])} width={W} height={H}>
                    <span className="text-background/70">
                        {byDay ? format(parseISO(bars[hovered].period), 'EEE d MMM') : format(parseISO(`${bars[hovered].period}-01`), 'MMMM yyyy')}
                    </span>
                    <b className="text-[13px] font-bold font-rounded tabular">{money(bars[hovered].total)}</b>
                </ChartTip>
            )}
        </div>
    );
}

export default function SpendingOverTime({ range, kind, index, version }: { range: QueryRange | null; kind: PeriodKind; index: number; version: number }) {
    const byDay = kind === 'month';
    const { data, error } = useMetric<Bar[]>(() => getSpendingOverTime(range, byDay ? 'day' : 'month'), `over-time-${range?.from}-${range?.to}-${byDay}-${version}`);

    return (
        <Panel title={byDay ? 'Spending by day' : 'Spending by month'} index={index}>
            {!data && !error && <Skeleton className="mt-2 h-40 w-full" />}
            {!data && error && <p className="py-2 text-muted-foreground">Couldn’t load this. Try again in a moment.</p>}
            {data && data.length === 0 && <p className="py-2 text-muted-foreground">No spending in this period.</p>}
            {data && data.length > 0 && (
                <div className="mt-2">
                    <Bars key={`${range?.from}-${byDay}`} bars={data} byDay={byDay} />
                </div>
            )}
        </Panel>
    );
}
