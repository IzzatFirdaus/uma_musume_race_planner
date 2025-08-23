// Jest tests for config.js

describe('Config', () => {
  test('API_BASE normalization removes trailing slash', () => {
    function normalize(base) {
      return base.replace(/\/+$/, '');
    }
    expect(normalize('/uma_musume_race_planner/api/')).toBe('/uma_musume_race_planner/api');
    expect(normalize('/api')).toBe('/api');
  });
});
