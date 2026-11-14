import { cn } from '@/lib/utils';

interface PageHeaderProps {
    eyebrow?: string;
    title: React.ReactNode;
    actions?: React.ReactNode;
    className?: string;
}

export default function PageHeader({ eyebrow, title, actions, className }: PageHeaderProps) {
    return (
        <div className={cn('rise flex flex-wrap items-end justify-between gap-3', className)}>
            <div className="min-w-0">
                {eyebrow && <p className="text-[13px] font-medium text-muted-foreground">{eyebrow}</p>}
                <h1 className="text-[28px] leading-tight font-bold tracking-[-0.028em] md:text-[30px]">{title}</h1>
            </div>
            {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
