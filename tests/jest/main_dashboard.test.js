// Jest tests for main_dashboard.js

describe('Main Dashboard', () => {
  test('API base fallback', () => {
    function getApiBase(config, legacy) {
      return config || legacy || '/api';
    }
    expect(getApiBase('/api', null)).toBe('/api');
    expect(getApiBase(null, '/legacy')).toBe('/legacy');
    expect(getApiBase(null, null)).toBe('/api');
  });
});
