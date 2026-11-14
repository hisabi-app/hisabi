export interface SmsSegment {
    text: string;
    label?: string;
}

const LABELS: Record<string, string> = {
    amount: 'Amount',
    card: 'Card',
    account: 'Account',
    brand: 'Shop',
    date: 'Date',
    time: 'Time',
};

// Splits an SMS into plain text and the parts the parser picked out, in reading order
export const smsSegments = (body: string, data: Record<string, string> = {}): SmsSegment[] => {
    const found: { start: number; end: number; label: string }[] = [];

    Object.entries(LABELS).forEach(([key, label]) => {
        const value = data[key];
        if (!value) return;

        const start = body.indexOf(value);
        if (start === -1) return;

        const end = start + value.length;
        // Skip anything that overlaps a part already picked out
        if (found.some((part) => start < part.end && end > part.start)) return;

        found.push({ start, end, label });
    });

    found.sort((a, b) => a.start - b.start);

    const segments: SmsSegment[] = [];
    let cursor = 0;
    found.forEach((part) => {
        if (part.start > cursor) segments.push({ text: body.slice(cursor, part.start) });
        segments.push({ text: body.slice(part.start, part.end), label: part.label });
        cursor = part.end;
    });
    if (cursor < body.length) segments.push({ text: body.slice(cursor) });

    return segments;
};
