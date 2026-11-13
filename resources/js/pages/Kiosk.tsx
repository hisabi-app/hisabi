import { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowDown, ArrowUp } from 'lucide-react';

import { getKioskSummary } from '@/Api/kiosk';
import { formatNumber, getAppCurrency } from '@/Utils';
import { BudgetStatus, toNumber } from '@/Utils/budgets';
import { compareWithLastMonth, KioskBudget, msUntilDailyReload, toKioskBudgets } from '@/Utils/kiosk';

const REFRESH_INTERVAL = 60 * 60 * 1000;
const RETRY_INTERVAL = 5 * 60 * 1000;

const STATUS_STYLES: Record<BudgetStatus, { label: string; color: string; text: string }> = {
    over: { label: 'Over budget', color: 'bg-red-500', text: 'text-red-400' },
    ahead: { label: 'Ahead of pace', color: 'bg-amber-500', text: 'text-amber-400' },
    on_track: { label: 'On track', color: 'bg-green-500', text: 'text-muted-foreground' },
    used: { label: 'Fully used', color: 'bg-neutral-500', text: 'text-muted-foreground' },
};

interface Summary {
    spent: number;
    spentLastMonthToDate: number;
    budgets: KioskBudget[];
}

const amount = (value: number) => formatNumber(Math.round(value), '0,0');

export default function Kiosk() {
    const [summary, setSummary] = useState<Summary | null>(null);
    const [updatedAt, setUpdatedAt] = useState<Date | null>(null);
    const [failed, setFailed] = useState(false);
    const lastAttemptAt = useRef(0);

    useEffect(() => {
        let timer: number | undefined;
        let cancelled = false;

        const refresh = () => {
            window.clearTimeout(timer);
            lastAttemptAt.current = Date.now();

            getKioskSummary()
                .then((data) => {
                    if (cancelled) return;

                    setSummary({
                        spent: toNumber(data.spent),
                        spentLastMonthToDate: toNumber(data.spent_last_month_to_date),
                        budgets: toKioskBudgets(data.budgets),
                    });
                    setUpdatedAt(new Date());
                    setFailed(false);
                    timer = window.setTimeout(refresh, REFRESH_INTERVAL);
                })
                .catch((error) => {
                    if (cancelled) return;

                    // The session expired: reloading lands on the login page
                    if (error.status === 401) {
                        window.location.reload();
                        return;
                    }

                    setFailed(true);
                    timer = window.setTimeout(refresh, RETRY_INTERVAL);
                });
        };

        // Timers can pause while the screen is off, so catch up as soon as it's back on
        const onVisibilityChange = () => {
            if (document.visibilityState === 'visible' && Date.now() - lastAttemptAt.current >= REFRESH_INTERVAL) refresh();
        };

        refresh();
        document.addEventListener('visibilitychange', onVisibilityChange);
        const reloadTimer = window.setTimeout(() => window.location.reload(), msUntilDailyReload(new Date()));

        return () => {
            cancelled = true;
            window.clearTimeout(timer);
            window.clearTimeout(reloadTimer);
            document.removeEventListener('visibilitychange', onVisibilityChange);
        };
    }, []);

    return (
        <div className="dark">
            <Head title="Kiosk" />
            <main className="flex h-svh flex-col gap-10 overflow-hidden bg-background px-14 py-12 leading-tight text-foreground portrait:px-10">
                {summary && updatedAt ? (
                    <>
                        <Spending summary={summary} updatedAt={updatedAt} failed={failed} />
                        {summary.budgets.length > 0 && (
                            <section
                                aria-label="Budgets"
                                className="grid min-h-0 flex-1 auto-rows-[minmax(14rem,1fr)] grid-cols-2 gap-6 overflow-y-auto landscape:grid-cols-3"
                            >
                                {summary.budgets.map((budget) => (
                                    <BudgetTile key={budget.id} budget={budget} />
                                ))}
                            </section>
                        )}
                    </>
                ) : (
                    <p className="m-auto text-2xl text-muted-foreground">{failed ? 'Couldn’t reach Hisabi, trying again in a few minutes' : 'Loading…'}</p>
                )}
            </main>
        </div>
    );
}

function Spending({ summary, updatedAt, failed }: { summary: Summary; updatedAt: Date; failed: boolean }) {
    const currency = getAppCurrency();
    const comparison = compareWithLastMonth(summary.spent, summary.spentLastMonthToDate);
    const time = format(updatedAt, 'h:mm a');

    return (
        <header className="grid gap-2.5">
            <div className="flex items-center justify-between gap-6">
                <p className="text-2xl text-muted-foreground">Spent in {format(updatedAt, 'MMMM')}</p>
                <p className="flex items-center gap-2.5 text-xl text-muted-foreground">
                    <span className={`size-2.5 rounded-full ${failed ? 'bg-amber-500' : 'bg-green-500'}`} />
                    {failed ? `Couldn’t refresh · data from ${time}` : `Updated ${time}`}
                </p>
            </div>

            <p className="flex items-baseline gap-3.5">
                <span className="text-[32px] font-medium text-muted-foreground">{currency}</span>
                <span className="text-[88px] leading-none font-semibold tracking-tight tabular-nums">{amount(summary.spent)}</span>
            </p>

            {comparison && (
                <p
                    className={`flex items-center gap-2 text-[22px] ${
                        { less: 'text-green-400', more: 'text-amber-400', same: 'text-muted-foreground' }[comparison.direction]
                    }`}
                >
                    {comparison.direction === 'less' && <ArrowDown className="size-[22px]" strokeWidth={2.25} aria-hidden="true" />}
                    {comparison.direction === 'more' && <ArrowUp className="size-[22px]" strokeWidth={2.25} aria-hidden="true" />}
                    {comparison.direction === 'same'
                        ? 'Same as this time last month'
                        : `${currency} ${amount(comparison.difference)} ${comparison.direction} than this time last month`}
                </p>
            )}
        </header>
    );
}

function BudgetTile({ budget }: { budget: KioskBudget }) {
    const style = STATUS_STYLES[budget.status];
    const currency = getAppCurrency();
    const remaining = Math.round(budget.remaining);

    return (
        <article className="flex flex-col justify-between rounded-[18px] border border-neutral-800 bg-neutral-900 px-7 py-[26px]">
            <div className="flex items-start gap-3">
                <span className={`mt-[9px] size-3 shrink-0 rounded-full ${style.color}`} />
                <div className="grid gap-1">
                    <h2 className="text-[26px] font-medium">{budget.name}</h2>
                    <p className={`text-[19px] ${style.text}`}>{style.label}</p>
                </div>
            </div>

            <div className="grid gap-3.5">
                <p className={`flex items-baseline gap-2.5 ${remaining < 0 ? 'text-red-400' : ''}`}>
                    {remaining === 0 ? (
                        <span className="text-[44px] leading-none font-semibold tracking-tight">Nothing left</span>
                    ) : (
                        <>
                            <span className="text-[22px] font-medium text-muted-foreground">{currency}</span>
                            <span className="text-[44px] leading-none font-semibold tracking-tight tabular-nums">{amount(Math.abs(remaining))}</span>
                            <span className={`text-[22px] ${remaining < 0 ? '' : 'text-muted-foreground'}`}>{remaining < 0 ? 'over' : 'left'}</span>
                        </>
                    )}
                </p>

                <div className="relative h-2.5 rounded-full bg-neutral-800" aria-hidden="true">
                    <span className={`absolute inset-y-0 left-0 rounded-full ${style.color}`} style={{ width: `${Math.min(budget.spentPercentage, 100)}%` }} />
                    <span
                        className="absolute -top-1.5 h-[22px] w-[3px] -translate-x-1/2 rounded-sm bg-foreground"
                        style={{ left: `${budget.elapsedPercentage}%` }}
                    />
                </div>

                <p className="text-[19px] text-muted-foreground tabular-nums">
                    {budget.spentPercentage}% of {currency} {amount(budget.amount)}
                </p>
            </div>
        </article>
    );
}
