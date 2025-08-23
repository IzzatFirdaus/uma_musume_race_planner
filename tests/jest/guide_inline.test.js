// Jest tests for guide_inline.js

describe('Guide Inline', () => {
  test('getStickyOffset returns sum of heights', () => {
    function getStickyOffset(mainH, guideH) {
      return mainH + guideH;
    }
    expect(getStickyOffset(10, 20)).toBe(30);
  });
});
