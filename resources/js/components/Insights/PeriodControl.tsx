import { CaretLeftIcon, CaretRightIcon } from '@phosphor-icons/react';

import { cn } from '@/lib/utils';
import { isCurrent, PERIOD_KINDS, shiftPeriod, type Period } from './period';

export default function PeriodControl({ period, onChange }: { period: Period; onChange: (period: Period) => void }) {
    const canStep = period.kind !== 'all';

    return (
        <div className="flex items-center gap-2">
            {canStep && (
                <div className="flex gap-1.5">
                    <button
                        type="button"
                        onClick={() => onChange(shiftPeriod(period, -1))}
                        className="grid size-8 place-items-center rounded-full bg-card text-muted-foreground shadow-card ring-1 ring-border ring-inset hover:text-foreground"
                        aria-label="Previous period"
                    >
                        <CaretLeftIcon size={14} weight="bold" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        onClick={() => onChange(shiftPeriod(period, 1))}
                        disabled={isCurrent(period)}
                        className="grid size-8 place-items-center rounded-full bg-card text-muted-foreground shadow-card ring-1 ring-border ring-inset hover:text-foreground disabled:opacity-40"
                        aria-label="Next period"
                    >
                        <CaretRightIcon size={14} weight="bold" aria-hidden="true" />
                    </button>
                </div>
            )}
            <div role="group" aria-label="Period" className="inline-flex rounded-full bg-card p-[3px] shadow-card ring-1 ring-border ring-inset">
                {PERIOD_KINDS.map(({ kind, label }) => (
                    <button
                        key={kind}
                        type="button"
                        aria-pressed={period.kind === kind}
                        onClick={() => onChange({ kind, anchor: new Date() })}
                        className={cn(
                            'rounded-full px-3 py-1 text-[12.5px] font-semibold transition-colors duration-200 ease-out-quint',
                            period.kind === kind ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground',
                        )}
                    >
                        {label}
                    </button>
                ))}
            </div>
        </div>
    );
}
