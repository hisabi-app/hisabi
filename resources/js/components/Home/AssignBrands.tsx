import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import { CheckIcon } from '@phosphor-icons/react';

import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SheetTitle } from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import { money } from '@/components/Wallet/format';
import { getAllCategories, updateBrand } from '@/Api';
import { notifyDataChanged } from '@/hooks/useDataChanged';
import { cn } from '@/lib/utils';
import type { UncategorizedBrand } from './types';

interface Category {
    id: number;
    name: string;
    type: string;
}

const TYPE_LABELS: Record<string, string> = { EXPENSES: 'Spending', INCOME: 'Income', SAVINGS: 'Savings', INVESTMENT: 'Investment' };

// Needs you → Assign: pick a category for each new brand, right here
export default function AssignBrands({ brands, onClose }: { brands: UncategorizedBrand[]; onClose: () => void }) {
    const [categories, setCategories] = useState<Category[] | null>(null);
    // Brands assigned while the panel is open, so they can check off and slide away
    const [done, setDone] = useState<Record<number, string>>({});
    const [gone, setGone] = useState<number[]>([]);
    const [failed, setFailed] = useState<number | null>(null);

    useEffect(() => {
        getAllCategories()
            .then(({ data }) => setCategories(data.allCategories))
            .catch(() => setCategories([]));
    }, []);

    const assign = (brand: UncategorizedBrand, categoryId: string) => {
        const category = categories?.find((candidate) => String(candidate.id) === categoryId);
        if (!category) return;

        setFailed(null);
        updateBrand({ id: brand.id, name: brand.name, categoryId: category.id })
            .then(() => {
                setDone((current) => ({ ...current, [brand.id]: category.name }));
                window.setTimeout(() => setGone((current) => [...current, brand.id]), 650);
                notifyDataChanged();
                router.reload({ only: ['nav'] });
            })
            .catch(() => setFailed(brand.id));
    };

    const remaining = brands.filter((brand) => !gone.includes(brand.id));
    const groups = Object.entries(TYPE_LABELS)
        .map(([type, label]) => ({ label, items: (categories ?? []).filter((category) => category.type === type) }))
        .filter((group) => group.items.length > 0);

    return (
        <div className="grid gap-4 p-5 pt-[18px]">
            <div className="pr-10">
                <SheetTitle className="text-xl font-bold tracking-[-0.02em]">Choose categories</SheetTitle>
                <p className="mt-1 text-muted-foreground">Every transaction from these brands, past and future, goes to the category you pick.</p>
            </div>

            {categories === null && <Skeleton className="h-40 w-full" />}

            {categories !== null && remaining.length === 0 && (
                <div className="grid justify-items-start gap-3" style={{ animation: 'rise 400ms var(--ease-out-quint)' }}>
                    <p className="flex items-center gap-2.5 font-semibold">
                        <span className="grid size-6 place-items-center rounded-full bg-success text-white">
                            <CheckIcon size={14} weight="bold" aria-hidden="true" />
                        </span>
                        All brands have a category
                    </p>
                    <Button onClick={onClose}>Done</Button>
                </div>
            )}

            {categories !== null && remaining.length > 0 && (
                <div className="divide-y">
                    {remaining.map((brand) => {
                        const assigned = done[brand.id];

                        return (
                            <div key={brand.id} className={cn('grid gap-2 py-3 transition-[opacity,transform] duration-300 ease-out-quint', assigned && 'translate-x-3 opacity-0 delay-300')}>
                                <div className="flex items-baseline justify-between gap-3">
                                    <p className="min-w-0 truncate font-semibold">{brand.name}</p>
                                    <p className="shrink-0 text-[12.5px] text-muted-foreground font-rounded tabular">
                                        {brand.transactions} × · {money(brand.total, 2)}
                                    </p>
                                </div>
                                {assigned ? (
                                    <p className="flex h-10 items-center gap-2 font-semibold text-success">
                                        <CheckIcon size={15} weight="bold" aria-hidden="true" />
                                        Filed under {assigned}
                                    </p>
                                ) : (
                                    <Select onValueChange={(value) => assign(brand, value)}>
                                        <SelectTrigger className="h-10 w-full" aria-label={`Category for ${brand.name}`}>
                                            <SelectValue placeholder="Pick a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {groups.map((group) => (
                                                <SelectGroup key={group.label}>
                                                    <SelectLabel>{group.label}</SelectLabel>
                                                    {group.items.map((category) => (
                                                        <SelectItem key={category.id} value={String(category.id)}>
                                                            {category.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                )}
                                {failed === brand.id && <p className="text-[12.5px] text-destructive">Couldn’t save. Try again.</p>}
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
