import Ring from '@/components/Wallet/Ring';
import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount } from '@/components/Wallet/format';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import type { KioskBudget } from '@/Utils/kiosk';

// large: alone on an answer page, a few rings grow to fill it
export default function BudgetRingsCard({ budgets, large = false }: { budgets: KioskBudget[]; large?: boolean }) {
    // Up to six rings stay readable from across the room
    const shown = budgets.slice(0, 6);

    return (
        <section aria-label="Budgets" className="grid min-h-0 content-center rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            <div className="grid grid-cols-2 gap-x-[1em] gap-y-[1.3em]" style={large && shown.length <= 4 ? { fontSize: '1.45em' } : undefined}>
                {shown.map((budget, i) => {
                    const spent = budget.amount - budget.remaining;
                    const color = categoryColor(budget.color);
                    const Icon = getCategoryIcon(budget.icon ?? '');

                    return (
                        <div key={budget.id} className="grid grid-cols-[5em_minmax(0,1fr)] items-center gap-x-[0.9em] gap-y-[0.1em]">
                            <Ring
                                value={budget.amount > 0 ? (spent / budget.amount) * 100 : 0}
                                color={color}
                                size="5em"
                                index={i}
                                className="row-span-3"
                            >
                                <Icon size="1.7em" weight="bold" style={{ color }} aria-hidden="true" />
                            </Ring>
                            <p className="truncate text-[1.2em] font-semibold">{budget.name}</p>
                            <p className="text-[1.05em] whitespace-nowrap text-muted-foreground font-rounded tabular">
                                {budget.remaining >= 0 ? `${amount(budget.remaining)} left` : `${amount(spent)} of ${amount(budget.amount)}`}
                            </p>
                            <p
                                key={budget.status}
                                className="text-[0.95em] font-semibold"
                                style={{ animation: 'fade-in 400ms var(--ease-out-quint)' }}
                            >
                                {budget.status === 'over' && <span className="text-destructive">Over by {amount(-budget.remaining)}</span>}
                                {budget.status === 'ahead' && <span className="text-warning">Ahead of pace</span>}
                                {budget.status === 'used' && <span className="text-muted-foreground">Fully used</span>}
                                {budget.status === 'on_track' && <span className="font-medium text-muted-foreground">On track</span>}
                            </p>
                        </div>
                    );
                })}
            </div>
        </section>
    );
}
