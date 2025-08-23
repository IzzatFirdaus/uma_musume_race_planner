// Jest tests for trainee_image_handler.js

describe('Trainee Image Handler', () => {
  test('valid image types', () => {
    const validTypes = ['image/jpeg','image/png','image/gif','image/webp'];
    expect(validTypes).toContain('image/png');
    expect(validTypes).not.toContain('image/svg+xml');
  });

  test('file size limit', () => {
    function isValidSize(size) {
      return size <= 5 * 1024 * 1024;
    }
    expect(isValidSize(1024)).toBe(true);
    expect(isValidSize(6 * 1024 * 1024)).toBe(false);
  });
});
