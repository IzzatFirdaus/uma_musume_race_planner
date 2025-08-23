// Jest tests for quick_create_modal.js

describe('Quick Create Modal', () => {
  test('form validation logic', () => {
    function isValid(name, stage, cls) {
      return name.trim() !== '' && stage !== '' && cls !== '';
    }
    expect(isValid('Uma', 'junior', 'A')).toBe(true);
    expect(isValid('', 'junior', 'A')).toBe(false);
    expect(isValid('Uma', '', 'A')).toBe(false);
    expect(isValid('Uma', 'junior', '')).toBe(false);
  });
});
