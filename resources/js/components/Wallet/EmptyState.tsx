import { cn } from '@/lib/utils';

interface EmptyStateProps {
    title: string;
    body?: React.ReactNode;
    action?: React.ReactNode;
    className?: string;
}

// What a list shows before it has anything in it: one line on what goes here, and how to start
export default function EmptyState({ title, body, action, className }: EmptyStateProps) {
    return (
        <div className={cn('grid justify-items-center gap-2 px-6 py-10 text-center', className)}>
            <p className="font-semibold">{title}</p>
            {body && <p className="max-w-sm text-sm text-muted-foreground">{body}</p>}
            {action && <div className="mt-2">{action}</div>}
        </div>
    );
}
