// Jest tests for plan_details_modal.js

describe('Plan Details Modal', () => {
  test('API endpoint construction', () => {
    function endpoint(base, type, id) {
      return `${base}/plan_section.php?type=${type}&id=${id}`;
    }
    expect(endpoint('/api', 'attributes', 1)).toBe('/api/plan_section.php?type=attributes&id=1');
  });
});
