import { useEffect, useRef, useState } from 'react';
import { CheckIcon } from '@phosphor-icons/react';

import Panel from '@/components/Wallet/Panel';
import { cn } from '@/lib/utils';

export interface NeedsYouItem {
    key: string;
    tile: React.ReactNode;
    title: string;
    detail: string;
    action: string;
    onAction: () => void;
}

type Rendered = NeedsYouItem & { leaving?: boolean };

const LEAVE_MS = 700;

// Keeps items that just went away on screen long enough to check them off and slide them out
function useLeavingItems(items: NeedsYouItem[]): Rendered[] {
    const [rendered, setRendered] = useState<Rendered[]>(items);
    const signature = items.map((item) => item.key).join('|');
    const latest = useRef(items);
    latest.current = items;

    useEffect(() => {
        const current = latest.current;
        const keys = new Set(current.map((item) => item.key));

        setRendered((previous) => {
            const next: Rendered[] = [...current];
            previous.forEach((item, index) => {
                if (!keys.has(item.key)) {
                    next.splice(Math.min(index, next.length), 0, { ...item, leaving: true });
                }
            });

            return next;
        });

        const timer = window.setTimeout(() => setRendered((previous) => previous.filter((item) => !item.leaving)), LEAVE_MS);

        return () => window.clearTimeout(timer);
    }, [signature]);

    // Keep the actions pointing at the latest data
    return rendered.map((item) => current(item, items));
}

const current = (item: Rendered, items: NeedsYouItem[]): Rendered => {
    const fresh = items.find((candidate) => candidate.key === item.key);

    return fresh ? { ...fresh } : item;
};

export default function NeedsYou({ items, index }: { items: NeedsYouItem[]; index: number }) {
    const rendered = useLeavingItems(items);

    // Nothing to do: leave the section out rather than say so
    if (rendered.length === 0) {
        return null;
    }

    // The last item is checking off: fold the panel away with it, and the gap below
    const clearing = items.length === 0;

    return (
        <div
            className={cn(
                'grid transition-[grid-template-rows,opacity,margin] duration-300 ease-out-quint',
                clearing ? '-mb-3.5 grid-rows-[0fr] opacity-0 delay-300' : 'grid-rows-[1fr]',
            )}
        >
            <div className={cn('min-h-0', clearing && 'overflow-hidden')}>
                <Panel title="Needs you" count={items.length} index={index}>
                    {rendered.map((item, i) => (
                        <div
                            key={item.key}
                            className={cn(
                                'grid transition-[grid-template-rows,opacity] duration-300 ease-out-quint',
                                item.leaving ? 'grid-rows-[0fr] opacity-0 delay-300' : 'grid-rows-[1fr]',
                            )}
                        >
                            <div className="min-h-0 overflow-hidden">
                                <div
                                    className={cn(
                                        'grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 py-2.5 transition-transform duration-300 ease-out-quint',
                                        i > 0 && 'border-t',
                                        item.leaving && 'translate-x-3.5 delay-300',
                                    )}
                                >
                                    <span className="row-span-2">{item.tile}</span>
                                    <p className="font-semibold">{item.title}</p>
                                    <p className="col-start-2 truncate text-[12.5px] text-muted-foreground font-rounded tabular">{item.detail}</p>
                                    <button
                                        type="button"
                                        onClick={item.onAction}
                                        disabled={item.leaving}
                                        className={cn(
                                            'col-start-3 row-span-2 row-start-1 inline-flex items-center gap-1 rounded-full px-3.5 py-1.5 text-[12.5px] font-semibold transition-[background-color,color,transform] duration-200 ease-out-quint active:scale-95',
                                            item.leaving ? 'bg-success text-white' : 'bg-secondary hover:bg-[oklch(0.94_0.003_286)]',
                                        )}
                                    >
                                        {item.leaving ? (
                                            <>
                                                <CheckIcon size={13} weight="bold" aria-hidden="true" />
                                                Done
                                            </>
                                        ) : (
                                            item.action
                                        )}
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </Panel>
            </div>
        </div>
    );
}
