import { useCallback, useEffect, useMemo, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { ChatCircleTextIcon } from '@phosphor-icons/react';

import Authenticated from '@/Layouts/Authenticated';
import BudgetRings from '@/components/Home/BudgetRings';
import BudgetSuggestion from '@/components/Home/BudgetSuggestion';
import DetailSheet from '@/components/Home/DetailSheet';
import FirstRun from '@/components/Home/FirstRun';
import HomeSkeleton from '@/components/Home/HomeSkeleton';
import Insights from '@/components/Home/Insights';
import MonthCard from '@/components/Home/MonthCard';
import NeedsYou, { type NeedsYouItem } from '@/components/Home/NeedsYou';
import Recent from '@/components/Home/Recent';
import { monthName } from '@/components/Home/dates';
import type { Detail, HomeData } from '@/components/Home/types';
import EmptyState from '@/components/Wallet/EmptyState';
import PageHeader from '@/components/Wallet/PageHeader';
import Tile from '@/components/Wallet/Tile';
import { amount } from '@/components/Wallet/format';
import { Button } from '@/components/ui/button';
import { getHome } from '@/Api/home';
import { requestAdd, useDataChanged } from '@/hooks/useDataChanged';

export default function Home({ auth }: { auth: any }) {
    const [data, setData] = useState<HomeData | null>(null);
    const [error, setError] = useState(false);
    const [detail, setDetail] = useState<Detail>(null);

    const load = useCallback(() => {
        getHome()
            .then((home: HomeData) => {
                setData(home);
                setError(false);
            })
            .catch(() => setError(true));
    }, []);

    useEffect(load, [load]);
    useDataChanged(load);

    const closeDetail = () => {
        setDetail(null);
        load();
    };

    const needsYou = useMemo<NeedsYouItem[]>(() => {
        if (!data) return [];

        const { brands, budgets, unparsed_sms: unparsedSms } = data.needs_you;
        const budgetLook = (id: number) => data.budgets.find((budget) => budget.id === id);
        const items: NeedsYouItem[] = [];

        if (brands.count > 0) {
            items.push({
                key: 'brands',
                tile: <Tile />,
                title: brands.count === 1 ? '1 new brand needs a category' : `${brands.count} new brands need a category`,
                detail: brands.items.slice(0, 3).map((brand) => brand.name).join(', '),
                action: 'Assign',
                onAction: () => setDetail({ kind: 'brands' }),
            });
        }

        budgets.forEach((budget) => {
            const look = budgetLook(budget.id);
            items.push({
                key: `budget-${budget.id}`,
                tile: <Tile color={look?.color} icon={look?.icon ?? 'Wallet'} />,
                title: budget.status === 'over' ? `${budget.name} is over budget` : `${budget.name} is ahead of pace`,
                detail: `${amount(budget.spent)} of ${amount(budget.amount)}`,
                action: 'Review',
                onAction: () => setDetail({ kind: 'budget', id: budget.id }),
            });
        });

        if (unparsedSms > 0) {
            items.push({
                key: 'sms',
                tile: (
                    <span className="grid size-9 place-items-center rounded-[10px] bg-secondary text-muted-foreground ring-1 ring-border ring-inset">
                        <ChatCircleTextIcon size={18} weight="bold" aria-hidden="true" />
                    </span>
                ),
                title: unparsedSms === 1 ? '1 SMS couldn’t be read' : `${unparsedSms} SMS couldn’t be read`,
                detail: 'Fix the text or delete it',
                action: 'Review',
                onAction: () => requestAdd('sms'),
            });
        }

        return items;
    }, [data]);

    const firstRunDone = () => {
        load();
        router.reload({ only: ['nav'] });
    };

    let content: React.ReactNode;

    if (!data) {
        content = error ? (
            <EmptyState title="Couldn’t load your home" body="Check your connection and try again." action={<Button onClick={load}>Try again</Button>} />
        ) : (
            <HomeSkeleton />
        );
    } else if (data.stage === 'new') {
        content = <FirstRun onDone={firstRunDone} />;
    } else {
        const isFull = data.stage === 'full';
        const showInsights = isFull && data.insights.length > 0;
        const hasBudgets = data.budgets.length > 0;
        const suggestion = data.suggestion && (
            <BudgetSuggestion suggestion={data.suggestion} index={showInsights ? 4 : 3} onDismissed={() => setData({ ...data, suggestion: null })} />
        );

        content = (
            <>
                <PageHeader eyebrow="This month" title={monthName(new Date())} />

                <NeedsYou items={needsYou} index={1} />

                <div className="grid gap-3.5 md:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] md:[&:has(>:only-child)]:grid-cols-1">
                    <MonthCard month={data.month} index={2} />
                    {showInsights ? <Insights insights={data.insights} index={3} /> : suggestion}
                </div>

                <div className="grid gap-3.5 md:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] md:[&:has(>:only-child)]:grid-cols-1">
                    {hasBudgets ? <BudgetRings budgets={data.budgets} index={4} onOpen={(id) => setDetail({ kind: 'budget', id })} /> : showInsights && suggestion}
                    {data.recent.length > 0 && <Recent transactions={data.recent} index={5} onChooseCategory={() => setDetail({ kind: 'brands' })} />}
                </div>
            </>
        );
    }

    return (
        <Authenticated auth={auth}>
            <Head title="Home" />

            <div className="mx-auto grid w-full max-w-[1100px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">{content}</div>

            <DetailSheet detail={detail} brands={data?.needs_you.brands.items ?? []} onClose={closeDetail} />
        </Authenticated>
    );
}
