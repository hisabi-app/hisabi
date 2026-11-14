import { ChatCircleTextIcon, PencilSimpleIcon, PlusIcon } from '@phosphor-icons/react';

import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { requestAdd } from '@/hooks/useDataChanged';
import { cn } from '@/lib/utils';

// Add, in the page header on laptops; phones use the + in the dock instead
export default function AddButton({ className }: { className?: string }) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button className={cn('hidden md:inline-flex', className)}>
                    <PlusIcon weight="bold" aria-hidden="true" />
                    Add
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-[200px]">
                <DropdownMenuItem onSelect={() => requestAdd('sms')}>
                    <ChatCircleTextIcon aria-hidden="true" />
                    Paste a bank SMS
                </DropdownMenuItem>
                <DropdownMenuItem onSelect={() => requestAdd('manual')}>
                    <PencilSimpleIcon aria-hidden="true" />
                    Add by hand
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
