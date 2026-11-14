import { formatNumber, cutString, escapeHtml } from '../';

it('formatNumber', () => {
  expect(formatNumber(2000)).toBe('2k')
  expect(formatNumber(-2000)).toBe('(2k)')
  expect(formatNumber(-1530)).toBe('(1.530k)')
  expect(formatNumber(-1530, '0[.]00a')).toBe('-1.53k')
});

it('cutString', () => {
    expect(cutString('saleem', 2)).toBe('sa...')
    expect(cutString('saleem', 6)).toBe('saleem')
});

it('escapeHtml', () => {
    expect(escapeHtml('<img src=x onerror="alert(1)">')).toBe('&lt;img src=x onerror=&quot;alert(1)&quot;&gt;')
    expect(escapeHtml("Ben & Jerry's")).toBe('Ben &amp; Jerry&#39;s')
    expect(escapeHtml(null)).toBe('')
});
