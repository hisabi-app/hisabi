import { useEffect, useRef, useState } from 'react';

interface CountUpProps {
    value: number;
    format: (value: number) => string;
    duration?: number;
}

const prefersReducedMotion = () => typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

// Counts up to the value on first render, then rolls from the old value whenever it changes
export default function CountUp({ value, format, duration = 700 }: CountUpProps) {
    const [shown, setShown] = useState(prefersReducedMotion() ? value : 0);
    const from = useRef(prefersReducedMotion() ? value : 0);

    useEffect(() => {
        const start = from.current;
        from.current = value;

        if (start === value || prefersReducedMotion()) {
            setShown(value);
            return;
        }

        let frame = 0;
        let startedAt: number | null = null;
        const step = (now: number) => {
            if (startedAt === null) startedAt = now;
            const progress = Math.min(1, (now - startedAt) / duration);
            // ease-out-expo
            const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            setShown(start + (value - start) * eased);
            if (progress < 1) frame = requestAnimationFrame(step);
        };
        frame = requestAnimationFrame(step);
        // Animation frames pause in background tabs; make sure the final value lands
        const settle = window.setTimeout(() => setShown(value), duration + 80);

        return () => {
            cancelAnimationFrame(frame);
            window.clearTimeout(settle);
        };
    }, [value, duration]);

    return <span className="tabular">{format(shown)}</span>;
}
