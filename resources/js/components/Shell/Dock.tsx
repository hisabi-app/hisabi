import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { ChatCircleTextIcon, DotsThreeIcon, MonitorIcon, PencilSimpleIcon, PlusIcon, SignOutIcon } from '@phosphor-icons/react';

import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import type { AddKind } from '@/hooks/useDataChanged';
import { isCurrent, isVisible, MAIN_ITEMS, MANAGE_ITEMS, type Navigation, type NavItem } from './navigation';

const SHORT_LABELS: Record<string, string> = { transactions: 'Activity' };

function DockLink({ item, nav }: { item: NavItem; nav: Navigation }) {
    const current = isCurrent(item);
    const count = item.count ? nav.counts[item.count] : 0;
    const Icon = item.icon;

    return (
        <Link
            href={item.href}
            aria-current={current ? 'page' : undefined}
            className={cn(
                'relative grid min-w-0 flex-1 justify-items-center gap-0.5 rounded-full px-1 py-1.5 text-[10px] font-semibold transition-colors duration-150',
                current ? 'bg-secondary text-foreground' : 'text-muted-foreground',
            )}
        >
            <Icon size={20} weight={current ? 'fill' : 'regular'} aria-hidden="true" />
            <span className="truncate">{SHORT_LABELS[item.key] ?? item.label}</span>
            {count > 0 && (
                <span className="absolute top-0.5 right-[18%] grid h-4 min-w-4 place-items-center rounded-full bg-primary px-1 text-[9.5px] font-bold text-primary-foreground tabular">
                    {count}
                </span>
            )}
        </Link>
    );
}

function SheetLink({ item, nav, onNavigate }: { item: NavItem; nav: Navigation; onNavigate: () => void }) {
    const count = item.count ? nav.counts[item.count] : 0;
    const Icon = item.icon;

    return (
        <Link href={item.href} onClick={onNavigate} className="flex items-center gap-3 rounded-xl px-3 py-3 font-medium hover:bg-secondary">
            <Icon size={20} className="text-muted-foreground" aria-hidden="true" />
            {item.label}
            {count > 0 && <span className="ml-auto rounded-full bg-secondary px-2 text-xs font-bold tabular">{count}</span>}
        </Link>
    );
}

// The phone navigation: a floating dock with Add in the middle
export default function Dock({ nav, onAdd }: { nav: Navigation; onAdd: (kind: AddKind) => void }) {
    const [sheet, setSheet] = useState<'add' | 'more' | null>(null);
    const main = MAIN_ITEMS.filter((item) => isVisible(item, nav));
    const [home, transactions, ...rest] = main;
    const fourth = rest.find((item) => item.key === 'budgets');
    const inMore = [...rest.filter((item) => item !== fourth), ...MANAGE_ITEMS];

    const add = (kind: AddKind) => {
        setSheet(null);
        onAdd(kind);
    };

    return (
        <>
            <nav
                className="fixed inset-x-3 bottom-[calc(0.75rem+env(safe-area-inset-bottom))] z-30 flex items-center gap-1 rounded-full bg-card p-1.5 shadow-float md:hidden"
                aria-label="Main"
            >
                <DockLink item={home} nav={nav} />
                <DockLink item={transactions} nav={nav} />
                <button
                    type="button"
                    onClick={() => setSheet('add')}
                    className="mx-1 grid size-11 shrink-0 place-items-center rounded-full bg-brand text-brand-foreground transition-transform duration-150 ease-out-quint active:scale-95"
                    aria-label="Add"
                >
                    <PlusIcon size={20} weight="bold" aria-hidden="true" />
                </button>
                {fourth ? <DockLink item={fourth} nav={nav} /> : <span className="flex-1" />}
                <button
                    type="button"
                    onClick={() => setSheet('more')}
                    className="relative grid min-w-0 flex-1 justify-items-center gap-0.5 rounded-full px-1 py-1.5 text-[10px] font-semibold text-muted-foreground"
                >
                    <DotsThreeIcon size={20} weight="bold" aria-hidden="true" />
                    More
                    {nav.counts.brands > 0 && <span className="absolute top-1 right-[26%] size-2 rounded-full bg-primary" aria-label="Something is waiting" />}
                </button>
            </nav>

            <Sheet open={sheet !== null} onOpenChange={(open) => !open && setSheet(null)}>
                <SheetContent side="bottom" className="gap-1 px-3 pt-5 pb-6">
                    {sheet === 'add' && (
                        <>
                            <SheetTitle className="px-3 pb-2 text-lg font-bold">Add</SheetTitle>
                            <button type="button" onClick={() => add('sms')} className="flex items-center gap-3 rounded-xl px-3 py-3 text-left font-medium hover:bg-secondary">
                                <ChatCircleTextIcon size={20} className="text-muted-foreground" aria-hidden="true" />
                                Paste a bank SMS
                            </button>
                            <button type="button" onClick={() => add('manual')} className="flex items-center gap-3 rounded-xl px-3 py-3 text-left font-medium hover:bg-secondary">
                                <PencilSimpleIcon size={20} className="text-muted-foreground" aria-hidden="true" />
                                Add by hand
                            </button>
                        </>
                    )}
                    {sheet === 'more' && (
                        <>
                            <SheetTitle className="px-3 pb-2 text-lg font-bold">More</SheetTitle>
                            {inMore.map((item) => (
                                <SheetLink key={item.key} item={item} nav={nav} onNavigate={() => setSheet(null)} />
                            ))}
                            <a href="/kiosk" className="flex items-center gap-3 rounded-xl px-3 py-3 font-medium hover:bg-secondary">
                                <MonitorIcon size={20} className="text-muted-foreground" aria-hidden="true" />
                                Kiosk
                            </a>
                            <Link href="/logout" method="post" as="button" className="flex items-center gap-3 rounded-xl px-3 py-3 text-left font-medium hover:bg-secondary">
                                <SignOutIcon size={20} className="text-muted-foreground" aria-hidden="true" />
                                Log out
                            </Link>
                        </>
                    )}
                </SheetContent>
            </Sheet>
        </>
    );
}
