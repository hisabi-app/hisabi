import { amount } from '@/components/Wallet/format';
import { trendDomain, trendLabelIndices } from '@/Utils/kiosk';

import type { TrendPoint } from './types';

interface TrendChartProps {
    points: TrendPoint[];
    // A balance's month ends rather than totals per period: the line hugs its values instead of starting from zero
    running: boolean;
    label: string;
    // Height of the drawing against its width of 340
    height: number;
}

// How a total moved over time, as a thin ink line: the periods along the bottom, the latest value beside its end
// (where the line never runs) and the highest one above where it peaked
export default function TrendChart({ points, running, label, height }: TrendChartProps) {
    const W = 340;
    const H = height;
    const L = 8;
    // Room on the right for the latest value
    const R = W - 40;
    const T = 16;
    const B = H - 16;
    const values = points.map((point) => point.value);
    const [low, high] = trendDomain(values, running);
    const x = (i: number) => L + (points.length > 1 ? (i / (points.length - 1)) * (R - L) : (R - L) / 2);
    const y = (value: number) => B - ((value - low) / (high - low)) * (B - T);
    const path = values.map((value, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)} ${y(value).toFixed(1)}`).join(' ');
    const last = points.length - 1;
    const peak = values.indexOf(Math.max(...values));
    // Labels sit centred on their point, starting from it at the left edge
    const anchor = (i: number) => (x(i) < L + 24 ? 'start' : 'middle');

    return (
        <svg viewBox={`0 0 ${W} ${H}`} className="block h-auto w-full overflow-visible" role="img" aria-label={label}>
            {/* From zero, the baseline is zero; for a balance it's only the floor of the drawing */}
            <line x1={L} x2={R} y1={running ? B : y(0)} y2={running ? B : y(0)} stroke="var(--border)" />
            <path
                className="line-draw"
                pathLength={1}
                d={path}
                fill="none"
                stroke="var(--foreground)"
                strokeWidth="2.5"
                strokeLinejoin="round"
                strokeLinecap="round"
            />
            <g className="fade-in-late">
                {points.length <= 12 &&
                    points
                        .slice(0, -1)
                        .map((point, i) => (
                            <circle key={i} cx={x(i)} cy={y(point.value)} r="2.4" fill="var(--card)" stroke="var(--foreground)" strokeWidth="1.5" />
                        ))}
                <circle cx={x(last)} cy={y(values[last])} r="3.5" fill="var(--foreground)" stroke="var(--card)" strokeWidth="1.5" />
                <text x={x(last) + 7} y={y(values[last]) + 3.5} fontSize="9.5" fontWeight="700" fill="var(--foreground)" className="font-rounded">
                    {amount(values[last])}
                </text>
                {peak !== last && (
                    <text
                        x={x(peak)}
                        y={y(values[peak]) - 7}
                        textAnchor={anchor(peak)}
                        fontSize="8.5"
                        fontWeight="600"
                        fill="var(--muted-foreground)"
                        className="font-rounded"
                    >
                        {amount(values[peak])}
                    </text>
                )}
                {trendLabelIndices(points.length).map((i) => (
                    <text key={i} x={x(i)} y={H - 3} textAnchor={anchor(i)} fontSize="8.5" fill="var(--muted-foreground)">
                        {points[i].label}
                    </text>
                ))}
            </g>
        </svg>
    );
}
