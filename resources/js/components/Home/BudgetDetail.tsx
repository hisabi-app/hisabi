import { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { format, parseISO, subDays } from 'date-fns';

import { SheetTitle } from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import CountUp from '@/components/Wallet/CountUp';
import Tile from '@/components/Wallet/Tile';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount, toAmount } from '@/components/Wallet/format';
import { getBudgetDetail } from '@/Api/home';
import { getAppCurrency } from '@/Utils';
import { budgetStatusLine } from './BudgetRings';
import TransactionRow from './TransactionRow';
import type { Budget, CategorySummary, Transaction } from './types';

interface BudgetDetailData {
    budget: Budget;
    category: CategorySummary | null;
    brands: { id: number; name: string; total: number; count: number }[];
    transactions: Transaction[];
}

// "2026-11-01" to "2026-12-01" → "1 Nov to 30 Nov" (end_at_date is the first day after the window)
const windowLabel = (budget: Budget) =>
    `${format(parseISO(budget.start_at_date), 'd MMM')} to ${format(subDays(parseISO(budget.end_at_date), 1), 'd MMM')}`;

export default function BudgetDetail({ id }: { id: number }) {
    const [data, setData] = useState<BudgetDetailData | null>(null);
    const [error, setError] = useState(false);

    useEffect(() => {
        setData(null);
        setError(false);
        getBudgetDetail(id)
            .then(setData)
            .catch(() => setError(true));
    }, [id]);

    if (error) {
        return (
            <div className="p-5">
                <SheetTitle className="text-xl font-bold">Budget</SheetTitle>
                <p className="mt-3 text-muted-foreground">Couldn’t load this budget. Close it and try again.</p>
            </div>
        );
    }

    if (!data) {
        return (
            <div className="grid gap-5 p-5" aria-busy="true">
                <SheetTitle className="sr-only">Loading budget</SheetTitle>
                <Skeleton className="h-9 w-40" />
                <Skeleton className="h-16 w-full" />
                <Skeleton className="h-28 w-full" />
                <Skeleton className="h-40 w-full" />
            </div>
        );
    }

    const { budget, category, brands, transactions } = data;
    const spent = toAmount(budget.total_transactions_amount);
    const total = toAmount(budget.amount);
    const color = categoryColor(category?.color ?? budget.color);
    const top = brands[0]?.total ?? 1;

    return (
        <div className="grid gap-6 p-5 pt-[18px]">
            <SheetTitle className="flex items-center gap-2.5 pr-10 text-xl font-bold tracking-[-0.02em]">
                <Tile color={category?.color ?? budget.color} icon={category?.icon ?? budget.icon} />
                {budget.name}
            </SheetTitle>

            <div className="rise">
                <p className="font-medium text-muted-foreground">{windowLabel(budget)}</p>
                <p className="mt-0.5 text-[34px] leading-[1.1] font-bold tracking-[-0.03em] font-rounded">
                    <CountUp value={spent} format={(value) => amount(value)} />
                    <small className="ml-1.5 text-[0.45em] font-semibold tracking-normal text-muted-foreground">
                        of {getAppCurrency()} {amount(total)}
                    </small>
                </p>
                <p className="mt-0.5 text-sm">{budgetStatusLine(budget.status, spent, total)}</p>
                <div className="relative mt-2.5 h-2 overflow-hidden rounded-full bg-track">
                    <i className="grow-x absolute inset-y-0 left-0 rounded-full" style={{ width: `${Math.min(100, total > 0 ? (spent / total) * 100 : 0)}%`, background: color }} />
                </div>
            </div>

            {brands.length > 0 && (
                <section className="rise" style={{ '--i': 1 } as React.CSSProperties}>
                    <h3 className="mb-1 text-[13px] font-bold">By brand</h3>
                    <div className="divide-y">
                        {brands.map((brand, j) => (
                            <div key={brand.id} className="grid grid-cols-[minmax(0,1fr)_auto] gap-x-2.5 gap-y-1 py-2">
                                <p className="truncate font-semibold">
                                    {brand.name}
                                    <span className="ml-1 font-medium text-muted-foreground">
                                        {brand.count} {brand.count === 1 ? 'purchase' : 'purchases'}
                                    </span>
                                </p>
                                <p className="font-semibold font-rounded tabular">{amount(brand.total)}</p>
                                <div className="relative col-span-2 h-[5px] overflow-hidden rounded-full bg-track">
                                    <i className="grow-x absolute inset-y-0 left-0 rounded-full" style={{ width: `${(brand.total / top) * 100}%`, background: color, '--j': j + 1 } as React.CSSProperties} />
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            <section className="rise" style={{ '--i': 2 } as React.CSSProperties}>
                <h3 className="mb-1 text-[13px] font-bold">Transactions</h3>
                {transactions.length === 0 ? (
                    <p className="py-2 text-muted-foreground">Nothing spent in this budget yet.</p>
                ) : (
                    <div className="divide-y">
                        {transactions.map((transaction) => (
                            <TransactionRow key={transaction.id} transaction={transaction} />
                        ))}
                    </div>
                )}
                {category && (
                    <Link href={`/transactions?category=${category.id}`} className="mt-2 inline-block text-[13px] font-semibold text-info hover:underline">
                        See all in Transactions
                    </Link>
                )}
            </section>
        </div>
    );
}
