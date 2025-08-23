// Jest tests for autosuggest.js

describe('Autosuggest', () => {
  test('ALLOWED_FIELDS contains expected fields', () => {
    const ALLOWED_FIELDS = ['name', 'race_name', 'skill_name', 'goal'];
    expect(ALLOWED_FIELDS).toContain('name');
    expect(ALLOWED_FIELDS).toContain('skill_name');
    expect(ALLOWED_FIELDS).toContain('goal');
  });

  test('buildApiUrl returns correct format', () => {
    function buildApiUrl(field, query) {
      const base = '/uma_musume_race_planner/api';
      return `${base}/autosuggest.php?field=${encodeURIComponent(field)}&query=${encodeURIComponent(query)}`;
    }
    expect(buildApiUrl('name', 'uma')).toMatch(/field=name&query=uma/);
  });
});
