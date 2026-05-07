const { execFileSync, spawn } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

const projectRoot = path.resolve(__dirname, '..');
const expoCli = require.resolve('expo/bin/cli');
const args = process.argv.slice(2);
const expoHome = path.join(projectRoot, '.expo-home');

fs.mkdirSync(expoHome, { recursive: true });

function getPidsForPorts(ports) {
  if (process.platform !== 'win32') {
    return [];
  }

  let output = '';
  try {
    output = execFileSync('netstat.exe', ['-ano'], { encoding: 'utf8' });
  } catch {
    return [];
  }

  const pids = new Set();
  const portPattern = new RegExp(`:(${ports.join('|')})\\s`, 'i');

  output.split(/\r?\n/).forEach((line) => {
    if (!portPattern.test(line) || !/LISTENING/i.test(line)) {
      return;
    }

    const parts = line.trim().split(/\s+/);
    const pid = Number(parts[parts.length - 1]);
    if (Number.isInteger(pid) && pid > 0 && pid !== process.pid) {
      pids.add(pid);
    }
  });

  return Array.from(pids);
}

function clearExpoPorts() {
  if (!args.includes('start')) {
    return;
  }

  const requestedPortIndex = args.indexOf('--port');
  const metroPort =
    requestedPortIndex >= 0 && args[requestedPortIndex + 1]
      ? Number(args[requestedPortIndex + 1])
      : 8081;
  const portsToClear = [
    Number.isInteger(metroPort) ? metroPort : 8081,
    19000,
    19001,
    19002,
    4040,
  ];
  const pids = getPidsForPorts(portsToClear);
  pids.forEach((pid) => {
    try {
      execFileSync('taskkill.exe', ['/PID', String(pid), '/T', '/F'], {
        stdio: 'ignore',
      });
      console.log(`Cleared stale Expo dev process: ${pid}`);
    } catch {
      // Best effort only; Expo can still choose another port if needed.
    }
  });
}

const baseChildEnv = {
  ...process.env,
  __UNSAFE_EXPO_HOME_DIRECTORY: expoHome,
};

delete baseChildEnv.EXPO_PUBLIC_NAIJABUILDERS_API_URL;

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

  return '';
}

function usesLan(runArgs) {
  return runArgs.some(
    (arg, index) => arg === '--host' && runArgs[index + 1] === 'lan'
  );
}

const lanIpAddress = getLanIpv4Address();

function shouldSkipAdb(runArgs) {
  return runArgs.includes('start') && !runArgs.includes('--android');
}

function getChildEnv(runArgs) {
  const childEnv = { ...baseChildEnv };

  if (usesLan(runArgs) && lanIpAddress) {
    childEnv.REACT_NATIVE_PACKAGER_HOSTNAME = lanIpAddress;
    console.log(`Expo LAN host: ${lanIpAddress}`);
  }

  if (shouldSkipAdb(runArgs)) {
    childEnv.ANDROID_HOME = path.join(projectRoot, '.skip-adb-for-tunnel');
    delete childEnv.ANDROID_SDK_ROOT;
  }

  return childEnv;
}

clearExpoPorts();

function usesTunnel(runArgs) {
  return (
    runArgs.includes('--tunnel') ||
    runArgs.some((arg, index) => arg === '--host' && runArgs[index + 1] === 'tunnel')
  );
}

function toLanArgs(runArgs) {
  const nextArgs = [];
  let hasHost = false;

  for (let index = 0; index < runArgs.length; index += 1) {
    const arg = runArgs[index];

    if (arg === '--tunnel') {
      continue;
    }

    if (arg === '--host') {
      hasHost = true;
      nextArgs.push(arg, 'lan');
      index += 1;
      continue;
    }

    nextArgs.push(arg);
  }

  if (!hasHost) {
    const startIndex = nextArgs.indexOf('start');
    nextArgs.splice(startIndex >= 0 ? startIndex + 1 : 0, 0, '--host', 'lan');
  }

  return nextArgs;
}

function runExpo(runArgs, hasRetriedWithLan = false) {
  const child = spawn(process.execPath, [expoCli, ...runArgs], {
    cwd: projectRoot,
    env: getChildEnv(runArgs),
    stdio: 'inherit',
  });

  child.on('exit', (code, signal) => {
    if (signal) {
      process.kill(process.pid, signal);
      return;
    }

    if ((code ?? 0) !== 0 && usesTunnel(runArgs) && !hasRetriedWithLan) {
      console.log('Expo tunnel failed. Retrying with LAN mode...');
      runExpo(toLanArgs(runArgs), true);
      return;
    }

    process.exit(code ?? 0);
  });
}

runExpo(args);
