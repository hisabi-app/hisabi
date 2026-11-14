import Panel from '@/components/Wallet/Panel';
import Ring from '@/components/Wallet/Ring';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount, toAmount } from '@/components/Wallet/format';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import type { Budget } from './types';

export function budgetStatusLine(status: Budget['status'], spent: number, total: number) {
    switch (status) {
        case 'over':
            return <span className="font-semibold text-destructive">Over by {amount(spent - total)}</span>;
        case 'ahead':
            return <span className="font-semibold text-warning">Ahead of pace</span>;
        case 'used':
            return <span className="text-muted-foreground">Fully used</span>;
        default:
            return <span className="text-muted-foreground">{amount(total - spent)} left</span>;
    }
}

export default function BudgetRings({ budgets, index, onOpen }: { budgets: Budget[]; index: number; onOpen: (id: number) => void }) {
    return (
        <Panel title="Budgets" more={{ label: 'All', href: '/budgets' }} index={index}>
            <div className="mt-2 grid grid-cols-2 gap-x-2 gap-y-3 sm:grid-cols-4">
                {budgets.slice(0, 8).map((budget, i) => {
                    const spent = toAmount(budget.total_transactions_amount);
                    const total = toAmount(budget.amount);
                    const color = categoryColor(budget.color);
                    const Icon = getCategoryIcon(budget.icon ?? '');

                    return (
                        <button
                            key={budget.id}
                            type="button"
                            onClick={() => onOpen(budget.id)}
                            className="grid justify-items-center gap-px rounded-2xl px-1 py-1.5 text-center transition-colors duration-150 hover:bg-secondary"
                        >
                            <Ring value={total > 0 ? (spent / total) * 100 : 0} color={color} index={i} className="mb-1.5">
                                <Icon size={20} weight="bold" style={{ color }} aria-hidden="true" />
                            </Ring>
                            <span className="max-w-full truncate text-[13px] font-semibold">{budget.name}</span>
                            <span className="text-xs text-muted-foreground font-rounded tabular">
                                {amount(spent)} / {amount(total)}
                            </span>
                            <span className="text-[11.5px]">{budgetStatusLine(budget.status, spent, total)}</span>
                        </button>
                    );
                })}
            </div>
        </Panel>
    );
}
