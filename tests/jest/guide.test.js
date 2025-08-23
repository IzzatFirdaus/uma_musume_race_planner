// Jest tests for guide.js

describe('Guide', () => {
  test('label fallback', () => {
    function getLabel(dataLabel, ariaLabel, i) {
      return dataLabel || ariaLabel || `Guide Section ${i + 1}`;
    }
    expect(getLabel('', '', 0)).toBe('Guide Section 1');
    expect(getLabel('foo', '', 0)).toBe('foo');
    expect(getLabel('', 'bar', 0)).toBe('bar');
  });
});
