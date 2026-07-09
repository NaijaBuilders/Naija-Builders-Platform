import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { StatusTone } from '../types';

type BadgeProps = {
  label: string;
  tone?: StatusTone;
};

// Built per-render (not at module scope) so the colors track the live theme
// after a light/dark switch. Borders derive from the tone color so they adapt
// to dark mode instead of using fixed light-only hexes.
function getToneStyles(): Record<
  StatusTone,
  { background: string; color: string; border: string }
> {
  return {
    danger: { background: theme.colors.dangerSoft, color: theme.colors.danger, border: theme.colors.dangerSoft },
    neutral: { background: theme.colors.surfaceMuted, color: theme.colors.textMuted, border: theme.colors.border },
    primary: { background: theme.colors.primarySoft, color: theme.colors.primaryDark, border: theme.colors.primarySoft },
    success: { background: theme.colors.secondarySoft, color: theme.colors.success, border: theme.colors.secondarySoft },
    warning: { background: theme.colors.accentSoft, color: theme.colors.warning, border: theme.colors.accentSoft },
  };
}

export function Badge({ label, tone = 'neutral' }: BadgeProps) {
  const palette = getToneStyles()[tone];

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
