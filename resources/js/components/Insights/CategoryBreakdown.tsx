import { Link } from '@inertiajs/react';

import { Skeleton } from '@/components/ui/skeleton';
import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount } from '@/components/Wallet/format';
import { getCategoryBreakdown } from '@/Api/insights';
import type { QueryRange } from './period';
import { useMetric } from './useMetric';

interface Row {
    id: number;
    name: string;
    color: string | null;
    icon: string | null;
    total: number;
    count: number;
}

export const transactionsLink = (filter: string, range: QueryRange | null) => {
    const params = new URLSearchParams(filter);
    if (range) {
        params.set('dateFrom', range.from);
        params.set('dateTo', range.to);
    }

    return `/transactions?${params}`;
};

export default function CategoryBreakdown({ range, index, version }: { range: QueryRange | null; index: number; version: number }) {
    const { data } = useMetric<Row[]>(() => getCategoryBreakdown(range), `categories-${range?.from}-${range?.to}-${version}`);
    const top = data?.[0]?.total ?? 1;

    return (
        <Panel title="Spending by category" index={index}>
            {!data && <Skeleton className="mt-2 h-60 w-full" />}
            {data && data.length === 0 && <p className="py-2 text-muted-foreground">Nothing spent in this period.</p>}
            {data && data.length > 0 && (
                <div className="divide-y">
                    {data.map((row, j) => (
                        <Link
                            key={row.id}
                            href={transactionsLink(`category=${row.id}`, range)}
                            className="grid grid-cols-[30px_minmax(0,1fr)_auto] items-center gap-x-2.5 gap-y-1 py-2 transition-colors duration-150 hover:bg-secondary/60"
                        >
                            <Tile color={row.color} icon={row.icon} size="sm" className="row-span-2" />
                            <span className="truncate font-semibold">{row.name}</span>
                            <span className="font-semibold font-rounded tabular">{amount(row.total)}</span>
                            <span className="relative col-span-2 col-start-2 h-[5px] overflow-hidden rounded-full bg-track">
                                <i
                                    className="grow-x absolute inset-y-0 left-0 rounded-full"
                                    style={{ width: `${(row.total / top) * 100}%`, background: categoryColor(row.color), '--j': j } as React.CSSProperties}
                                />
                            </span>
                        </Link>
                    ))}
                </div>
            )}
        </Panel>
    );
}
