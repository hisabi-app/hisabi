import { useState } from 'react';
import { Head } from '@inertiajs/react';

import Authenticated from '@/Layouts/Authenticated';
import Budgets from '@/components/Domain/Budgets';
import { HistoryRangePicker } from '@/components/Domain/BudgetHistory';
import { useBudgetsHistory } from '@/hooks/useBudgetsHistory';

type View = 'now' | 'history';

const VIEWS: { value: View; label: string }[] = [
    { value: 'now', label: 'Now' },
    { value: 'history', label: 'History' },
];

export default function Index({ auth, hasHistory }: { auth: any; hasHistory: boolean }) {
    const [view, setView] = useState<View>('now');
    const history = useBudgetsHistory();
    const [refreshKey, setRefreshKey] = useState(0);

    const onAmountSaved = () => {
        history.reload();
        setRefreshKey((key) => key + 1);
    };

    const selectView = (value: View) => {
        setView(value);
        if (value === 'history') history.load();
    };

    const header = (
        <div className="flex items-center justify-between w-full gap-2">
            <h2>Budgets</h2>
            <div className="flex items-center gap-2">
                {view === 'history' && history.state.data && <HistoryRangePicker meta={history.state.data.meta} onChange={history.load} />}
                {hasHistory && (
                    <div role="group" aria-label="Budgets view" className="inline-flex h-9 items-center rounded-lg bg-muted p-[3px]">
                        {VIEWS.map(({ value, label }) => (
                            <button
                                key={value}
                                type="button"
                                aria-pressed={view === value}
                                onClick={() => selectView(value)}
                                className={`h-full rounded-md px-3 text-sm font-medium transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none ${
                                    view === value ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Budgets" />

            <div className="p-4">
                <div className="max-w-7xl mx-auto grid gap-4">
                    <Budgets view={view} history={history.state} onAmountSaved={onAmountSaved} refreshKey={refreshKey} />
                </div>
            </div>
        </Authenticated>
    );
}
