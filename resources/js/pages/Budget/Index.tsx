import { useEffect, useState } from 'react';
import { Head } from '@inertiajs/react';
import { PlusIcon } from '@phosphor-icons/react';

import Authenticated from '@/Layouts/Authenticated';
import { Button } from '@/components/ui/button';
import Budgets from '@/components/Domain/Budgets';
import { HistoryRangePicker } from '@/components/Domain/BudgetHistory';
import { useBudgetsHistory } from '@/hooks/useBudgetsHistory';
import Create from './Create';
import PageHeader from '@/components/Wallet/PageHeader';
import { markSeen } from '@/components/Shell/navigation';

type View = 'now' | 'history';

const VIEWS: { value: View; label: string }[] = [
    { value: 'now', label: 'Now' },
    { value: 'history', label: 'History' },
];

export default function Index({ auth, hasHistory }: { auth: any; hasHistory: boolean }) {
    const [view, setView] = useState<View>('now');
    const history = useBudgetsHistory();
    const [refreshKey, setRefreshKey] = useState(0);
    // /budgets?create=1&category=3&name=Groceries opens the form prefilled (from the home's suggestion)
    const [prefill, setPrefill] = useState(() => {
        const params = new URLSearchParams(window.location.search);
        const category = Number(params.get('category'));

        return params.get('create') ? { name: params.get('name') ?? '', categoryIds: category ? [category] : [] } : null;
    });
    const [showCreate, setShowCreate] = useState(prefill !== null);

    useEffect(() => markSeen('budgets'), []);

    const closeCreate = () => {
        setShowCreate(false);
        if (!prefill) return;

        window.history.replaceState({}, '', window.location.pathname);
        setPrefill(null);
    };

    const refresh = () => {
        history.reload();
        setRefreshKey((key) => key + 1);
    };

    const onCreated = () => {
        closeCreate();
        refresh();
    };

    const selectView = (value: View) => {
        setView(value);
        if (value === 'history') history.load();
    };

    const actions = (
        <>
                {view === 'history' && history.state.data && <HistoryRangePicker meta={history.state.data.meta} onChange={history.load} />}
                {hasHistory && (
                    <div role="group" aria-label="Budgets view" className="inline-flex h-9 items-center rounded-full bg-card p-[3px] shadow-card ring-1 ring-border ring-inset">
                        {VIEWS.map(({ value, label }) => (
                            <button
                                key={value}
                                type="button"
                                aria-pressed={view === value}
                                onClick={() => selectView(value)}
                                className={`h-full rounded-full px-3.5 text-[13px] font-semibold transition-colors duration-200 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none ${
                                    view === value ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                )}
                <Button onClick={() => setShowCreate(true)} aria-label="New budget">
                    <PlusIcon weight="bold" aria-hidden="true" />
                    <span className="hidden sm:inline">New budget</span>
                </Button>
        </>
    );

    return (
        <Authenticated auth={auth}>
            <Head title="Budgets" />

            <Create open={showCreate} onClose={closeCreate} onCreated={onCreated} initial={prefill} />

            <div className="mx-auto grid w-full max-w-[1100px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">
                <PageHeader title="Budgets" actions={actions} />
                <Budgets view={view} history={history.state} onChange={refresh} refreshKey={refreshKey} />
            </div>
        </Authenticated>
    );
}
