import { Link } from '@inertiajs/react';
import { CaretRightIcon } from '@phosphor-icons/react';

import { cn } from '@/lib/utils';

interface PanelProps extends Omit<React.HTMLAttributes<HTMLElement>, 'title'> {
    title?: React.ReactNode;
    count?: number;
    // A "See all" style link in the corner
    more?: { label: string; href?: string; onClick?: () => void };
    index?: number;
    children: React.ReactNode;
}

// A white card with an optional title row; the building block of every page
export default function Panel({ title, count, more, index, className, children, style, ...props }: PanelProps) {
    const moreContent = more && (
        <>
            {more.label}
            <CaretRightIcon size={13} weight="bold" aria-hidden="true" />
        </>
    );

    return (
        <section
            className={cn('min-w-0 rounded-2xl bg-card px-4 py-4 shadow-card sm:px-[18px]', index !== undefined && 'rise', className)}
            style={index !== undefined ? ({ '--i': index, ...style } as React.CSSProperties) : style}
            {...props}
        >
            {(title || more) && (
                <div className="mb-1.5 flex items-center gap-2">
                    {title && <h2 className="text-base font-bold tracking-[-0.018em]">{title}</h2>}
                    {count !== undefined && count > 0 && (
                        <span className="pop inline-grid h-5 min-w-5 place-items-center rounded-full bg-primary px-1.5 text-[11.5px] font-bold text-primary-foreground" key={count}>
                            {count}
                        </span>
                    )}
                    {more?.href && (
                        <Link href={more.href} className="ml-auto inline-flex items-center gap-0.5 text-[13px] text-muted-foreground hover:text-foreground">
                            {moreContent}
                        </Link>
                    )}
                    {!more?.href && more?.onClick && (
                        <button type="button" onClick={more.onClick} className="ml-auto inline-flex items-center gap-0.5 text-[13px] text-muted-foreground hover:text-foreground">
                            {moreContent}
                        </button>
                    )}
                </div>
            )}
            {children}
        </section>
    );
}
