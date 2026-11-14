import { smsSegments } from '../smsTokens';

describe('smsSegments', () => {
    const body = 'Purchase of AED 312.40 with Credit Card ending 4321 at CARREFOUR CITY CENTRE, DUBAI.';

    it('splits the SMS around the parts the parser found, in reading order', () => {
        const segments = smsSegments(body, { brand: 'CARREFOUR CITY CENTRE', amount: '312.40', card: 'Credit Card ending 4321' });

        expect(segments.filter((segment) => segment.label).map((segment) => [segment.label, segment.text])).toEqual([
            ['Amount', '312.40'],
            ['Card', 'Credit Card ending 4321'],
            ['Shop', 'CARREFOUR CITY CENTRE'],
        ]);
        expect(segments.map((segment) => segment.text).join('')).toBe(body);
    });

    it('ignores values that are missing from the text or overlap another part', () => {
        const segments = smsSegments(body, { amount: '312.40', brand: '312.40 with', date: '01/01/2026' });

        expect(segments.filter((segment) => segment.label)).toEqual([{ text: '312.40', label: 'Amount' }]);
    });

    it('returns the whole text when nothing was found', () => {
        expect(smsSegments('Hello', {})).toEqual([{ text: 'Hello' }]);
    });
});
