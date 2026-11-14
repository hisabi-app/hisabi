import { useEffect, useState } from 'react';
import { format } from 'date-fns';
import { CheckIcon } from '@phosphor-icons/react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { getAllCategories } from '@/Api';
import { createBudget } from '@/Api/budgets';
import { getAppCurrency } from '@/Utils';
import { RecurringReoccurrence, getPeriodStart } from '@/Utils/budgets';

type Reoccurrence = RecurringReoccurrence | 'CUSTOM';

const PERIODS: { value: Reoccurrence; label: string }[] = [
    { value: 'MONTHLY', label: 'Monthly' },
    { value: 'WEEKLY', label: 'Weekly' },
    { value: 'YEARLY', label: 'Yearly' },
    { value: 'CUSTOM', label: 'Custom' },
];

interface Category {
    id: number;
    name: string;
    type: string;
}

const today = () => format(new Date(), 'yyyy-MM-dd');

export default function Create({ open, onClose, onCreated }: { open: boolean; onClose: () => void; onCreated: () => void }) {
    const [categories, setCategories] = useState<Category[] | null>(null);
    const [name, setName] = useState('');
    const [amount, setAmount] = useState('');
    const [reoccurrence, setReoccurrence] = useState<Reoccurrence>('MONTHLY');
    const [from, setFrom] = useState(today);
    const [to, setTo] = useState('');
    const [categoryIds, setCategoryIds] = useState<number[]>([]);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(false);

    useEffect(() => {
        if (!open || categories) return;

        getAllCategories()
            .then(({ data }) => setCategories(data.allCategories.filter((category: Category) => category.type === 'EXPENSES')))
            .catch(() => setCategories([]));
    }, [open, categories]);

    const custom = reoccurrence === 'CUSTOM';
    const valid = name.trim() !== '' && Number(amount) > 0 && categoryIds.length > 0 && (!custom || (from !== '' && to !== '' && to >= from));

    const toggleCategory = (id: number) =>
        setCategoryIds((ids) => (ids.includes(id) ? ids.filter((selected) => selected !== id) : [...ids, id]));

    const reset = () => {
        setName('');
        setAmount('');
        setReoccurrence('MONTHLY');
        setFrom(today());
        setTo('');
        setCategoryIds([]);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!valid || saving) return;

        setSaving(true);
        setError(false);
        createBudget({
            name: name.trim(),
            amount: Number(amount),
            reoccurrence,
            startAt: reoccurrence === 'CUSTOM' ? from : getPeriodStart(reoccurrence, new Date()),
            endAt: custom ? to : undefined,
            categoryIds,
        })
            .then(() => {
                reset();
                onCreated();
            })
            .catch(() => setError(true))
            .finally(() => setSaving(false));
    };

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>New budget</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-5">
                    <div className="grid gap-2">
                        <Label htmlFor="budget-name">Name</Label>
                        <Input id="budget-name" autoFocus value={name} placeholder="e.g. Dining out" onChange={(event) => setName(event.target.value)} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="budget-amount">{getAppCurrency() ? `Amount (${getAppCurrency()})` : 'Amount'}</Label>
                        <Input
                            id="budget-amount"
                            type="number"
                            inputMode="decimal"
                            min={0}
                            step="any"
                            className="tabular-nums"
                            value={amount}
                            onChange={(event) => setAmount(event.target.value)}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label>Period</Label>
                        <Tabs value={reoccurrence} onValueChange={(value) => setReoccurrence(value as Reoccurrence)}>
                            <TabsList className="grid w-full grid-cols-4">
                                {PERIODS.map(({ value, label }) => (
                                    <TabsTrigger key={value} value={value}>
                                        {label}
                                    </TabsTrigger>
                                ))}
                            </TabsList>
                        </Tabs>
                        {custom && (
                            <div className="grid grid-cols-2 gap-3 pt-1">
                                <div className="grid gap-2">
                                    <Label htmlFor="budget-from">From</Label>
                                    <Input id="budget-from" type="date" value={from} max={to || undefined} onChange={(event) => setFrom(event.target.value)} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="budget-to">To</Label>
                                    <Input id="budget-to" type="date" value={to} min={from || undefined} onChange={(event) => setTo(event.target.value)} />
                                </div>
                            </div>
                        )}
                    </div>

                    <fieldset className="grid gap-2">
                        <legend className="mb-2 text-sm font-medium">Categories</legend>
                        {categories === null && <p className="text-sm text-muted-foreground">Loading…</p>}
                        {categories?.length === 0 && <p className="text-sm text-muted-foreground">Add an expense category first.</p>}
                        {categories && categories.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {categories.map((category) => {
                                    const selected = categoryIds.includes(category.id);

                                    return (
                                        <button
                                            key={category.id}
                                            type="button"
                                            aria-pressed={selected}
                                            onClick={() => toggleCategory(category.id)}
                                            className={`inline-flex h-8 items-center gap-1.5 rounded-full border px-3 text-sm transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none ${
                                                selected ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted'
                                            }`}
                                        >
                                            {selected && <CheckIcon size={14} weight="bold" aria-hidden="true" />}
                                            {category.name}
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </fieldset>

                    {error && <p className="text-sm text-red-700 dark:text-red-400">Couldn't create the budget. Try again.</p>}

                    <DialogFooter>
                        <Button type="button" variant="ghost" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={!valid || saving}>
                            {saving ? 'Creating…' : 'Create budget'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
