import { useState } from 'react';
import { Link } from '@inertiajs/react';

import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';
import { money } from '@/components/Wallet/format';
import { dismissSuggestion } from '@/Api/home';
import { cn } from '@/lib/utils';
import type { HomeData } from './types';

interface BudgetSuggestionProps {
    suggestion: NonNullable<HomeData['suggestion']>;
    index: number;
    onDismissed: () => void;
}

export default function BudgetSuggestion({ suggestion, index, onDismissed }: BudgetSuggestionProps) {
    const [leaving, setLeaving] = useState(false);
    const category = suggestion.category;

    const notNow = () => {
        setLeaving(true);
        // Hidden until next month; the card fades out either way
        dismissSuggestion('budget-suggestion').catch(console.error);
        window.setTimeout(onDismissed, 240);
    };

    const params = new URLSearchParams({ create: '1' });
    if (category) {
        params.set('category', String(category.id));
        params.set('name', category.name);
    }

    return (
        <Panel title="Set your first budget?" index={index} className={cn('self-start transition-[opacity,transform] duration-200 ease-out-quint', leaving && 'scale-[0.98] opacity-0')}>
            <div className="grid grid-cols-[36px_minmax(0,1fr)] items-center gap-x-3 py-2">
                <Tile color={category?.color} icon={category?.icon ?? 'Wallet'} className="row-span-2" />
                <p className="font-medium">
                    You’ve spent <b className="font-bold font-rounded">{money(suggestion.spent)}</b> on {category?.name ?? 'one category'} this month.
                </p>
                <p className="col-start-2 text-[12.5px] text-muted-foreground">A monthly budget tells you when you’re ahead of pace.</p>
            </div>
            <div className="mt-2 flex flex-wrap gap-2">
                <Link href={`/budgets?${params}`} className="inline-flex h-[34px] items-center rounded-full bg-brand px-4 font-semibold text-brand-foreground hover:bg-brand-hover transition-transform duration-150 ease-out-quint active:scale-[0.97]">
                    Set a budget
                </Link>
                <button type="button" onClick={notNow} className="inline-flex h-[34px] items-center rounded-full bg-secondary px-4 font-semibold">
                    Not now
                </button>
            </div>
        </Panel>
    );
}
