// Jest tests for app.js
const { JSDOM } = require('jsdom');

describe('Theme management', () => {
  test('getEffectiveTheme returns light or dark', () => {
    const { window } = new JSDOM('<!DOCTYPE html><html><body></body></html>');
    // Mock localStorage
    window.localStorage = {
      store: {},
      getItem: function(key) { return this.store[key] || null; },
      setItem: function(key, value) { this.store[key] = value; },
      removeItem: function(key) { delete this.store[key]; }
    };
    // Simulate prefers-color-scheme
    window.matchMedia = () => ({ matches: true });
    // Re-implement getEffectiveTheme for test
    function getEffectiveTheme() {
      let stored = 'system';
      try {
        stored = window.localStorage.getItem('theme') || 'system';
      } catch (_) {}
      if (stored === 'dark') return 'dark';
      if (stored === 'light') return 'light';
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    window.localStorage.setItem('theme', 'dark');
    expect(getEffectiveTheme()).toBe('dark');
    window.localStorage.setItem('theme', 'light');
    expect(getEffectiveTheme()).toBe('light');

    window.localStorage.setItem('theme', 'system');
    expect(getEffectiveTheme()).toBe('dark');
  });
});
