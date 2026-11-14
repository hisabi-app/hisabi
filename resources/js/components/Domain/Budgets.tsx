import { useState, useEffect } from 'react';
import { format, parseISO, subDays } from 'date-fns';
import { DotsThreeIcon, TrashIcon } from '@phosphor-icons/react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { formatNumber, getAppCurrency } from '@/Utils';
import { BudgetStatus, getBudgetStatus, toNumber } from '@/Utils/budgets';
import { deleteBudget, getBudgets } from '@/Api/budgets';
import { useInView } from '@/hooks/useInView';
import Tile from '@/components/Wallet/Tile';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { Skeleton } from '@/components/ui/skeleton';
import EmptyState from '@/components/Wallet/EmptyState';
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
    color: string | null;
    icon: string | null;
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
    color: string | null;
    icon: string | null;
}

// On track bars wear the category's color; anything that needs attention says so in color and words
const STATUS_STYLES: Record<BudgetStatus, { label: string; bar: string | null; text: string }> = {
    on_track: { label: 'On track', bar: null, text: 'text-muted-foreground' },
    ahead: { label: 'Ahead of pace', bar: 'var(--warning-bar)', text: 'text-warning font-semibold' },
    used: { label: 'Fully used', bar: 'var(--cat-gray)', text: 'text-muted-foreground' },
    over: { label: 'Over budget', bar: 'var(--destructive)', text: 'text-destructive font-semibold' },
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
        endLabel: format(subDays(parseISO(budget.end_at_date), 1), 'd MMM'),
        color: budget.color,
        icon: budget.icon,
    };
};

interface BudgetsProps {
    view: 'now' | 'history';
    history: HistoryState;
    // Called after a budget is updated or deleted
    onChange: () => void;
    // Changes when a budget is created or updated, so the list is fetched again
    refreshKey: number;
}

export default function Budgets({ view, history, onChange, refreshKey }: BudgetsProps) {
    const [budgets, setBudgets] = useState<BudgetView[]>([]);
    const [loading, setLoading] = useState(true);
    const [deleting, setDeleting] = useState<BudgetView | null>(null);
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
                <Skeleton className="h-[158px] w-full rounded-2xl" />
            </div>
        );
    }

    if (budgets.length === 0)
        return (
            <EmptyState
                className="rounded-2xl bg-card shadow-card"
                title="No budgets yet"
                body="A budget tells you when a category is spending faster than the month is passing."
            />
        );

    const needsAttention = budgets.filter((budget) => budget.status !== 'on_track');
    const onTrack = budgets.filter((budget) => budget.status === 'on_track');

    if (view === 'history') return <BudgetsHistoryView state={history} onAmountSaved={onChange} />;

    const onDeleted = (id: number) => {
        setDeleting(null);
        setBudgets((current) => current.filter((budget) => budget.id !== id));
        onChange();
    };

    return (
        <div className="grid gap-7">
            <DeleteBudgetDialog budget={deleting} onClose={() => setDeleting(null)} onDeleted={onDeleted} />

            {[
                { title: 'Needs attention', items: needsAttention },
                { title: 'On track', items: onTrack },
            ]
                .filter((section) => section.items.length > 0)
                .map((section) => (
                    <section key={section.title} className="grid gap-3">
                        <h2 className="px-1 text-[15px] font-bold">
                            {section.title} <span className="font-normal text-muted-foreground">· {section.items.length}</span>
                        </h2>
                        <Card className="rise gap-0 overflow-hidden py-0">
                            {section.items.map((budget, index) => (
                                <BudgetRow key={budget.id} budget={budget} onDelete={() => setDeleting(budget)} className={index > 0 ? 'border-t' : ''} />
                            ))}
                        </Card>
                    </section>
                ))}
        </div>
    );
}

function BudgetRow({ budget, onDelete, className = '' }: { budget: BudgetView; onDelete: () => void; className?: string }) {
    const style = STATUS_STYLES[budget.status];

    return (
        <div
            className={`grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-x-6 gap-y-3 py-4 pr-3 pl-5 md:grid-cols-[220px_minmax(0,1fr)_150px_auto] md:gap-x-8 ${className}`}
        >
            <span className="flex items-center gap-3">
                <Tile color={budget.color} icon={budget.icon ?? 'Wallet'} />
                <span className="grid gap-0.5">
                    <span className="font-semibold">{budget.name}</span>
                    <span className={`text-xs ${style.text}`}>{budget.status === 'on_track' ? `Until ${budget.endLabel}` : style.label}</span>
                </span>
            </span>

            <span className="relative order-last col-span-3 h-2 rounded-full bg-track md:order-none md:col-span-1" aria-hidden="true">
                <span
                    className="grow-x absolute inset-y-0 left-0 rounded-full"
                    style={{ width: `${Math.min(budget.spentPercentage, 100)}%`, background: style.bar ?? categoryColor(budget.color) }}
                />
                <span
                    className="absolute -top-1 h-4 w-0.5 -translate-x-1/2 rounded-full bg-foreground"
                    style={{ left: `${budget.elapsedPercentage}%` }}
                    title="Today"
                />
            </span>

            <span className="grid gap-0.5 text-right">
                <span className={`font-semibold font-rounded tabular-nums ${budget.remaining < 0 ? 'text-destructive' : ''}`}>
                    {budget.remaining > 0 && `${money(budget.remaining)} left`}
                    {budget.remaining === 0 && 'Nothing left'}
                    {budget.remaining < 0 && `${money(-budget.remaining)} over`}
                </span>
                <span className="text-xs text-muted-foreground tabular-nums">
                    {budget.spentPercentage}% of {money(budget.amount)}
                </span>
            </span>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="-ml-3 size-8 text-muted-foreground md:-ml-5" aria-label={`More actions for ${budget.name}`}>
                        <DotsThreeIcon size={18} weight="bold" aria-hidden="true" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem variant="destructive" onSelect={onDelete}>
                        <TrashIcon aria-hidden="true" />
                        Delete
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}

function DeleteBudgetDialog({ budget, onClose, onDeleted }: { budget: BudgetView | null; onClose: () => void; onDeleted: (id: number) => void }) {
    const [deleting, setDeleting] = useState(false);
    const [error, setError] = useState(false);
    // Keeps the name on screen while the dialog fades out
    const [shown, setShown] = useState<BudgetView | null>(null);

    useEffect(() => {
        if (!budget) return;

        setShown(budget);
        setError(false);
    }, [budget]);

    const confirm = () => {
        if (!budget) return;

        setDeleting(true);
        setError(false);
        deleteBudget(budget.id)
            .then(() => onDeleted(budget.id))
            .catch(() => setError(true))
            .finally(() => setDeleting(false));
    };

    return (
        <Dialog open={budget !== null} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete {shown?.name}?</DialogTitle>
                    <DialogDescription>Its history goes with it. Your transactions and categories stay as they are.</DialogDescription>
                </DialogHeader>

                {error && <p className="text-sm text-red-700 dark:text-red-400">Couldn't delete the budget. Try again.</p>}

                <DialogFooter>
                    <Button variant="ghost" onClick={onClose}>
                        Cancel
                    </Button>
                    <Button variant="destructive" disabled={deleting} onClick={confirm}>
                        {deleting ? 'Deleting…' : 'Delete budget'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
