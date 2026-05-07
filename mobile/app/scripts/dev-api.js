const { spawn } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

const repoRoot = path.resolve(__dirname, '..', '..', '..');
const laravelRoot = path.join(repoRoot, 'laravel-app');
const defaultWampPhp = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe';
const phpBin = process.env.NAIJABUILDERS_PHP ||
  (fs.existsSync(defaultWampPhp) ? defaultWampPhp : 'php');
const port = process.env.NAIJABUILDERS_API_PORT || '8080';

function getLanIpv4Address() {
  const interfaces = os.networkInterfaces();

  for (const entries of Object.values(interfaces)) {
    for (const entry of entries || []) {
      if (
        entry.family === 'IPv4' &&
        !entry.internal &&
        !entry.address.startsWith('169.254.')
      ) {
        return entry.address;
      }
    }
  }

  return '127.0.0.1';
}

const lanIpAddress = getLanIpv4Address();

console.log('Starting NaijaBuilders Laravel API for development...');
console.log(`Local: http://127.0.0.1:${port}/api/mobile`);
console.log(`Phone: http://${lanIpAddress}:${port}/api/mobile`);
console.log('Keep this terminal open while testing the mobile app.');

const child = spawn(
  phpBin,
  ['artisan', 'serve', '--host=0.0.0.0', `--port=${port}`],
  {
    cwd: laravelRoot,
    env: process.env,
    stdio: 'inherit',
  }
);

child.on('exit', (code, signal) => {
  if (signal) {
    process.kill(process.pid, signal);
    return;
  }

  process.exit(code ?? 0);
});
