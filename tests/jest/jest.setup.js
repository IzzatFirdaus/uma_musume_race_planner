const { TextEncoder, TextDecoder } = require("util");
global.TextEncoder = global.TextEncoder || TextEncoder;
global.TextDecoder = global.TextDecoder || TextDecoder;

const { JSDOM } = require("jsdom");

// Provide a consistent JSDOM window with a proper origin so localStorage works
const dom = new JSDOM("<!doctype html><html><body></body></html>", {
  url: "http://localhost",
});
global.window = dom.window;
global.document = dom.window.document;
global.localStorage = dom.window.localStorage;
// Provide minimal console shim if needed
if (!global.console) global.console = console;
