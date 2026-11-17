import { categoryColor } from '@/components/Wallet/categoryColor';
import { amount } from '@/components/Wallet/format';
import { useSize } from '@/hooks/useSize';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import { categoryLayout } from '@/Utils/kiosk';

import type { BarRow } from './types';

// Ranked rows (categories, or brands in their category's color) with bars against the biggest
export default function CategoryBarsCard({ label, rows }: { label: string; rows: BarRow[] }) {
    const [listRef, listBox] = useSize<HTMLDivElement>();
    const { columns, rows: perColumn, scale } = categoryLayout(rows.length, listBox.height / (listBox.em || 1), listBox.width / (listBox.em || 1));
    const top = rows[0]?.total || 1;

    return (
        <section aria-label={label} className="flex min-h-0 flex-1 flex-col rounded-[1.6em] bg-card px-[1.6em] py-[1.4em] shadow-card">
            <p className="text-[1.1em] font-medium text-muted-foreground">{label}</p>
            <div ref={listRef} className="mt-[0.9em] min-h-0 flex-1 overflow-hidden">
                {/* Biggest first, down each column in turn; the 2.3em rows and 0.9em gaps are what categoryLayout scales */}
                <div
                    className="grid grid-flow-col gap-x-[2.4em] gap-y-[0.9em]"
                    style={{
                        fontSize: `${scale}em`,
                        gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                        gridTemplateRows: `repeat(${perColumn}, auto)`,
                    }}
                >
                    {rows.map((row, j) => {
                        const Icon = getCategoryIcon(row.icon ?? '');

                        return (
                            <div
                                key={row.id ?? row.name}
                                className="grid grid-cols-[2.3em_minmax(0,1fr)_auto] items-center gap-x-[0.8em] gap-y-[0.3em] leading-none"
                            >
                                <span
                                    className="row-span-2 grid size-[2.3em] place-items-center rounded-[0.65em] text-white"
                                    style={{ backgroundColor: categoryColor(row.color) }}
                                >
                                    <Icon size="1.3em" weight="bold" aria-hidden="true" />
                                </span>
                                <span className="truncate text-[1.3em] font-semibold">{row.name}</span>
                                <span className="text-[1.3em] font-semibold font-rounded tabular">{amount(row.total)}</span>
                                <span className="relative col-span-2 col-start-2 h-[0.45em] overflow-hidden rounded-full bg-track">
                                    <i
                                        className="grow-x absolute inset-y-0 left-0 rounded-full transition-[width] duration-700 ease-out-quint"
                                        style={
                                            {
                                                width: `${(row.total / top) * 100}%`,
                                                background: categoryColor(row.color),
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
