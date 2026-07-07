import { Appearance } from 'react-native';

const laravelBrandPlaceholder = {
  primary: '#1F4FA3',
  secondary: '#15A362',
  accent: '#F59E0B',
};

export const lightColors = {
  primary: laravelBrandPlaceholder.primary,
  primaryDark: '#123B7A',
  primarySoft: '#E7F0FF',
  secondary: laravelBrandPlaceholder.secondary,
  secondarySoft: '#E4F7EC',
  accent: laravelBrandPlaceholder.accent,
  accentSoft: '#FFF3D6',
  background: '#F4F7FB',
  surface: '#FFFFFF',
  surfaceMuted: '#F8FAFC',
  text: '#111827',
  textMuted: '#667085',
  textSubtle: '#98A2B3',
  border: '#D9E2EF',
  borderStrong: '#B8C7DA',
  success: '#1E9A5B',
  warning: '#B7791F',
  danger: '#D94A38',
  dangerSoft: '#FFE9E5',
  white: '#FFFFFF',
  shadow: '#0B2A5B',
  onPrimary: '#FFFFFF',
  onPrimaryMuted: '#DCEAFF',
  onPrimarySubtle: '#BFD5F6',
  overlay: 'rgba(11, 42, 91, 0.45)',
  glassLight: 'rgba(255, 255, 255, 0.14)',
  glassBorder: 'rgba(255, 255, 255, 0.22)',
};

export const darkColors: typeof lightColors = {
  primary: '#4F7ECC',
  primaryDark: '#A9C6F2',
  primarySoft: '#1B2A44',
  secondary: '#22B573',
  secondarySoft: '#11291D',
  accent: '#F5A623',
  accentSoft: '#3A2E12',
  background: '#0E1420',
  surface: '#161D2B',
  surfaceMuted: '#1D2536',
  text: '#F2F5FA',
  textMuted: '#9AA7BD',
  textSubtle: '#6E7C94',
  border: '#26314A',
  borderStrong: '#374663',
  success: '#2FBF71',
  warning: '#E0A83F',
  danger: '#F0705C',
  dangerSoft: '#3A1B16',
  white: '#FFFFFF',
  shadow: '#000000',
  onPrimary: '#FFFFFF',
  onPrimaryMuted: '#DCEAFF',
  onPrimarySubtle: '#BFD5F6',
  overlay: 'rgba(0, 0, 0, 0.55)',
  glassLight: 'rgba(255, 255, 255, 0.08)',
  glassBorder: 'rgba(255, 255, 255, 0.14)',
};

export type ThemeScheme = 'light' | 'dark';

const initialScheme: ThemeScheme =
  Appearance.getColorScheme() === 'dark' ? 'dark' : 'light';

// `isDarkTheme` and `theme.colors` are read by every screen at StyleSheet
// creation time. To switch theme live we keep the SAME object identities and
// overwrite their contents, then remount the tree so every StyleSheet.create
// re-runs and reads the new values. See applyThemeScheme + the ThemeProvider
// key in App.tsx.
export let isDarkTheme = initialScheme === 'dark';

const colors = { ...(isDarkTheme ? darkColors : lightColors) };
const shadowColor = { value: isDarkTheme ? '#000000' : '#0B2A5B' };

const shadows = {
  card: {
    shadowColor: shadowColor.value,
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: isDarkTheme ? 0.3 : 0.08,
    shadowRadius: 18,
    elevation: 4,
  },
  soft: {
    shadowColor: shadowColor.value,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: isDarkTheme ? 0.24 : 0.06,
    shadowRadius: 10,
    elevation: 2,
  },
  elevated: {
    shadowColor: shadowColor.value,
    shadowOffset: { width: 0, height: 16 },
    shadowOpacity: isDarkTheme ? 0.4 : 0.14,
    shadowRadius: 28,
    elevation: 10,
  },
};

export const theme = {
  colors,
  spacing: {
    xs: 6,
    sm: 10,
    md: 16,
    lg: 22,
    xl: 30,
    xxl: 42,
  },
  radius: {
    sm: 8,
    md: 12,
    lg: 18,
    xl: 26,
    pill: 999,
  },
  typography: {
    display: 30,
    title: 26,
    section: 18,
    body: 15,
    small: 12,
    tiny: 11,
  },
  shadows,
};

/**
 * Overwrite the live palette in place so a following tree remount picks up the
 * new colors. Returns true if the scheme actually changed.
 */
export function applyThemeScheme(scheme: ThemeScheme): boolean {
  const nextIsDark = scheme === 'dark';
  if (nextIsDark === isDarkTheme) {
    return false;
  }

  isDarkTheme = nextIsDark;
  Object.assign(colors, nextIsDark ? darkColors : lightColors);

  const nextShadow = nextIsDark ? '#000000' : '#0B2A5B';
  theme.shadows.card.shadowColor = nextShadow;
  theme.shadows.card.shadowOpacity = nextIsDark ? 0.3 : 0.08;
  theme.shadows.soft.shadowColor = nextShadow;
  theme.shadows.soft.shadowOpacity = nextIsDark ? 0.24 : 0.06;
  theme.shadows.elevated.shadowColor = nextShadow;
  theme.shadows.elevated.shadowOpacity = nextIsDark ? 0.4 : 0.14;

  return true;
}
