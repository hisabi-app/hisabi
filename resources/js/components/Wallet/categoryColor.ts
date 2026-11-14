// The palette has eight category colors; older or seeded categories may use other Tailwind names
const ALIASES: Record<string, string> = {
    amber: 'orange',
    yellow: 'orange',
    lime: 'green',
    emerald: 'green',
    teal: 'green',
    cyan: 'blue',
    sky: 'blue',
    violet: 'purple',
    fuchsia: 'purple',
    rose: 'red',
    slate: 'gray',
    zinc: 'gray',
    neutral: 'gray',
    stone: 'gray',
};

const PALETTE = ['red', 'blue', 'green', 'orange', 'purple', 'pink', 'indigo', 'gray'];

// "emerald" → "var(--cat-green)"; unknown or missing colors fall back to gray
export const categoryColor = (color?: string | null): string => {
    const name = color ? (ALIASES[color] ?? color) : 'gray';

    return `var(--cat-${PALETTE.includes(name) ? name : 'gray'})`;
};
