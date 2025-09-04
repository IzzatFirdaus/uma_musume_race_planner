module.exports = {
  testEnvironment: "jsdom",
  testPathIgnorePatterns: ["/node_modules/", "/scripts/"],
  setupFilesAfterEnv: ["./tests/jest/jest.setup.js"],
  verbose: true,
};
