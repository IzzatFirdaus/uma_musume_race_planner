// Sample Jest test

describe('Sample Jest Test', () => {
  test('adds 2 + 2 to equal 4', () => {
    expect(2 + 2).toBe(4);
  });

  test('string contains substring', () => {
    expect('uma musume race planner').toContain('musume');
  });

  test('array includes value', () => {
    expect([1, 2, 3]).toContain(2);
  });

  test('object equality', () => {
    const obj1 = { a: 1, b: 2 };
    const obj2 = { a: 1, b: 2 };
    expect(obj1).toEqual(obj2);
  });

  test('throws error', () => {
    function throwError() {
      throw new Error('fail');
    }
    expect(throwError).toThrow('fail');
  });

  test('async resolves', async () => {
    await expect(Promise.resolve('done')).resolves.toBe('done');
  });

  test('async rejects', async () => {
    await expect(Promise.reject(new Error('fail'))).rejects.toThrow('fail');
  });

  test('edge case: empty array', () => {
    expect([]).toHaveLength(0);
  });

  test('edge case: null and undefined', () => {
    expect(null).toBeNull();
    expect(undefined).toBeUndefined();
  });
});
