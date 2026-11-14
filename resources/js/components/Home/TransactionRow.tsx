import Tile from '@/components/Wallet/Tile';
import { amount } from '@/components/Wallet/format';
import { cn } from '@/lib/utils';
import { shortDate } from './dates';
import type { Transaction } from './types';

interface TransactionRowProps {
    transaction: Transaction;
    // Shown under the brand instead of the category, e.g. inside a category's own detail
    hideCategory?: boolean;
    onChooseCategory?: () => void;
}

export default function TransactionRow({ transaction, hideCategory, onChooseCategory }: TransactionRowProps) {
    const category = transaction.brand.category;
    const isIncome = category?.type === 'INCOME';

    return (
        <div className="grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 py-2.5">
            <Tile color={category?.color} icon={category?.icon} className="row-span-2" label={category?.name ?? 'No category'} />
            <p className="truncate font-semibold">{transaction.brand.name}</p>
            <p className="col-start-2 truncate text-[12.5px] text-muted-foreground">
                {category || hideCategory ? (
                    <>{hideCategory ? shortDate(transaction.created_at) : `${category?.name} · ${shortDate(transaction.created_at)}`}</>
                ) : onChooseCategory ? (
                    <button type="button" onClick={onChooseCategory} className="font-semibold text-info hover:underline">
                        Choose a category
                    </button>
                ) : (
                    shortDate(transaction.created_at)
                )}
            </p>
            <p className={cn('col-start-3 row-span-2 row-start-1 text-right text-sm font-semibold font-rounded tabular', isIncome && 'text-success')}>
                {isIncome ? '+' : ''}
                {amount(transaction.amount, 2)}
            </p>
        </div>
    );
}
