import { formatNumber, cutString } from '../';

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