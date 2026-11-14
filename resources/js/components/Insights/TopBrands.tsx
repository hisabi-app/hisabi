import { Link } from '@inertiajs/react';

import { Skeleton } from '@/components/ui/skeleton';
import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';
import { amount } from '@/components/Wallet/format';
import { getTopBrands } from '@/Api/insights';
import { transactionsLink } from './CategoryBreakdown';
import type { QueryRange } from './period';
import { useMetric } from './useMetric';

interface Row {
    id: number;
    name: string;
    total: number;
    count: number;
    category: { color: string | null; icon: string | null; name: string } | null;
}

export default function TopBrands({ range, index, version }: { range: QueryRange | null; index: number; version: number }) {
    const { data } = useMetric<Row[]>(() => getTopBrands(range), `brands-${range?.from}-${range?.to}-${version}`);

    return (
        <Panel title="Top brands" more={{ label: 'All', href: '/brands' }} index={index}>
            {!data && <Skeleton className="mt-2 h-48 w-full" />}
            {data && data.length === 0 && <p className="py-2 text-muted-foreground">No spending in this period.</p>}
            {data && data.length > 0 && (
                <div className="divide-y">
                    {data.map((row) => (
                        <Link key={row.id} href={transactionsLink(`brand=${row.id}`, range)} className="grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 py-2.5 transition-colors duration-150 hover:bg-secondary/60">
                            <Tile color={row.category?.color} icon={row.category?.icon} className="row-span-2" label={row.category?.name} />
                            <span className="truncate font-semibold">{row.name}</span>
                            <span className="col-start-2 text-[12.5px] text-muted-foreground">
                                {row.count} {row.count === 1 ? 'transaction' : 'transactions'}
                            </span>
                            <span className="col-start-3 row-span-2 row-start-1 text-sm font-semibold font-rounded tabular">{amount(row.total)}</span>
                        </Link>
                    ))}
                </div>
            )}
        </Panel>
    );
}
