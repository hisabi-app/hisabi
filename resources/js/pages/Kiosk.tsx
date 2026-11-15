import { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import { format, formatDistanceToNowStrict, parseISO, subMonths } from 'date-fns';

import MonthChart from '@/components/Home/MonthChart';
import type { Transaction } from '@/components/Home/types';
import CountUp from '@/components/Wallet/CountUp';
import Ring from '@/components/Wallet/Ring';
import Tile from '@/components/Wallet/Tile';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount } from '@/components/Wallet/format';
import { getKioskSummary } from '@/Api/kiosk';
import { getAppCurrency } from '@/Utils';
import { toNumber } from '@/Utils/budgets';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import { categoryLayout, compareWithLastMonth, isNight, KioskBudget, KioskCategory, msUntilDailyReload, toKioskBudgets } from '@/Utils/kiosk';
import { cn } from '@/lib/utils';

const REFRESH_INTERVAL = 60 * 60 * 1000;
const RETRY_INTERVAL = 5 * 60 * 1000;
const CLOCK_INTERVAL = 60 * 1000;
const PAGE_INTERVAL = 20 * 1000;

// The overview and the categories take turns on the screen
type PageName = 'overview' | 'categories';
const PAGES: PageName[] = ['overview', 'categories'];
const nextPage = (page: PageName): PageName => (page === 'overview' ? 'categories' : 'overview');

interface Summary {
    spent: number;
    spentLastMonthToDate: number;
    budgets: KioskBudget[];
    daily: number[];
    lastMonthDaily: number[];
    categories: KioskCategory[];
    latest: Transaction | null;
    latestAt: string | null;
}

export default function Kiosk() {
    const [summary, setSummary] = useState<Summary | null>(null);
    const [updatedAt, setUpdatedAt] = useState<Date | null>(null);
    const [failed, setFailed] = useState(false);
    const [now, setNow] = useState(() => new Date());
    const [page, setPage] = useState<PageName>('overview');
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
                        daily: data.daily ?? [],
                        lastMonthDaily: data.last_month_daily ?? [],
                        categories: data.categories ?? [],
                        latest: data.latest ?? null,
                        latestAt: data.latest_at ?? null,
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
        // Keeps the date, "2 hours ago" and day/night current between refreshes
        const clock = window.setInterval(() => setNow(new Date()), CLOCK_INTERVAL);

        return () => {
            cancelled = true;
            window.clearTimeout(timer);
            window.clearTimeout(reloadTimer);
            window.clearInterval(clock);
            document.removeEventListener('visibilitychange', onVisibilityChange);
        };
    }, []);

    const night = isNight(now);
    const hasBudgets = (summary?.budgets.length ?? 0) > 0;
    // Until something is spent this month there's no categories page, and the overview stays up
    const rotates = (summary?.categories.length ?? 0) > 0;
    const currentPage = rotates ? page : 'overview';

    // Each page stays up for 20 seconds; a tap turns the page and starts the wait again
    useEffect(() => {
        if (!rotates) return;

        const timer = window.setTimeout(() => setPage(nextPage), PAGE_INTERVAL);

        return () => window.clearTimeout(timer);
    }, [page, rotates]);

    return (
        <div className={cn(night && 'dark')}>
            <Head title="Kiosk" />
            <main
                className="flex h-svh flex-col gap-[1.1em] overflow-hidden bg-background px-[2.1em] pt-[1.9em] pb-[1.7em] leading-tight text-foreground transition-colors duration-1000"
                style={{ fontSize: 'min(1.45vw, 2.35vh)' }}
                onClick={rotates ? () => setPage(nextPage) : undefined}
            >
                {summary && updatedAt ? (
                    <>
                        <header className="flex items-end justify-between gap-[1em]">
                            <div>
                                <h1 className="text-[2.5em] leading-none font-bold tracking-[-0.03em]">{format(now, 'MMMM')}</h1>
                                <p className="mt-[0.35em] text-[1.05em] text-muted-foreground">{format(now, 'EEEE d MMMM')}</p>
                            </div>
                            <p className="flex items-center gap-[0.5em] text-[0.95em] text-muted-foreground">
                                <span className={cn('size-[0.5em] rounded-full', failed ? 'bg-warning-bar' : 'bg-success')} />
                                {failed ? `Couldn’t refresh · data from ${format(updatedAt, 'HH:mm')}` : `Updated ${format(updatedAt, 'HH:mm')}`}
                            </p>
                        </header>

                        <div className="grid min-h-0 flex-1 grid-rows-[minmax(0,1fr)]">
                            <KioskPage
                                shown={currentPage === 'overview'}
                                className={cn('grid grid-rows-[minmax(0,1fr)] gap-[1.1em]', hasBudgets && 'grid-cols-[1.15fr_1fr]')}
                            >
                                <Spending summary={summary} now={now} />
                                {hasBudgets && <Budgets budgets={summary.budgets} />}
                            </KioskPage>
                            {rotates && (
                                <KioskPage shown={currentPage === 'categories'} className="flex flex-col">
                                    <Categories categories={summary.categories} />
                                </KioskPage>
                            )}
                        </div>

                        <footer className="flex items-center justify-between gap-[1em]">
                            {summary.latest && summary.latestAt ? (
                                <Latest key={summary.latest.id} transaction={summary.latest} at={summary.latestAt} now={now} />
                            ) : (
                                <p className="text-[1.05em] text-muted-foreground">Waiting for this month’s first transaction</p>
                            )}
                            {rotates && <PageDots current={currentPage} />}
                        </footer>
                    </>
                ) : (
                    <p className="m-auto text-[1.6em] text-muted-foreground">{failed ? 'Couldn’t reach Hisabi, trying again in a few minutes' : 'Loading…'}</p>
                )}
            </main>
        </div>
    );
}

function Spending({ summary, now }: { summary: Summary; now: Date }) {
    const comparison = compareWithLastMonth(summary.spent, summary.spentLastMonthToDate);
    const [chartRef, chartBox] = useSize<HTMLDivElement>();
    // The chart takes the room left under the numbers, less a line's gap: its usual shape (340 by 86) when there's space,
    // flatter on wide, short screens, and none when it would be too flat to read
    const chartHeight = chartBox.width > 0 ? Math.min(86, (340 * (chartBox.height - chartBox.em)) / chartBox.width) : 0;

    return (
        <section className="flex min-h-0 flex-col rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            <p className="text-[1.1em] font-medium text-muted-foreground">Spent so far</p>
            <p className="mt-[0.1em] text-[5em] leading-[1.05] font-bold tracking-[-0.035em] font-rounded">
                <small className="mr-[0.3em] text-[0.3em] font-semibold tracking-normal text-muted-foreground">{getAppCurrency()}</small>
                <CountUp value={summary.spent} format={(value) => amount(value)} duration={1200} />
            </p>
            {comparison && (
                <p className="mt-[0.3em] text-[1.1em] text-muted-foreground">
                    {comparison.direction === 'same' ? (
                        'The same as this time last month'
                    ) : (
                        <>
                            <span className="mr-[0.3em] inline-block rounded-full bg-secondary px-[0.5em] font-bold text-foreground font-rounded tabular">
                                {comparison.direction === 'less' ? '↓' : '↑'} <CountUp value={comparison.difference} format={(value) => amount(value)} duration={1200} />
                            </span>
                            {comparison.direction} than this time last month
                        </>
                    )}
                </p>
            )}
            {/* Positioned so the chart's own height never holds the card open */}
            <div ref={chartRef} className="relative min-h-0 flex-1">
                {summary.daily.length > 2 && chartHeight >= 30 && (
                    <div className="absolute inset-x-0 bottom-0">
                        <MonthChart
                            daily={summary.daily}
                            lastMonth={summary.spentLastMonthToDate > 0 ? summary.lastMonthDaily : []}
                            label={`Spending so far in ${format(now, 'MMMM')} against ${format(subMonths(now, 1), 'MMMM')}`}
                            height={chartHeight}
                        />
                    </div>
                )}
            </div>
        </section>
    );
}

function Budgets({ budgets }: { budgets: KioskBudget[] }) {
    // Up to six rings stay readable from across the room
    const shown = budgets.slice(0, 6);

    return (
        <section aria-label="Budgets" className="grid min-h-0 grid-cols-2 content-center gap-x-[1em] gap-y-[1.3em] rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            {shown.map((budget, i) => {
                const spent = budget.amount - budget.remaining;
                const color = categoryColor(budget.color);
                const Icon = getCategoryIcon(budget.icon ?? '');

                return (
                    <div key={budget.id} className="grid grid-cols-[5em_minmax(0,1fr)] items-center gap-x-[0.9em] gap-y-[0.1em]">
                        <Ring value={budget.amount > 0 ? (spent / budget.amount) * 100 : 0} color={color} size="5em" index={i} className="row-span-3">
                            <Icon size="1.7em" weight="bold" style={{ color }} aria-hidden="true" />
                        </Ring>
                        <p className="truncate text-[1.2em] font-semibold">{budget.name}</p>
                        <p className="text-[1.05em] whitespace-nowrap text-muted-foreground font-rounded tabular">
                            {budget.remaining >= 0 ? `${amount(budget.remaining)} left` : `${amount(spent)} of ${amount(budget.amount)}`}
                        </p>
                        <p key={budget.status} className="text-[0.95em] font-semibold" style={{ animation: 'fade-in 400ms var(--ease-out-quint)' }}>
                            {budget.status === 'over' && <span className="text-destructive">Over by {amount(-budget.remaining)}</span>}
                            {budget.status === 'ahead' && <span className="text-warning">Ahead of pace</span>}
                            {budget.status === 'used' && <span className="text-muted-foreground">Fully used</span>}
                            {budget.status === 'on_track' && <span className="font-medium text-muted-foreground">On track</span>}
                        </p>
                    </div>
                );
            })}
        </section>
    );
}

function Categories({ categories }: { categories: KioskCategory[] }) {
    const [listRef, listBox] = useSize<HTMLDivElement>();
    const { columns, rows, scale } = categoryLayout(categories.length, listBox.height / (listBox.em || 1), listBox.width / (listBox.em || 1));
    const top = categories[0].total || 1;

    return (
        <section aria-label="Spending by category" className="flex min-h-0 flex-1 flex-col rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            <p className="text-[1.1em] font-medium text-muted-foreground">By category</p>
            <div ref={listRef} className="mt-[0.9em] min-h-0 flex-1 overflow-hidden">
                {/* Biggest first, down each column in turn; the 2.3em rows and 0.9em gaps are what categoryLayout scales */}
                <div
                    className="grid grid-flow-col gap-x-[2.4em] gap-y-[0.9em]"
                    style={{
                        fontSize: `${scale}em`,
                        gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                        gridTemplateRows: `repeat(${rows}, auto)`,
                    }}
                >
                    {categories.map((category, j) => {
                        const Icon = getCategoryIcon(category.icon ?? '');

                        return (
                            <div
                                key={category.id}
                                className="grid grid-cols-[2.3em_minmax(0,1fr)_auto] items-center gap-x-[0.8em] gap-y-[0.3em] leading-none"
                            >
                                <span
                                    className="row-span-2 grid size-[2.3em] place-items-center rounded-[0.65em] text-white"
                                    style={{ backgroundColor: categoryColor(category.color) }}
                                >
                                    <Icon size="1.3em" weight="bold" aria-hidden="true" />
                                </span>
                                <span className="truncate text-[1.3em] font-semibold">{category.name}</span>
                                <span className="text-[1.3em] font-semibold font-rounded tabular">{amount(category.total)}</span>
                                <span className="relative col-span-2 col-start-2 h-[0.45em] overflow-hidden rounded-full bg-track">
                                    <i
                                        className="grow-x absolute inset-y-0 left-0 rounded-full transition-[width] duration-700 ease-out-quint"
                                        style={
                                            {
                                                width: `${(category.total / top) * 100}%`,
                                                background: categoryColor(category.color),
                                                '--j': j,
                                            } as React.CSSProperties
                                        }
                                    />
                                </span>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

// A page out of turn fades out but stays laid out, so turning back to it is only a fade and nothing draws in again
function KioskPage({ shown, className, children }: { shown: boolean; className?: string; children: React.ReactNode }) {
    return (
        <div
            aria-hidden={!shown}
            className={cn(
                'col-start-1 row-start-1 min-h-0 transition-opacity duration-700 ease-out-quint motion-reduce:transition-none',
                !shown && 'pointer-events-none opacity-0',
                className,
            )}
        >
            {children}
        </div>
    );
}

// Which page is up, as the longer dot
function PageDots({ current }: { current: PageName }) {
    return (
        <span aria-hidden="true" className="flex shrink-0 items-center gap-[0.45em]">
            {PAGES.map((page) => (
                <i
                    key={page}
                    className={cn(
                        'h-[0.5em] rounded-full transition-[width,background-color] duration-700 ease-out-quint motion-reduce:transition-none',
                        page === current ? 'w-[1.5em] bg-foreground' : 'w-[0.5em] bg-muted-foreground/40',
                    )}
                />
            ))}
        </span>
    );
}

// An element's size and font size in px, kept current as the screen or the cards around it change
function useSize<T extends HTMLElement>() {
    const ref = useRef<T>(null);
    const [size, setSize] = useState({ width: 0, height: 0, em: 0 });

    useEffect(() => {
        if (!ref.current) return;

        const observer = new ResizeObserver(([entry]) =>
            setSize({ width: entry.contentRect.width, height: entry.contentRect.height, em: parseFloat(getComputedStyle(entry.target).fontSize) }),
        );
        observer.observe(ref.current);

        return () => observer.disconnect();
    }, []);

    return [ref, size] as const;
}

function Latest({ transaction, at, now }: { transaction: Transaction; at: string; now: Date }) {
    const category = transaction.brand.category;
    const when = parseISO(at);
    const minutes = (now.getTime() - when.getTime()) / 60000;

    return (
        <p
            className="flex items-center gap-[0.8em] rounded-full bg-card py-[0.5em] pr-[1.2em] pl-[0.5em] text-[1.05em] shadow-card"
            style={{ animation: 'rise 460ms var(--ease-out-quint)' }}
        >
            <Tile color={category?.color} icon={category?.icon} className="size-[2.2em] rounded-full" />
            <b className="font-semibold">{transaction.brand.name}</b>
            <span className="font-rounded tabular">
                {getAppCurrency()} {amount(transaction.amount, 2)}
            </span>
            <span className="text-muted-foreground">{minutes < 2 ? 'Just now' : formatDistanceToNowStrict(when, { addSuffix: true })}</span>
        </p>
    );
}
