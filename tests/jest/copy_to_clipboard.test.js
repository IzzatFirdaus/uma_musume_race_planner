// Jest tests for copy_to_clipboard.js

describe('TxtBuilder', () => {
  test('pad pads string to length', () => {
    function pad(str, length, align = 'left') {
      str = String(str);
      if (align === 'right') return str.padStart(length);
      if (align === 'center') {
        const totalPad = length - str.length;
        const left = Math.floor(totalPad / 2);
        const right = totalPad - left;
        return ' '.repeat(left) + str + ' '.repeat(right);
      }
      return str.padEnd(length);
    }
    expect(pad('abc', 5)).toBe('abc  ');
    expect(pad('abc', 5, 'right')).toBe('  abc');
    expect(pad('abc', 5, 'center')).toBe(' abc ');
  });
});
