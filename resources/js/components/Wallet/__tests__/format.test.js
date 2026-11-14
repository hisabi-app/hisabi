import { amount, money, toAmount } from '../format';

describe('Wallet format helpers', () => {
    beforeEach(() => {
        window.AppCurrency = 'AED';
    });

    it('reads number_format()ed strings as numbers', () => {
        expect(toAmount('1,234.50')).toBe(1234.5);
        expect(toAmount(null)).toBe(0);
        expect(toAmount('not a number')).toBe(0);
    });

    it('formats whole amounts with thousands separators', () => {
        expect(amount(9486)).toBe('9,486');
        expect(amount('2,310')).toBe('2,310');
    });

    it('keeps the requested decimals', () => {
        expect(amount(312.4, 2)).toBe('312.40');
    });

    it('prefixes the app currency', () => {
        expect(money(9486)).toBe('AED 9,486');
        expect(money(64.25, 2)).toBe('AED 64.25');
    });
});
