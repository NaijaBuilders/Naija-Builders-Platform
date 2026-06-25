const laravelBrandPlaceholder = {
  primary: '#1F4FA3',
  secondary: '#15A362',
  accent: '#F59E0B',
};

export const theme = {
  colors: {
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
    white: '#FFFFFF',
    shadow: '#0B2A5B',
    onPrimary: '#FFFFFF',
    onPrimaryMuted: '#DCEAFF',
    onPrimarySubtle: '#BFD5F6',
    overlay: 'rgba(11, 42, 91, 0.45)',
    glassLight: 'rgba(255, 255, 255, 0.14)',
    glassBorder: 'rgba(255, 255, 255, 0.22)',
  },
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
  shadows: {
    card: {
      shadowColor: '#0B2A5B',
      shadowOffset: { width: 0, height: 10 },
      shadowOpacity: 0.08,
      shadowRadius: 18,
      elevation: 4,
    },
    soft: {
      shadowColor: '#0B2A5B',
      shadowOffset: { width: 0, height: 4 },
      shadowOpacity: 0.06,
      shadowRadius: 10,
      elevation: 2,
    },
    elevated: {
      shadowColor: '#0B2A5B',
      shadowOffset: { width: 0, height: 16 },
      shadowOpacity: 0.14,
      shadowRadius: 28,
      elevation: 10,
    },
  },
};

export const colors = theme.colors;
export const spacing = theme.spacing;
export const radius = theme.radius;
export const typography = theme.typography;
export const shadows = theme.shadows;
