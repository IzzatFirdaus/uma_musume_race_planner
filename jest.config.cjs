module.exports = {
  testEnvironment: "jsdom",
  testPathIgnorePatterns: ["/node_modules/", "/scripts/"],
  setupFilesAfterEnv: ["./jest.setup.js"],
  verbose: true,
};
