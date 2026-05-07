const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// cPanel/WAMP dev machines can block Metro's child worker spawns.
// Keep bundling in-process so Expo Go does not hang on "Opening project".
config.maxWorkers = 1;

module.exports = config;
