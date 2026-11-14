import { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';

import Authenticated from '@/Layouts/Authenticated';
import CategoryBreakdown from '@/components/Insights/CategoryBreakdown';
import IncomeSplit from '@/components/Insights/IncomeSplit';
import MoreCharts from '@/components/Insights/MoreCharts';
import NetWorthCard from '@/components/Insights/NetWorthCard';
import PeriodControl from '@/components/Insights/PeriodControl';
import SpendingOverTime from '@/components/Insights/SpendingOverTime';
import TopBrands from '@/components/Insights/TopBrands';
import { periodTitle, toQuery, type Period } from '@/components/Insights/period';
import type { Navigation } from '@/components/Shell/navigation';
import EmptyState from '@/components/Wallet/EmptyState';
import PageHeader from '@/components/Wallet/PageHeader';
import { useDataChanged } from '@/hooks/useDataChanged';

export default function Insights({ auth, hasData }: { auth: any; hasData: boolean }) {
    const { nav } = usePage<{ nav?: Navigation | null }>().props;
    const [period, setPeriod] = useState<Period>({ kind: 'year', anchor: new Date() });
    // Bumped when transactions change elsewhere, so every section reloads
    const [version, setVersion] = useState(0);
    useDataChanged(() => setVersion((current) => current + 1));

    const range = toQuery(period);
    // Before a full month there's no history to draw, so only the breakdowns show
    const hasHistory = nav?.stage !== 'early' && nav?.stage !== 'new';

    return (
        <Authenticated auth={auth}>
            <Head title="Insights" />

            <div className="mx-auto grid w-full max-w-[1100px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">
                <PageHeader eyebrow="Insights" title={periodTitle(period)} actions={hasData && <PeriodControl period={period} onChange={setPeriod} />} />

                {!hasData && (
                    <EmptyState
                        className="rise rounded-2xl bg-card shadow-card"
                        title="Nothing to look at yet"
                        body="Insights fill in as transactions come in. Paste a bank SMS from Add to get started."
                    />
                )}

                {hasData && hasHistory && (
                    <>
                        <NetWorthCard index={1} version={version} />
                        <IncomeSplit range={range} index={2} version={version} />
                    </>
                )}

                {hasData && (
                    <div className="grid items-start gap-3.5 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <CategoryBreakdown range={range} index={3} version={version} />
                        <div className="grid gap-3.5">
                            {hasHistory && <SpendingOverTime range={range} kind={period.kind} index={4} version={version} />}
                            <TopBrands range={range} index={5} version={version} />
                        </div>
                    </div>
                )}

                {hasData && hasHistory && <MoreCharts period={period} index={6} />}
            </div>
        </Authenticated>
    );
}
