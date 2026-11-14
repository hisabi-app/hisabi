import { Link } from '@inertiajs/react';
import { CaretUpDownIcon, GearSixIcon, MonitorIcon, SignOutIcon } from '@phosphor-icons/react';

import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

export interface ShellUser {
    name: string;
    email: string;
}

export const initials = (name: string) => {
    const parts = name.trim().split(/\s+/);

    return (parts.length > 1 ? parts[0][0] + parts[parts.length - 1][0] : name.slice(0, 2)).toUpperCase();
};

export default function AccountMenu({ user }: { user: ShellUser }) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button
                    type="button"
                    className="flex w-full items-center gap-2.5 rounded-[11px] px-2 py-2 text-left transition-colors duration-150 hover:bg-secondary data-[state=open]:bg-secondary"
                >
                    <span className="grid size-7 shrink-0 place-items-center rounded-full bg-secondary text-[11px] font-bold ring-1 ring-border ring-inset">
                        {initials(user.name)}
                    </span>
                    <span className="min-w-0 flex-1 truncate text-[13px] font-medium">{user.name}</span>
                    <CaretUpDownIcon size={14} className="text-muted-foreground" aria-hidden="true" />
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent side="top" align="start" className="w-[200px]">
                <DropdownMenuItem asChild>
                    <Link href="/settings">
                        <GearSixIcon aria-hidden="true" />
                        Settings
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href="/kiosk">
                        <MonitorIcon aria-hidden="true" />
                        Kiosk
                    </a>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href="/logout" method="post" as="button" className="w-full">
                        <SignOutIcon aria-hidden="true" />
                        Log out
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
