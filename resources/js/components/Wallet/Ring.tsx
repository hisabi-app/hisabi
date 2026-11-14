import { cn } from '@/lib/utils';

interface RingProps {
    // 0 to 100; anything above 100 draws a full ring
    value: number;
    color: string;
    // Pixels, or any CSS length (the kiosk sizes rings in em)
    size?: number | string;
    stroke?: number;
    // Position in a row of rings, so they draw one after another
    index?: number;
    className?: string;
    children?: React.ReactNode;
}

export default function Ring({ value, color, size = 60, stroke = 7, index = 0, className, children }: RingProps) {
    const percentage = Math.max(0, Math.min(100, value));
    const radius = 25;

    return (
        <span className={cn('relative grid shrink-0 place-items-center', className)} style={{ width: size, height: size }}>
            <svg viewBox="0 0 60 60" className="absolute inset-0 size-full" aria-hidden="true">
                <circle cx="30" cy="30" r={radius} fill="none" stroke="var(--track)" strokeWidth={stroke} />
                {percentage > 0 && (
                    <circle
                        className="ring-draw"
                        cx="30"
                        cy="30"
                        r={radius}
                        fill="none"
                        stroke={color}
                        strokeWidth={stroke}
                        strokeLinecap="round"
                        pathLength={100}
                        strokeDasharray={`${percentage} 100`}
                        transform="rotate(-90 30 30)"
                        style={{ '--ring-length': percentage, '--i': index, transition: 'stroke-dasharray 900ms var(--ease-out-quint)' } as React.CSSProperties}
                    />
                )}
            </svg>
            {children && <span className="relative grid place-items-center">{children}</span>}
        </span>
    );
}
