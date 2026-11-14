import { Link } from '@inertiajs/react';
import { ChatCircleTextIcon, PencilSimpleIcon, PlusIcon } from '@phosphor-icons/react';

import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Logo from '@/components/Wallet/Logo';
import { cn } from '@/lib/utils';
import AccountMenu, { type ShellUser } from './AccountMenu';
import { hasSeen, isCurrent, isVisible, MAIN_ITEMS, MANAGE_ITEMS, type Navigation, type NavItem } from './navigation';

import type { AddKind } from '@/hooks/useDataChanged';

interface SidebarProps {
    nav: Navigation;
    user?: ShellUser;
    onAdd: (kind: AddKind) => void;
}

function SidebarLink({ item, nav }: { item: NavItem; nav: Navigation }) {
    const current = isCurrent(item);
    const count = item.count ? nav.counts[item.count] : 0;
    // Budgets is the one section that unlocks while the user watches
    const isNew = item.key === 'budgets' && nav.stage === 'early' && !current && !hasSeen(item.key);
    const Icon = item.icon;

    return (
        <Link
            href={item.href}
            aria-current={current ? 'page' : undefined}
            className={cn(
                'flex items-center gap-2.5 rounded-[11px] px-2.5 py-2 text-[13.5px] font-medium transition-colors duration-150',
                current ? 'bg-secondary font-semibold' : 'hover:bg-secondary/70',
            )}
        >
            <Icon size={18} weight={current ? 'fill' : 'regular'} className={current ? 'text-foreground' : 'text-muted-foreground'} aria-hidden="true" />
            <span>{item.label}</span>
            {isNew && <span className="size-1.5 rounded-full bg-info" title="New" />}
            {count > 0 && (
                <span className={cn('ml-auto rounded-full px-1.5 text-[11.5px] font-bold tabular', current ? 'bg-card' : 'bg-secondary')} aria-label={`${count} waiting`}>
                    {count}
                </span>
            )}
        </Link>
    );
}

// The floating sidebar: a white card inset from the window edges, laptop and up
export default function Sidebar({ nav, user, onAdd }: SidebarProps) {
    return (
        <aside className="fixed inset-y-3 left-3 z-30 hidden w-[224px] flex-col rounded-2xl bg-card px-2.5 pt-3.5 pb-2 shadow-float md:flex" aria-label="Main">
            <Link href="/" className="mb-2.5 self-start rounded-lg px-1" aria-label="Home">
                <Logo className="-my-0.5 h-10" />
            </Link>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <button
                        type="button"
                        className="mb-3 flex h-[38px] items-center justify-center gap-1.5 rounded-xl bg-primary font-semibold text-primary-foreground transition-[background-color,transform] duration-150 ease-out-quint hover:bg-primary/85 active:scale-[0.98]"
                    >
                        <PlusIcon size={15} weight="bold" aria-hidden="true" />
                        Add
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" className="w-[var(--radix-dropdown-menu-trigger-width)]">
                    <DropdownMenuItem onSelect={() => onAdd('sms')}>
                        <ChatCircleTextIcon aria-hidden="true" />
                        Paste a bank SMS
                    </DropdownMenuItem>
                    <DropdownMenuItem onSelect={() => onAdd('manual')}>
                        <PencilSimpleIcon aria-hidden="true" />
                        Add by hand
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <nav className="grid gap-0.5">
                {MAIN_ITEMS.filter((item) => isVisible(item, nav)).map((item) => (
                    <SidebarLink key={item.key} item={item} nav={nav} />
                ))}
            </nav>

            <p className="px-2.5 pt-5 pb-1.5 text-[11.5px] font-semibold text-muted-foreground">Manage</p>
            <nav className="grid gap-0.5" aria-label="Manage">
                {MANAGE_ITEMS.map((item) => (
                    <SidebarLink key={item.key} item={item} nav={nav} />
                ))}
            </nav>

            {user && (
                <div className="mt-auto border-t pt-2">
                    <AccountMenu user={user} />
                </div>
            )}
        </aside>
    );
}
