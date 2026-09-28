import { useState, useEffect } from 'react';
import { format, parseISO, subDays } from 'date-fns';
import { Card } from '@/components/ui/card';
import { formatNumber, getAppCurrency } from '@/Utils';
import { BudgetStatus, getBudgetStatus, toNumber } from '@/Utils/budgets';
import { getBudgets } from '@/Api/budgets';
import { useInView } from '@/hooks/useInView';
import LoadingView from '../Global/LoadingView';
import NoContent from '../Global/NoContent';
import { BudgetsHistoryView, HistoryState } from './BudgetHistory';

interface Budget {
    id: number;
    name: string;
    amount: number;
    reoccurrence: string;
    total_spent_percentage: number;
    start_at_date: string;
    end_at_date: string;
    remaining_to_spend: number | string;
    total_margin_per_day: number | string;
    remaining_days: number;
    elapsed_days_percentage: number;
    is_saving: boolean;
    total_transactions_amount: number | string;
}

interface BudgetView {
    id: number;
    name: string;
    amount: number;
    spent: number;
    remaining: number;
    spentPercentage: number;
    elapsedPercentage: number;
    status: BudgetStatus;
    endLabel: string;
}

const STATUS_STYLES: Record<BudgetStatus, { label: string; bar: string }> = {
    on_track: { label: 'On track', bar: 'bg-green-500' },
    ahead: { label: 'Ahead of pace', bar: 'bg-amber-500' },
    used: { label: 'Fully used', bar: 'bg-neutral-500' },
    over: { label: 'Over budget', bar: 'bg-red-600' },
};

const money = (value: number) => `${getAppCurrency()} ${formatNumber(Math.round(value), '0,0')}`.trim();

const toView = (budget: Budget): BudgetView => {
    const amount = toNumber(budget.amount);
    const spent = toNumber(budget.total_transactions_amount);
    const remaining = amount - spent;

    return {
        id: budget.id,
        name: budget.name,
        amount,
        spent,
        remaining,
        spentPercentage: amount > 0 ? Math.round((spent / amount) * 100) : 0,
        elapsedPercentage: toNumber(budget.elapsed_days_percentage),
        status: getBudgetStatus(spent, amount, toNumber(budget.elapsed_days_percentage)),
        // end_at_date is the first day of the next window
        endLabel: format(subDays(parseISO(budget.end_at_date), 1), 'MMM d'),
    };
};

interface BudgetsProps {
    view: 'now' | 'history';
    history: HistoryState;
    onAmountSaved: () => void;
    // Changes when a budget is updated, so the list is fetched again
    refreshKey: number;
}

export default function Budgets({ view, history, onAmountSaved, refreshKey }: BudgetsProps) {
    const [budgets, setBudgets] = useState<BudgetView[]>([]);
    const [loading, setLoading] = useState(true);
    const [ref, isInView] = useInView();

    useEffect(() => {
        if (!isInView) return;

        getBudgets()
            .then((response) => {
                setBudgets(response.data.budgets.map(toView));
            })
            .finally(() => {
                setLoading(false);
            });
    }, [isInView, refreshKey]);

    if (loading) {
        return (
            <div ref={ref}>
                <Card className="h-[158px]">
                    <LoadingView />
                </Card>
            </div>
        );
    }

    if (budgets.length === 0) return <NoContent body="No budgets found" />;

    const needsAttention = budgets.filter((budget) => budget.status !== 'on_track');
    const onTrack = budgets.filter((budget) => budget.status === 'on_track');

    if (view === 'history') return <BudgetsHistoryView state={history} onAmountSaved={onAmountSaved} />;

    return (
        <div className="grid gap-7">
            {[
                { title: 'Needs attention', items: needsAttention },
                { title: 'On track', items: onTrack },
            ]
                .filter((section) => section.items.length > 0)
                .map((section) => (
                    <section key={section.title} className="grid gap-3">
                        <h2 className="text-[15px] font-semibold">
                            {section.title} <span className="font-normal text-muted-foreground">· {section.items.length}</span>
                        </h2>
                        <Card className="gap-0 overflow-hidden py-0">
                            {section.items.map((budget, index) => (
                                <BudgetRow key={budget.id} budget={budget} className={index > 0 ? 'border-t' : ''} />
                            ))}
                        </Card>
                    </section>
                ))}
        </div>
    );
}

function BudgetRow({ budget, className = '' }: { budget: BudgetView; className?: string }) {
    const style = STATUS_STYLES[budget.status];

    return (
        <div className={`grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-6 gap-y-3 px-5 py-4 md:grid-cols-[220px_minmax(0,1fr)_150px] md:gap-x-8 ${className}`}>
            <span className="flex items-center gap-3">
                <span className={`size-2.5 shrink-0 rounded-full ${style.bar}`} />
                <span className="grid gap-0.5">
                    <span className="font-medium">{budget.name}</span>
                    <span className="text-xs text-muted-foreground">{budget.status === 'on_track' ? `Until ${budget.endLabel}` : style.label}</span>
                </span>
            </span>

            <span className="relative order-last col-span-2 h-2 rounded-full bg-muted md:order-none md:col-span-1" aria-hidden="true">
                <span className={`absolute inset-y-0 left-0 rounded-full ${style.bar}`} style={{ width: `${Math.min(budget.spentPercentage, 100)}%` }} />
                <span
                    className="absolute -top-1 h-4 w-0.5 -translate-x-1/2 rounded-full bg-foreground"
                    style={{ left: `${budget.elapsedPercentage}%` }}
                    title="Today"
                />
            </span>

            <span className="grid gap-0.5 text-right">
                <span className={`font-semibold tabular-nums ${budget.remaining < 0 ? 'text-red-700 dark:text-red-400' : ''}`}>
                    {budget.remaining > 0 && `${money(budget.remaining)} left`}
                    {budget.remaining === 0 && 'Nothing left'}
                    {budget.remaining < 0 && `${money(-budget.remaining)} over`}
                </span>
                <span className="text-xs text-muted-foreground tabular-nums">
                    {budget.spentPercentage}% of {money(budget.amount)}
                </span>
            </span>
        </div>
    );
}
