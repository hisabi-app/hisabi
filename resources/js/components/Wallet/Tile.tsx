import { cn } from '@/lib/utils';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import { categoryColor } from './categoryColor';

interface TileProps {
    // A category's stored color (red, blue, green, orange, purple, pink, indigo, gray)
    color?: string | null;
    icon?: string | null;
    size?: 'sm' | 'md' | 'lg';
    className?: string;
    label?: string;
}

const SIZES = {
    sm: { box: 'size-7 rounded-lg', icon: 15 },
    md: { box: 'size-9 rounded-[10px]', icon: 18 },
    lg: { box: 'size-11 rounded-xl', icon: 22 },
};

// A category's icon on its color. Without a category it's a quiet "?" tile.
export default function Tile({ color, icon, size = 'md', className, label }: TileProps) {
    const { box, icon: iconSize } = SIZES[size];

    if (!color && !icon) {
        return (
            <span className={cn('grid shrink-0 place-items-center bg-muted font-bold text-muted-foreground ring-1 ring-border ring-inset', box, className)} aria-label={label}>
                ?
            </span>
        );
    }

    const Icon = getCategoryIcon(icon ?? '');

    return (
        <span
            className={cn('grid shrink-0 place-items-center text-white transition-[background-color,transform] duration-300 ease-out-quint', box, className)}
            style={{ backgroundColor: categoryColor(color) }}
            aria-label={label}
        >
            <Icon size={iconSize} weight="bold" aria-hidden="true" />
        </span>
    );
}
