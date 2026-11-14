import { Skeleton } from '@/components/ui/skeleton';
import Panel from '@/components/Wallet/Panel';
import { money } from '@/components/Wallet/format';
import { getIncomeAllocation } from '@/Api/insights';
import type { QueryRange } from './period';
import { useMetric } from './useMetric';

interface Allocation {
    income: number;
    spent: number;
    saved: number;
    invested: number;
}

// Saved and invested were checked to stay distinguishable for colorblind readers
const PARTS = [
    { key: 'spent', label: 'Spent', color: 'var(--foreground)' },
    { key: 'saved', label: 'Saved', color: 'oklch(0.66 0.12 195)' },
    { key: 'invested', label: 'Invested', color: 'oklch(0.56 0.19 300)' },
] as const;

// Where the income went: one bar that adds up, instead of separate cash, savings and investment tiles
export default function IncomeSplit({ range, index, version }: { range: QueryRange | null; index: number; version: number }) {
    const { data, error } = useMetric<Allocation>(() => getIncomeAllocation(range), `allocation-${range?.from}-${range?.to}-${version}`);

    const parts = data
        ? [
              ...PARTS.map((part) => ({ ...part, value: data[part.key] })),
              { key: 'cash', label: 'Still in cash', color: 'var(--cat-gray)', value: Math.max(0, data.income - data.spent - data.saved - data.invested) },
          ].filter((part) => part.value > 0)
        : [];
    const total = parts.reduce((sum, part) => sum + part.value, 0);
    const overspent = data ? data.spent + data.saved + data.invested - data.income : 0;

    return (
        <Panel index={index}>
            <div className="mb-1.5 flex flex-wrap items-baseline justify-between gap-2">
                <h2 className="text-base font-bold tracking-[-0.018em]">Where your income went</h2>
                {data && <span className="font-semibold font-rounded tabular">{money(data.income)} in</span>}
            </div>

            {!data && !error && <Skeleton className="mt-3 h-20 w-full" />}
            {!data && error && <p className="py-2 text-muted-foreground">Couldn’t load this. Try again in a moment.</p>}

            {data && data.income <= 0 && <p className="py-2 text-muted-foreground">No income in this period.</p>}

            {data && data.income > 0 && (
                <>
                    <div className="mt-3 flex h-3.5 gap-[3px]" role="img" aria-label="Income split into spent, saved, invested and cash">
                        {parts.map((part, j) => (
                            <i
                                key={part.key}
                                className="grow-x rounded-[4px]"
                                style={{ flex: part.value, background: part.color, '--j': j } as React.CSSProperties}
                                title={`${part.label}: ${money(part.value)}`}
                            />
                        ))}
                    </div>
                    <div className="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 sm:grid-cols-4">
                        {parts.map((part) => (
                            <div key={part.key} className="grid gap-px">
                                <span className="flex items-center gap-1.5 font-medium text-muted-foreground">
                                    <i className="size-2.5 rounded-[3px]" style={{ background: part.color }} />
                                    {part.label}
                                </span>
                                <b className="text-base font-bold font-rounded tabular">{money(part.value)}</b>
                                <span className="text-xs text-muted-foreground tabular">{Math.round((part.value / total) * 100)}%</span>
                            </div>
                        ))}
                    </div>
                    {overspent > 0 && <p className="mt-3 text-[13px] text-muted-foreground">{money(overspent)} more went out than came in.</p>}
                </>
            )}
        </Panel>
    );
}
