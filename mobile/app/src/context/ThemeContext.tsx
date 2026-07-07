import AsyncStorage from '@react-native-async-storage/async-storage';
import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import { Appearance, type ColorSchemeName } from 'react-native';
import { applyThemeScheme, type ThemeScheme } from '../theme';

export type ThemeMode = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'naijabuilders.theme.mode';

function resolveScheme(mode: ThemeMode, system: ColorSchemeName): ThemeScheme {
  if (mode === 'system') {
    return system === 'dark' ? 'dark' : 'light';
  }
  return mode;
}

type ThemeContextValue = {
  mode: ThemeMode;
  scheme: ThemeScheme;
  /** Bump this on any theme change; used as a remount key so StyleSheets rebuild. */
  themeVersion: number;
  setMode: (mode: ThemeMode) => void;
};

const ThemeContext = createContext<ThemeContextValue | undefined>(undefined);

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [mode, setModeState] = useState<ThemeMode>('system');
  const [systemScheme, setSystemScheme] = useState<ColorSchemeName>(
    Appearance.getColorScheme()
  );
  const [themeVersion, setThemeVersion] = useState(0);

  // Load the saved preference once.
  useEffect(() => {
    AsyncStorage.getItem(STORAGE_KEY)
      .then((saved) => {
        if (saved === 'light' || saved === 'dark' || saved === 'system') {
          setModeState(saved);
        }
      })
      .catch(() => undefined);
  }, []);

  // Track OS theme changes (only matters while mode === 'system').
  useEffect(() => {
    const subscription = Appearance.addChangeListener(({ colorScheme }) => {
      setSystemScheme(colorScheme);
    });
    return () => subscription.remove();
  }, []);

  const scheme = useMemo(
    () => resolveScheme(mode, systemScheme),
    [mode, systemScheme]
  );

  // Apply the resolved scheme to the live palette. When it actually changes,
  // bump the version so consumers (App tree) remount and StyleSheets rebuild.
  useEffect(() => {
    if (applyThemeScheme(scheme)) {
      setThemeVersion((current) => current + 1);
    }
  }, [scheme]);

  const setMode = useCallback((next: ThemeMode) => {
    setModeState(next);
    AsyncStorage.setItem(STORAGE_KEY, next).catch(() => undefined);
  }, []);

  const value = useMemo(
    () => ({ mode, scheme, themeVersion, setMode }),
    [mode, scheme, themeVersion, setMode]
  );

  return (
    <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
  );
}

export function useThemeMode() {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useThemeMode must be used inside ThemeProvider');
  }
  return context;
}
