import { format, parseISO, subMonths } from 'date-fns';

import MonthChart from '@/components/Home/MonthChart';
import { Skeleton } from '@/components/ui/skeleton';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount } from '@/components/Wallet/format';
import { useSize } from '@/hooks/useSize';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import { toKioskBudgets } from '@/Utils/kiosk';
import { cn } from '@/lib/utils';

import BudgetRingsCard from './BudgetRingsCard';
import CategoryBarsCard from './CategoryBarsCard';
import AmountCard from './AmountCard';
import TrendChart from './TrendChart';
import type { AnswerCard, TransactionRow } from './types';

// Claude's answer, as one or two of the kiosk's own cards side by side
export default function AnswerPage({ cards }: { cards: AnswerCard[] }) {
    return (
        <div className={cn('grid h-full min-h-0 grid-rows-[minmax(0,1fr)] gap-[1.1em]', cards.length > 1 && 'grid-cols-2')}>
            {cards.map((card, i) => (
                <div key={i} className="rise grid min-h-0 grid-rows-[minmax(0,1fr)]" style={{ '--i': i } as React.CSSProperties}>
                    <Card card={card} alone={cards.length === 1} />
                </div>
            ))}
        </div>
    );
}

// While Claude looks it up: the shape of two cards, shimmering
export function ThinkingPage() {
    return (
        <div className="grid h-full min-h-0 grid-cols-2 grid-rows-[minmax(0,1fr)] gap-[1.1em]" aria-busy="true" aria-label="Looking it up">
            {[0, 1].map((i) => (
                <div
                    key={i}
                    className="rise flex flex-col gap-[1em] rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card"
                    style={{ '--i': i } as React.CSSProperties}
                >
                    <Skeleton className="h-[1.1em] w-[8em]" />
                    <Skeleton className="h-[4.2em] w-[60%]" />
                    <Skeleton className="mt-auto h-[2.4em] w-full" />
                    <Skeleton className="h-[2.4em] w-full" />
                </div>
            ))}
        </div>
    );
}

function Card({ card, alone }: { card: AnswerCard; alone: boolean }) {
    const now = new Date();

    switch (card.type) {
        case 'amount':
            return (
                <AmountCard
                    label={card.label}
                    total={card.total}
                    comparison={card.comparison}
                    chart={
                        card.daily.length > 2
                            ? (height) => (
                                  <MonthChart
                                      daily={card.daily}
                                      lastMonth={card.previous_daily}
                                      label={`${card.label} in ${format(now, 'MMMM')} against ${format(subMonths(now, 1), 'MMMM')}`}
                                      height={height}
                                  />
                              )
                            : undefined
                    }
                />
            );
        case 'trend':
            return (
                <AmountCard
                    label={card.label}
                    total={card.total}
                    comparison={card.comparison}
                    // The line is what was asked for, so it takes the room it can
                    maxChartHeight={170}
                    chart={
                        card.points.length > 1
                            ? (height) => <TrendChart points={card.points} running={card.running} label={card.label} height={height} />
                            : undefined
                    }
                />
            );
        case 'bars':
            return <CategoryBarsCard label={card.label} rows={card.rows} />;
        case 'budgets':
            return <BudgetRingsCard budgets={toKioskBudgets(card.budgets)} large={alone} />;
        case 'transactions':
            return <TransactionsCard label={card.label} rows={card.rows} />;
        case 'sentence':
            return <SentenceCard text={card.text} />;
    }
}

// A row is 3.9em tall: the name and date (2.6em), 0.6em above and below, and the line between rows
const TRANSACTION_ROW = 3.9;

function TransactionsCard({ label, rows }: { label: string; rows: TransactionRow[] }) {
    const [listRef, listBox] = useSize<HTMLDivElement>();
    // The rows grow to fill the card when there are few, up to 1.3 times, and shrink to fit when there are many
    const scale = listBox.em > 0 ? Math.max(0.7, Math.min(1.3, listBox.height / listBox.em / (rows.length * TRANSACTION_ROW))) : 1;

    return (
        <section aria-label={label} className="flex min-h-0 flex-col rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            <p className="text-[1.1em] font-medium text-muted-foreground">{label}</p>
            <div ref={listRef} className="mt-[0.5em] min-h-0 flex-1 overflow-hidden">
                <ul style={{ fontSize: `${scale}em` }}>
                    {rows.map((row) => {
                        const Icon = getCategoryIcon(row.icon ?? '');

                        return (
                            <li key={row.id} className="flex items-center gap-[0.8em] border-b border-border py-[0.6em] leading-none last:border-0">
                                <span
                                    className="grid size-[2.3em] shrink-0 place-items-center rounded-[0.65em] text-white"
                                    style={{ backgroundColor: categoryColor(row.color) }}
                                >
                                    <Icon size="1.3em" weight="bold" aria-hidden="true" />
                                </span>
                                <span className="min-w-0 flex-1">
                                    <span className="block truncate text-[1.3em] font-semibold">{row.brand ?? 'Unknown'}</span>
                                    <span className="mt-[0.3em] block text-[1em] text-muted-foreground">
                                        {format(parseISO(row.date), 'EEE d MMM')}
                                    </span>
                                </span>
                                <span className="text-[1.3em] font-semibold font-rounded tabular">{amount(row.amount, 2)}</span>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </section>
    );
}

// What Claude said, when no card shows it better (or something went wrong)
function SentenceCard({ text }: { text: string }) {
    return (
        <section className="flex min-h-0 flex-col justify-center rounded-[1.6em] bg-card px-[2em] py-[1.6em] shadow-card">
            <p className="text-[2.6em] leading-[1.2] font-semibold tracking-[-0.02em] text-balance">{text}</p>
        </section>
    );
}
