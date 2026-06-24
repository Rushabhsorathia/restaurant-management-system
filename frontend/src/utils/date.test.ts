import { describe, expect, it } from 'vitest';
import { formatIsoDate } from './date';

describe('formatIsoDate', () => {
  it('formats a valid ISO string into a localized string', () => {
    const out = formatIsoDate('2026-01-01T00:00:00Z');
    expect(out).not.toBe('2026-01-01T00:00:00Z');
  });

  it('returns the input as-is when the date is invalid', () => {
    const out = formatIsoDate('not-a-date');
    expect(out).toBe('not-a-date');
  });
});
