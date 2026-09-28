import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { endOfMonth, format, max as maxDate, min as minDate, parseISO, startOfMonth } from 'date-fns';
import { CaretDownIcon, SlidersHorizontalIcon } from '@phosphor-icons/react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Slider } from '@/components/ui/slider';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { formatNumber, getAppCurrency } from '@/Utils';
import { updateBudget } from '@/Api/budgets';
import { BudgetHistory, HistoryBand, HistoryPeriod, getHistoryBand, monthsBetween } from '@/Utils/budgets';
import LoadingView from '../Global/LoadingView';

export interface HistoryMeta {
    from: string;
    to: string;
    earliest_date: string;
    first_transaction_date: string | null;
}

export interface HistoryRange {
    from: string;
    to: string;
}

export interface HistoryState {
    data?: { budgets: BudgetHistory[]; meta: HistoryMeta };
    loading: boolean;
    error: boolean;
}

const BAND_STYLES: Record<HistoryBand, string> = {
    under: 'bg-green-200 text-green-900 dark:bg-green-500/35 dark:text-green-100',
    near: 'bg-orange-200 text-orange-900 dark:bg-orange-500/35 dark:text-orange-100',
    over: 'bg-red-600 text-white',
};

const CURRENT_CELL = 'border border-dashed border-neutral-400 text-muted-foreground dark:border-neutral-500';

const money = (value: number) => `${getAppCurrency()} ${formatNumber(Math.round(value), '0,0')}`.trim();

// Budgets that don't fit the monthly grid, grouped by how often they repeat
const OTHER_GROUPS = [
    { reoccurrence: 'YEARLY', title: 'Yearly budgets', unit: 'year' },
    { reoccurrence: 'MONTHLY', title: 'Multi-month budgets', unit: 'month' },
    { reoccurrence: 'WEEKLY', title: 'Weekly budgets', unit: 'week' },
    { reoccurrence: 'DAILY', title: 'Daily budgets', unit: 'day' },
];

const isCalendarMonthly = (budget: BudgetHistory) => budget.reoccurrence === 'MONTHLY' && budget.period === 1;

// Months get the year once the periods span more than a year
const periodLabel = (budget: BudgetHistory, period: HistoryPeriod) => {
    const start = parseISO(period.start_date);

    if (budget.reoccurrence === 'YEARLY') return format(start, 'yyyy');
    if (budget.reoccurrence === 'MONTHLY') return format(start, budget.period === 1 && budget.periods.length <= 12 ? 'MMM' : 'MMM yy');
    if (budget.reoccurrence === 'DAILY') return format(start, 'd');

    return format(start, 'MMM d');
};

// Hover details for a period: its month, amount spent and %
const PeriodTooltip = ({ period, children }: { period: HistoryPeriod; children: React.ReactElement }) => {
    const start = parseISO(period.start_date);

    return (
        <Tooltip>
            <TooltipTrigger asChild>{children}</TooltipTrigger>
            <TooltipContent>
                {format(start, start.getDate() === 1 ? 'MMM yyyy' : 'MMM d, yyyy')}: {money(period.spent)} ({period.percentage}%){period.is_current && ' so far'}
            </TooltipContent>
        </Tooltip>
    );
};

export function BudgetsHistoryView({ state, onAmountSaved }: { state: HistoryState; onAmountSaved: () => void }) {
    if (!state.data) {
        return state.error ? (
            <Card className="p-8 text-center text-sm text-muted-foreground">Couldn't load budget history. Try again.</Card>
        ) : (
            <Card className="h-[158px]">
                <LoadingView />
            </Card>
        );
    }

    const { budgets, meta } = state.data;

    return (
        <div className="grid gap-6">
            {state.error && <p className="text-sm text-red-700 dark:text-red-400">Couldn't update the range. Try again.</p>}

            {budgets.length === 0 ? (
                <Card className="p-8 text-center text-sm text-muted-foreground">Only recurring budgets have history.</Card>
            ) : (
                <div className={`transition-opacity ${state.loading ? 'opacity-60' : ''}`}>
                    <HistoryContent budgets={budgets} meta={meta} onAmountSaved={onAmountSaved} />
                </div>
            )}
        </div>
    );
}

export function HistoryRangePicker({ meta, onChange }: { meta: HistoryMeta; onChange: (range: HistoryRange) => void }) {
    const current = startOfMonth(new Date());
    const from = startOfMonth(parseISO(meta.from));
    const to = startOfMonth(parseISO(meta.to));
    // From the first transaction (or the selected start, if earlier), but never more than 5 years back
    const first = meta.first_transaction_date ? startOfMonth(parseISO(meta.first_transaction_date)) : from;
    const months = monthsBetween(maxDate([parseISO(meta.earliest_date), minDate([first, from])]), current);
    const last = months.length - 1;

    const indexOf = (date: Date) => Math.min(last, Math.max(0, months.findIndex((month) => month.getTime() === date.getTime())));
    const selected: [number, number] = [indexOf(from), indexOf(to)];
    const [draft, setDraft] = useState<number[]>(selected);

    useEffect(() => setDraft(selected), [meta.from, meta.to]);

    const apply = ([start, end]: number[]) =>
        onChange({ from: format(months[start], 'yyyy-MM-dd'), to: format(endOfMonth(months[end]), 'yyyy-MM-dd') });

    const januaryIndex = months.findIndex((month) => month.getFullYear() === current.getFullYear());
    const presets = [
        { label: 'Last 12 months', range: [Math.max(0, last - 11), last] },
        { label: 'This year', range: [Math.max(0, januaryIndex), last] },
        { label: 'Last 2 years', range: [Math.max(0, last - 23), last] },
        { label: 'Last 3 years', range: [Math.max(0, last - 35), last] },
        { label: `All since ${format(months[0], 'MMM yyyy')}`, range: [0, last] },
    ].filter((preset, index, all) => all.findIndex((other) => other.range.join() === preset.range.join()) === index);

    const count = draft[1] - draft[0] + 1;
    const yearMarks = months
        .map((month, index) => ({ month, index }))
        .filter(({ month, index }) => last > 0 && (index === 0 || month.getMonth() === 0));

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button variant="outline" className="gap-2">
                    <span className="sm:hidden">
                        {format(from, 'MMM yy')} – {format(to, 'MMM yy')}
                    </span>
                    <span className="hidden sm:inline">
                        {format(from, 'MMM yyyy')} – {format(to, 'MMM yyyy')}
                    </span>
                    <CaretDownIcon size={14} aria-hidden="true" />
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="grid w-[min(92vw,440px)] gap-5 p-5">
                <div className="flex flex-wrap gap-2">
                    {presets.map((preset) => {
                        const active = preset.range[0] === selected[0] && preset.range[1] === selected[1];

                        return (
                            <Button key={preset.label} size="sm" variant={active ? 'default' : 'outline'} onClick={() => apply(preset.range)}>
                                {preset.label}
                            </Button>
                        );
                    })}
                </div>

                {last > 0 && (
                    <div className="grid gap-2">
                        <div className="flex items-baseline justify-between gap-3 text-sm">
                            <span className="font-medium">
                                {format(months[draft[0]], 'MMM yyyy')} – {format(months[draft[1]], 'MMM yyyy')}
                            </span>
                            <span className="text-xs text-muted-foreground">
                                {count} {count === 1 ? 'month' : 'months'}
                            </span>
                        </div>
                        <Slider
                            min={0}
                            max={last}
                            step={1}
                            value={draft}
                            onValueChange={setDraft}
                            onValueCommit={apply}
                            thumbLabels={['Start month', 'End month']}
                            valueText={(index) => format(months[index], 'MMMM yyyy')}
                        />
                        <div className="relative h-4 text-[11px] text-muted-foreground">
                            {yearMarks.map(({ month, index }) => (
                                <span key={index} className="absolute -translate-x-1/2 first:translate-x-0" style={{ left: `${(index / last) * 100}%` }}>
                                    {index === 0 ? format(month, 'MMM yyyy') : format(month, 'yyyy')}
                                </span>
                            ))}
                        </div>
                    </div>
                )}
            </PopoverContent>
        </Popover>
    );
}

// Which budget is being adjusted in the dock, and the amount being tried
interface Adjusting {
    activeId: number | null;
    trialFor: (budget: BudgetHistory) => number | null;
    start: (budget: BudgetHistory) => void;
}

function HistoryContent({ budgets, meta, onAmountSaved }: { budgets: BudgetHistory[]; meta: HistoryMeta; onAmountSaved: () => void }) {
    const months = monthsBetween(parseISO(meta.from), parseISO(meta.to));
    const monthly = budgets.filter(isCalendarMonthly);
    const groups = OTHER_GROUPS.map((group) => ({
        ...group,
        budgets: budgets.filter((budget) => !isCalendarMonthly(budget) && budget.reoccurrence === group.reoccurrence),
    })).filter((group) => group.budgets.length > 0);

    const [active, setActive] = useState<{ id: number; draft: number } | null>(null);
    // A saved amount keeps colouring its row until the reloaded history brings it back
    const [saved, setSaved] = useState<{ id: number; amount: number } | null>(null);
    useEffect(() => setSaved(null), [budgets]);

    const activeBudget = active ? budgets.find((budget) => budget.id === active.id) : undefined;

    const adjusting: Adjusting = {
        activeId: activeBudget ? activeBudget.id : null,
        trialFor: (budget) => {
            if (activeBudget?.id === budget.id && active && Number.isFinite(active.draft) && active.draft > 0) return active.draft;
            if (saved?.id === budget.id) return saved.amount;
            return null;
        },
        start: (budget) => setActive({ id: budget.id, draft: budget.amount }),
    };

    return (
        <div className="grid gap-6">
            {monthly.length > 0 && (
                <Card className="gap-0 p-0">
                    <MonthlyGrid budgets={monthly} months={months} adjusting={adjusting} />
                </Card>
            )}

            {groups.map((group) => (
                <Card key={group.reoccurrence} className="gap-0 p-0">
                    <div className="grid gap-2 p-5 md:p-6">
                        <p className="text-xs text-muted-foreground">{group.title}</p>
                        {group.budgets.map((budget) => (
                            <GroupRow
                                key={budget.id}
                                budget={budget}
                                every={budget.period > 1 ? `every ${budget.period} ${group.unit}s` : undefined}
                                adjusting={adjusting}
                            />
                        ))}
                    </div>
                </Card>
            ))}

            {activeBudget && active && (
                <AdjustDock
                    key={activeBudget.id}
                    budget={activeBudget}
                    draft={active.draft}
                    onChange={(draft) => setActive({ id: activeBudget.id, draft })}
                    onClose={() => setActive(null)}
                    onSaved={(amount) => {
                        setSaved({ id: activeBudget.id, amount });
                        setActive(null);
                        onAmountSaved();
                    }}
                />
            )}
        </div>
    );
}

// Width of an element, kept up to date as the window resizes
function useWidth<T extends HTMLElement>() {
    const ref = useRef<T>(null);
    const [width, setWidth] = useState(0);

    useLayoutEffect(() => {
        if (!ref.current || typeof ResizeObserver === 'undefined') return;

        const observer = new ResizeObserver(([entry]) => setWidth(entry.contentRect.width));
        observer.observe(ref.current);

        return () => observer.disconnect();
    }, []);

    return [ref, width] as const;
}

// Months always fit the card: cells shrink with the range, and too-narrow cells show only their colour
function MonthlyGrid({ budgets, months, adjusting }: { budgets: BudgetHistory[]; months: Date[]; adjusting: Adjusting }) {
    const [ref, width] = useWidth<HTMLDivElement>();
    const nameWidth = width > 0 && width < 560 ? 96 : 150;
    const gap = months.length > 24 ? 2 : 6;
    const cellWidth = width > 0 ? (width - nameWidth - gap * months.length) / months.length : 48;
    const compact = cellWidth < 40;
    const labelEvery = Math.max(1, Math.ceil(34 / Math.max(cellWidth, 1)));
    const columns = `${nameWidth}px repeat(${months.length}, minmax(0, 1fr))`;

    return (
        <div ref={ref} className="grid gap-2 p-5 md:p-6">
            <div className="grid items-end pb-1 text-xs text-muted-foreground" style={{ gridTemplateColumns: columns, columnGap: gap }}>
                <span>Monthly budgets</span>
                {months.map((month, i) => (
                    <span key={month.toISOString()} className="grid justify-items-center gap-0.5 whitespace-nowrap">
                        <span className="h-3 text-[10px]">{i === 0 || month.getMonth() === 0 ? format(month, 'yyyy') : ''}</span>
                        <span>{i % labelEvery === 0 ? format(month, 'MMM') : '\u00a0'}</span>
                    </span>
                ))}
            </div>

            {budgets.map((budget) => (
                <MonthlyRow key={budget.id} budget={budget} months={months} compact={compact} style={{ gridTemplateColumns: columns, columnGap: gap }} adjusting={adjusting} />
            ))}
        </div>
    );
}

// A compact cell shows only its colour; its % is in a tooltip
function HistoryCell({ period, label, compact = false, className = '' }: { period: HistoryPeriod; label?: string; compact?: boolean; className?: string }) {
    const style = period.is_current ? CURRENT_CELL : BAND_STYLES[getHistoryBand(period.percentage)];
    const classes = `flex items-center justify-center gap-1.5 overflow-hidden text-xs font-medium whitespace-nowrap tabular-nums ${style} ${className}`;

    if (compact) {
        return (
            <PeriodTooltip period={period}>
                <span className={`h-8 rounded-md ${classes}`} />
            </PeriodTooltip>
        );
    }

    return (
        <PeriodTooltip period={period}>
            <span className={`h-10 rounded-lg ${classes}`}>
                {label && <span className="hidden font-normal opacity-70 sm:inline">{label}</span>}
                {period.percentage}%
            </span>
        </PeriodTooltip>
    );
}

// The budget's periods measured against the amount being tried, if any
const periodsFor = (budget: BudgetHistory, amount: number | null) =>
    amount === null ? budget.periods : budget.periods.map((period) => ({ ...period, percentage: Math.round((period.spent / amount) * 100) }));

// While another budget is being adjusted, this row fades back
const rowFade = (adjusting: Adjusting, budget: BudgetHistory) =>
    `transition-opacity ${adjusting.activeId !== null && adjusting.activeId !== budget.id ? 'opacity-30' : ''}`;

function MonthlyRow({
    budget,
    months,
    compact,
    style,
    adjusting,
}: {
    budget: BudgetHistory;
    months: Date[];
    compact: boolean;
    style: React.CSSProperties;
    adjusting: Adjusting;
}) {
    const trial = adjusting.trialFor(budget);
    const byMonth = new Map(periodsFor(budget, trial).map((period) => [period.start_date.slice(0, 7), period]));

    return (
        <div className={`grid items-center ${rowFade(adjusting, budget)}`} style={style}>
            <BudgetName budget={budget} amount={trial ?? budget.amount} adjusting={adjusting} />
            {months.map((month) => {
                const period = byMonth.get(format(month, 'yyyy-MM'));

                return period ? (
                    <HistoryCell key={month.toISOString()} period={period} compact={compact} />
                ) : (
                    <span key={month.toISOString()} className={`rounded-md bg-muted/40 ${compact ? 'h-8' : 'h-10'}`} />
                );
            })}
        </div>
    );
}

function GroupRow({ budget, every, adjusting }: { budget: BudgetHistory; every?: string; adjusting: Adjusting }) {
    const trial = adjusting.trialFor(budget);
    const periods = periodsFor(budget, trial);

    return (
        <div className={`grid items-center gap-x-4 gap-y-2 md:grid-cols-[150px_minmax(0,1fr)] ${rowFade(adjusting, budget)}`}>
            <BudgetName budget={budget} amount={trial ?? budget.amount} every={every} adjusting={adjusting} />
            <div className={`flex ${periods.length > 24 ? 'gap-0.5' : 'gap-1.5'}`}>
                {periods.map((period) => (
                    <HistoryCell
                        key={period.start_date}
                        period={period}
                        label={periodLabel(budget, period)}
                        compact={periods.length > 8}
                        className="min-w-0 flex-1"
                    />
                ))}
            </div>
        </div>
    );
}

function BudgetName({ budget, amount, every, adjusting }: { budget: BudgetHistory; amount: number; every?: string; adjusting: Adjusting }) {
    const active = adjusting.activeId === budget.id;

    return (
        <span className="grid min-w-0 gap-0.5 pr-2">
            <span className="truncate text-sm font-medium">{budget.name}</span>
            <span className="flex min-w-0 items-center gap-1 text-xs text-muted-foreground">
                <span className={`truncate tabular-nums ${amount !== budget.amount ? 'font-medium text-foreground' : ''}`}>
                    {money(amount)}
                    {every && ` · ${every}`}
                </span>
                <button
                    type="button"
                    aria-label={`Try a different amount for ${budget.name}`}
                    aria-pressed={active}
                    onClick={() => adjusting.start(budget)}
                    className={`shrink-0 rounded p-0.5 transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none ${active ? 'bg-muted text-foreground' : ''}`}
                >
                    <SlidersHorizontalIcon size={14} aria-hidden="true" />
                </button>
            </span>
        </span>
    );
}

// Docked at the bottom of the view while a budget's amount is being tried
function AdjustDock({
    budget,
    draft,
    onChange,
    onClose,
    onSaved,
}: {
    budget: BudgetHistory;
    draft: number;
    onChange: (amount: number) => void;
    onClose: () => void;
    onSaved: (amount: number) => void;
}) {
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(false);

    const step = budget.amount >= 10000 ? 100 : budget.amount >= 1000 ? 50 : 10;
    const maxSpent = Math.max(0, ...budget.periods.map((period) => period.spent));
    const max = Math.ceil(Math.max(budget.amount * 2, maxSpent * 1.25) / step) * step;
    const valid = Number.isFinite(draft) && draft > 0;

    useEffect(() => {
        const onKeyDown = (event: KeyboardEvent) => event.key === 'Escape' && onClose();
        window.addEventListener('keydown', onKeyDown);

        return () => window.removeEventListener('keydown', onKeyDown);
    }, [onClose]);

    const save = () => {
        setSaving(true);
        setError(false);
        updateBudget({ id: budget.id, amount: draft })
            .then(() => onSaved(draft))
            .catch(() => setError(true))
            .finally(() => setSaving(false));
    };

    return (
        <div className="sticky bottom-8 z-20">
            <Card
                role="region"
                aria-label={`Adjust ${budget.name} amount`}
                className="flex-row flex-wrap items-center gap-x-6 gap-y-3 rounded-3xl px-6 py-3 shadow-lg md:flex-nowrap md:rounded-full"
            >
                <div className="grid min-w-0 gap-0.5 md:w-44">
                    <span className="truncate text-sm font-medium">{budget.name}</span>
                    <span className="text-xs text-muted-foreground tabular-nums">Current {money(budget.amount)}</span>
                </div>

                <Slider
                    className="min-w-0 flex-1 basis-56"
                    min={step}
                    max={max}
                    step={step}
                    value={[Math.min(Math.max(valid ? draft : step, step), max)]}
                    onValueChange={([value]) => onChange(value)}
                    thumbLabels={[`${budget.name} amount`]}
                    valueText={(value) => money(value)}
                />

                <Label htmlFor="budget-trial-amount" className="sr-only">
                    Amount
                </Label>
                <Input
                    id="budget-trial-amount"
                    autoFocus
                    type="number"
                    inputMode="decimal"
                    min={0}
                    step={step}
                    className="w-28 tabular-nums"
                    value={Number.isFinite(draft) ? draft : ''}
                    onChange={(event) => onChange(event.target.value === '' ? NaN : Number(event.target.value))}
                />

                <div className="flex gap-2">
                    <Button variant="ghost" size="sm" onClick={onClose}>
                        Cancel
                    </Button>
                    <Button size="sm" disabled={saving || !valid || draft === budget.amount} onClick={save}>
                        {saving ? 'Saving…' : 'Save'}
                    </Button>
                </div>

                {error && <p className="basis-full text-xs text-red-700 dark:text-red-400">Couldn't save the amount. Try again.</p>}
            </Card>
        </div>
    );
}
