import { useState, useEffect } from 'react';
import { format, parseISO, subDays } from 'date-fns';
import { CaretRightIcon } from '@phosphor-icons/react';
import { Card } from '@/components/ui/card';
import { formatNumber, getAppCurrency } from '@/Utils';
import { BudgetStatus, DailySpending, buildBurnDown, getBudgetStatus, toNumber } from '@/Utils/budgets';
import { getBudgets, getBudgetDailySpending } from '@/Api/budgets';
import { useInView } from '@/hooks/useInView';
import LoadingView from '../Global/LoadingView';
import NoContent from '../Global/NoContent';

interface Budget {
    id: number;
    name: string;
    amount: number;
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
    daysLeft: number;
    perDay: number;
    isSaving: boolean;
    status: BudgetStatus;
    endLabel: string;
}

interface DailySpendingResponse {
    start_date: string;
    end_date: string;
    total_days: number;
    days: DailySpending[];
}

type DailyState = DailySpendingResponse | 'loading' | 'error';

const STATUS_STYLES: Record<BudgetStatus, { label: string; bar: string; stroke: string; pill: string }> = {
    on_track: {
        label: 'On track',
        bar: 'bg-green-500',
        stroke: '#22c55e',
        pill: 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    },
    ahead: {
        label: 'Ahead of pace',
        bar: 'bg-amber-500',
        stroke: '#f59e0b',
        pill: 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-400',
    },
    used: {
        label: 'Fully used',
        bar: 'bg-neutral-500',
        stroke: '#737373',
        pill: 'bg-muted text-muted-foreground',
    },
    over: {
        label: 'Over budget',
        bar: 'bg-red-600',
        stroke: '#dc2626',
        pill: 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    },
};

const money = (value: number) => `${getAppCurrency()} ${formatNumber(Math.round(value), '0,0')}`.trim();

const toView = (budget: Budget): BudgetView => {
    const amount = toNumber(budget.amount);
    const spent = toNumber(budget.total_transactions_amount);
    const remaining = amount - spent;
    const daysLeft = Math.max(0, Math.ceil(toNumber(budget.remaining_days)));

    return {
        id: budget.id,
        name: budget.name,
        amount,
        spent,
        remaining,
        spentPercentage: amount > 0 ? Math.round((spent / amount) * 100) : 0,
        elapsedPercentage: toNumber(budget.elapsed_days_percentage),
        daysLeft,
        perDay: remaining > 0 ? remaining / Math.max(1, daysLeft) : 0,
        isSaving: budget.is_saving,
        status: getBudgetStatus(spent, amount, toNumber(budget.elapsed_days_percentage)),
        // end_at_date is the first day of the next window
        endLabel: format(subDays(parseISO(budget.end_at_date), 1), 'MMM d'),
    };
};

export default function Budgets() {
    const [budgets, setBudgets] = useState<BudgetView[]>([]);
    const [loading, setLoading] = useState(true);
    const [openId, setOpenId] = useState<number | null>(null);
    const [daily, setDaily] = useState<Record<number, DailyState>>({});
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
    }, [isInView]);

    const toggle = (id: number) => {
        const opening = openId !== id;
        setOpenId(opening ? id : null);

        if (!opening || (daily[id] && daily[id] !== 'error')) return;

        setDaily((current) => ({ ...current, [id]: 'loading' }));
        getBudgetDailySpending(id)
            .then((data) => setDaily((current) => ({ ...current, [id]: data })))
            .catch(() => setDaily((current) => ({ ...current, [id]: 'error' })));
    };

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

    return (
        <div className="grid gap-7">
            <BudgetsSummary budgets={budgets} />

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
                                <div key={budget.id} className={index > 0 ? 'border-t' : ''}>
                                    <BudgetRow budget={budget} open={openId === budget.id} onToggle={() => toggle(budget.id)} />
                                    {openId === budget.id && <BurnDownPanel budget={budget} state={daily[budget.id]} />}
                                </div>
                            ))}
                        </Card>
                    </section>
                ))}
        </div>
    );
}

function BudgetsSummary({ budgets }: { budgets: BudgetView[] }) {
    const spending = budgets.filter((budget) => !budget.isSaving);
    const budgeted = spending.reduce((sum, budget) => sum + budget.amount, 0);
    const spent = spending.reduce((sum, budget) => sum + budget.spent, 0);
    const left = budgeted - spent;
    const safePerDay = spending.reduce((sum, budget) => sum + budget.perDay, 0);
    const spentPercentage = budgeted > 0 ? Math.round((spent / budgeted) * 100) : 0;

    const counts = (['over', 'used', 'ahead', 'on_track'] as BudgetStatus[])
        .map((status) => ({ status, count: budgets.filter((budget) => budget.status === status).length }))
        .filter(({ count }) => count > 0);

    return (
        <Card className="grid gap-8 p-6 md:grid-cols-[320px_minmax(0,1fr)] md:items-center md:gap-12 md:p-8">
            <div className="grid gap-2 md:border-r md:pr-12">
                <p className="text-xs font-medium uppercase tracking-wider text-muted-foreground">Safe to spend</p>
                <p className="flex items-baseline gap-2">
                    <span className="text-5xl font-semibold tracking-tight tabular-nums">{money(safePerDay)}</span>
                    <span className="text-lg text-muted-foreground">/ day</span>
                </p>
                <p className="text-sm text-muted-foreground">
                    Across {spending.length} {spending.length === 1 ? 'budget' : 'budgets'}, for the rest of each period.
                </p>
            </div>

            <div className="grid gap-5">
                <div className="grid grid-cols-3 gap-6">
                    <Stat label="Budgeted" value={money(budgeted)} />
                    <Stat label="Spent" value={money(spent)} />
                    <Stat
                        label={left >= 0 ? 'Left' : 'Over'}
                        value={money(Math.abs(left))}
                        className={left >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'}
                    />
                </div>

                <div className="h-3 rounded-full bg-muted">
                    <div className="h-full rounded-full bg-foreground" style={{ width: `${Math.min(spentPercentage, 100)}%` }} />
                </div>

                <div className="flex flex-wrap items-center gap-2.5">
                    {counts.map(({ status, count }) => (
                        <span key={status} className={`rounded-full px-2.5 py-1 text-xs font-medium ${STATUS_STYLES[status].pill}`}>
                            {count} {STATUS_STYLES[status].label.toLowerCase()}
                        </span>
                    ))}
                    <span className="ml-auto text-xs text-muted-foreground">{spentPercentage}% spent</span>
                </div>
            </div>
        </Card>
    );
}

function Stat({ label, value, className = '' }: { label: string; value: string; className?: string }) {
    return (
        <div className="grid gap-1">
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className={`text-lg font-semibold tabular-nums md:text-xl ${className}`}>{value}</span>
        </div>
    );
}

function BudgetRow({ budget, open, onToggle }: { budget: BudgetView; open: boolean; onToggle: () => void }) {
    const style = STATUS_STYLES[budget.status];

    return (
        <button
            type="button"
            onClick={onToggle}
            aria-expanded={open}
            className={`grid w-full cursor-pointer grid-cols-[minmax(0,1fr)_auto_20px] items-center gap-x-6 gap-y-3 px-5 py-4 text-left transition-colors hover:bg-muted/50 focus-visible:outline-2 focus-visible:-outline-offset-2 md:grid-cols-[220px_minmax(0,1fr)_150px_190px_20px] md:gap-x-8 ${open ? 'bg-muted/50' : ''}`}
        >
            <span className="flex items-center gap-3">
                <span className={`size-2.5 shrink-0 rounded-full ${style.bar}`} />
                <span className="grid gap-0.5">
                    <span className="font-medium">{budget.name}</span>
                    <span className="text-xs text-muted-foreground">{budget.status === 'on_track' ? `Until ${budget.endLabel}` : style.label}</span>
                </span>
            </span>

            <span className="relative order-last col-span-3 h-2 rounded-full bg-muted md:order-none md:col-span-1" aria-hidden="true">
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

            <span className="hidden text-right text-sm text-muted-foreground tabular-nums md:block">
                {budget.remaining > 0 ? `${money(budget.perDay)}/day · ` : ''}
                {budget.daysLeft} {budget.daysLeft === 1 ? 'day' : 'days'} left
            </span>

            <CaretRightIcon size={18} className={`text-muted-foreground transition-transform ${open ? 'rotate-90' : ''}`} aria-hidden="true" />
        </button>
    );
}

function BurnDownPanel({ budget, state }: { budget: BudgetView; state: DailyState | undefined }) {
    if (!state || state === 'loading') {
        return (
            <div className="h-[260px] border-t bg-muted/30">
                <LoadingView />
            </div>
        );
    }

    if (state === 'error') {
        return <p className="border-t bg-muted/30 px-5 py-8 text-center text-sm text-muted-foreground">Couldn't load daily spending. Try again.</p>;
    }

    const { points, spent, elapsedDays, projected, averagePerDay, evenPacePerDay } = buildBurnDown(state.days, state.total_days, budget.amount);
    const style = STATUS_STYLES[budget.status];
    const totalDays = Math.max(1, state.total_days);
    const max = Math.max(budget.amount, spent, 1) * 1.12;
    const x = (day: number) => (day / totalDays) * 100;
    const y = (total: number) => 100 - (total / max) * 100;
    const line = points.map((point) => `${x(point.day)},${y(point.total)}`).join(' ');
    const todayX = x(elapsedDays);
    const limitY = y(budget.amount);
    const projectedDiff = projected - budget.amount;

    return (
        <div className="grid gap-8 border-t bg-muted/30 px-5 pt-5 pb-6 md:grid-cols-[minmax(0,1fr)_240px] md:gap-10 md:pl-11">
            <div className="grid gap-2.5">
                <div className="flex flex-wrap gap-x-5 gap-y-1 text-xs text-muted-foreground">
                    <span className="flex items-center gap-2">
                        <span className="h-[3px] w-4 rounded-full" style={{ background: style.stroke }} />
                        Actual spending
                    </span>
                    <span className="flex items-center gap-2">
                        <span className="w-4 border-t-2 border-dashed border-neutral-400" />
                        Even pace
                    </span>
                    <span className="flex items-center gap-2">
                        <span className="w-4 border-t border-neutral-300 dark:border-neutral-600" />
                        Budget limit
                    </span>
                </div>

                <div className="relative h-40">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" className="h-full w-full overflow-visible" aria-hidden="true">
                        <line x1="0" y1={limitY} x2="100" y2={limitY} className="stroke-neutral-300 dark:stroke-neutral-600" strokeWidth="1" vectorEffect="non-scaling-stroke" />
                        <line x1="0" y1="100" x2="100" y2={limitY} className="stroke-neutral-400" strokeWidth="2" strokeDasharray="5 5" vectorEffect="non-scaling-stroke" />
                        <polygon points={`${line} ${todayX},100`} fill={style.stroke} fillOpacity="0.12" />
                        <polyline points={line} fill="none" stroke={style.stroke} strokeWidth="2.5" strokeLinejoin="round" strokeLinecap="round" vectorEffect="non-scaling-stroke" />
                        <line x1={todayX} y1="0" x2={todayX} y2="100" className="stroke-foreground" strokeWidth="1" strokeDasharray="2 3" vectorEffect="non-scaling-stroke" />
                    </svg>
                    <span
                        className="absolute size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full ring-2 ring-background"
                        style={{ left: `${todayX}%`, top: `${y(spent)}%`, background: style.stroke }}
                    />
                </div>

                <div className="flex justify-between text-[11px] text-muted-foreground">
                    <span>{format(parseISO(state.start_date), 'MMM d')}</span>
                    <span>{budget.endLabel}</span>
                </div>
            </div>

            <div className="grid content-start gap-5">
                <div className="grid gap-1">
                    <span className="text-xs text-muted-foreground">At this pace, by {budget.endLabel}</span>
                    <span className="text-xl font-semibold tabular-nums">{money(projected)}</span>
                    <span className={`text-xs ${projectedDiff > 0 ? 'text-red-700 dark:text-red-400' : 'text-green-700 dark:text-green-400'}`}>
                        {money(Math.abs(projectedDiff))} {projectedDiff > 0 ? 'over' : 'under'} budget
                    </span>
                </div>
                <div className="grid grid-cols-2 gap-4">
                    <div className="grid gap-1">
                        <span className="text-xs text-muted-foreground">Avg so far</span>
                        <span className="text-[15px] font-semibold tabular-nums">{money(averagePerDay)}/day</span>
                    </div>
                    <div className="grid gap-1">
                        <span className="text-xs text-muted-foreground">Even pace</span>
                        <span className="text-[15px] font-semibold tabular-nums">{money(evenPacePerDay)}/day</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
