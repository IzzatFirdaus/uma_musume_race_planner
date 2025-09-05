// Setup file for Jest to mock localStorage
require('jest-localstorage-mock');
global.TextEncoder = require('util').TextEncoder;
global.TextDecoder = require('util').TextDecoder;
