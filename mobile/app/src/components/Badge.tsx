import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { StatusTone } from '../types';

type BadgeProps = {
  label: string;
  tone?: StatusTone;
};

const toneStyles: Record<
  StatusTone,
  { background: string; color: string; border: string }
> = {
  danger: { background: '#FFE9E5', color: theme.colors.danger, border: '#FBD2C9' },
  neutral: { background: theme.colors.surfaceMuted, color: theme.colors.textMuted, border: theme.colors.border },
  primary: { background: theme.colors.primarySoft, color: theme.colors.primaryDark, border: '#CBDDF7' },
  success: { background: theme.colors.secondarySoft, color: theme.colors.success, border: '#C4ECD5' },
  warning: { background: theme.colors.accentSoft, color: theme.colors.warning, border: '#F4E1AE' },
};

export function Badge({ label, tone = 'neutral' }: BadgeProps) {
  const palette = toneStyles[tone];

  return (
    <View
      style={[
        styles.badge,
        { backgroundColor: palette.background, borderColor: palette.border },
      ]}
    >
      <View style={[styles.dot, { backgroundColor: palette.color }]} />
      <Text style={[styles.text, { color: palette.color }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignItems: 'center',
    alignSelf: 'flex-start',
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    flexDirection: 'row',
    gap: 6,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: 5,
  },
  dot: {
    borderRadius: theme.radius.pill,
    height: 6,
    width: 6,
  },
  text: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.2,
  },
});
