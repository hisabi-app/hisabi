import MonthChart from '@/components/Home/MonthChart';
import CountUp from '@/components/Wallet/CountUp';
import { amount } from '@/components/Wallet/format';
import { useSize } from '@/hooks/useSize';
import { getAppCurrency } from '@/Utils';

import type { LabelledComparison } from './types';

interface SpendingCardProps {
    label: string;
    total: number;
    comparison: LabelledComparison | null;
    // Running totals for the month chart: this month so far, and the month it's compared with (empty for none)
    daily: number[];
    previousDaily: number[];
    chartLabel: string;
}

// A big amount, how it compares, and the month chart in the room left under them
export default function SpendingCard({ label, total, comparison, daily, previousDaily, chartLabel }: SpendingCardProps) {
    const [chartRef, chartBox] = useSize<HTMLDivElement>();
    // The chart takes the room left under the numbers, less a line's gap: its usual shape (340 by 86) when there's space,
    // flatter on wide, short screens, and none when it would be too flat to read
    const chartHeight = chartBox.width > 0 ? Math.min(86, (340 * (chartBox.height - chartBox.em)) / chartBox.width) : 0;

    return (
        <section className="flex min-h-0 flex-col rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            <p className="text-[1.1em] font-medium text-muted-foreground">{label}</p>
            <p className="mt-[0.1em] text-[5em] leading-[1.05] font-bold tracking-[-0.035em] font-rounded">
                <small className="mr-[0.3em] text-[0.3em] font-semibold tracking-normal text-muted-foreground">{getAppCurrency()}</small>
                <CountUp value={total} format={(value) => amount(value)} duration={1200} />
            </p>
            {comparison && (
                <p className="mt-[0.3em] text-[1.1em] text-muted-foreground">
                    {comparison.direction === 'same' ? (
                        comparison.label.replace(/^than /, 'The same as ')
                    ) : (
                        <>
                            <span className="mr-[0.3em] inline-block rounded-full bg-secondary px-[0.5em] font-bold text-foreground font-rounded tabular">
                                {comparison.direction === 'less' ? '↓' : '↑'}{' '}
                                <CountUp value={comparison.difference} format={(value) => amount(value)} duration={1200} />
                            </span>
                            {comparison.direction} {comparison.label}
                        </>
                    )}
                </p>
            )}
            {/* Positioned so the chart's own height never holds the card open */}
            <div ref={chartRef} className="relative min-h-0 flex-1">
                {daily.length > 2 && chartHeight >= 30 && (
                    <div className="absolute inset-x-0 bottom-0">
                        <MonthChart daily={daily} lastMonth={previousDaily} label={chartLabel} height={chartHeight} />
                    </div>
                )}
            </div>
        </section>
    );
}
