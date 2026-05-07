import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { StatusTone } from '../types';

type BadgeProps = {
  label: string;
  tone?: StatusTone;
};

const toneStyles: Record<StatusTone, { background: string; color: string }> = {
  danger: { background: '#FFE9E5', color: theme.colors.danger },
  neutral: { background: theme.colors.surfaceMuted, color: theme.colors.textMuted },
  primary: { background: theme.colors.primarySoft, color: theme.colors.primaryDark },
  success: { background: theme.colors.secondarySoft, color: theme.colors.success },
  warning: { background: theme.colors.accentSoft, color: theme.colors.warning },
};

export function Badge({ label, tone = 'neutral' }: BadgeProps) {
  const palette = toneStyles[tone];

  return (
    <View style={[styles.badge, { backgroundColor: palette.background }]}>
      <Text style={[styles.text, { color: palette.color }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    borderRadius: theme.radius.pill,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: 6,
  },
  text: {
    fontSize: 11,
    fontWeight: '900',
  },
});
