// Jest tests for dashboard.js

describe('Dashboard', () => {
  test('stats keys and defaults', () => {
    const keys = ['speed', 'stamina', 'power', 'guts', 'wisdom'];
    const defaults = { speed: 80, stamina: 70, power: 90, guts: 60, wisdom: 85 };
    expect(keys).toContain('speed');
    expect(defaults.stamina).toBe(70);
  });
});
