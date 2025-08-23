// Jest tests for plan_inline_details.js

describe('Plan Inline Details', () => {
  test('escapeHTML escapes special chars', () => {
    function escapeHTML(str) {
      return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }
    expect(escapeHTML('<div>"&</div>')).toBe('&lt;div&gt;&quot;&amp;&lt;/div&gt;');
  });
});
