import { useEffect, useRef, useState } from 'react';

// Which of a chart's `count` points is under the pointer. `pick` turns an x in the SVG's viewBox units into a point's index.
// A mouse shows it while hovering; a tap (or a sideways drag) keeps it up until the next tap somewhere else.
export function useChartHover(width: number, count: number, pick: (x: number) => number) {
    const ref = useRef<SVGSVGElement>(null);
    const [index, setIndex] = useState<number | null>(null);
    const active = index !== null;

    useEffect(() => {
        if (!active) return;

        const clear = (event: PointerEvent) => {
            if (!ref.current?.contains(event.target as Node)) setIndex(null);
        };
        document.addEventListener('pointerdown', clear);

        return () => document.removeEventListener('pointerdown', clear);
    }, [active]);

    const update = (event: React.PointerEvent<SVGSVGElement>) => {
        const box = event.currentTarget.getBoundingClientRect();
        if (box.width > 0) setIndex(pick(((event.clientX - box.left) / box.width) * width));
    };

    return {
        // The data can shrink under a held index when it reloads
        index: index !== null && index < count ? index : null,
        svgProps: {
            ref,
            style: { touchAction: 'pan-y' } as React.CSSProperties,
            onPointerMove: update,
            onPointerDown: update,
            onPointerLeave: (event: React.PointerEvent<SVGSVGElement>) => event.pointerType !== 'touch' && setIndex(null),
        },
    };
}

// Nearest index along an axis from `left` to `right` holding `count` evenly spaced points
export const nearest = (x: number, left: number, right: number, count: number) =>
    Math.min(count - 1, Math.max(0, Math.round(((x - left) / (right - left)) * (count - 1))));

// A small ink label above a point of a chart, with (x, y) in the chart's viewBox units.
// The chart's wrapper must be `relative` and sized by the SVG.
interface ChartTipProps {
    x: number;
    y: number;
    // The chart's viewBox size
    width: number;
    height: number;
    children: React.ReactNode;
}

export default function ChartTip({ x, y, width, height, children }: ChartTipProps) {
    const position = x / width;
    // Centered, sliding to one side near the edges so it stays inside the card
    const shift = position < 0.2 ? position / 0.4 : position > 0.8 ? 1 - (1 - position) / 0.4 : 0.5;

    return (
        <div
            className="pointer-events-none absolute z-10 grid gap-0.5 rounded-lg bg-foreground px-2.5 py-1.5 text-xs whitespace-nowrap text-background shadow-float animate-[fade-in_150ms_var(--ease-out-quint)] transition-[left,top,transform] duration-150 ease-out-quint motion-reduce:transition-none"
            style={{ left: `${position * 100}%`, top: `${(y / height) * 100}%`, transform: `translate(${-shift * 100}%, calc(-100% - 10px))` }}
        >
            {children}
        </div>
    );
}
