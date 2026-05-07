import React from 'react';
import { StyleSheet, Text } from 'react-native';
import { theme } from '../theme';
import type { DashboardStat, StatusTone } from '../types';
import { Card } from './Card';

type StatCardProps = {
  stat: DashboardStat;
  animationIndex?: number;
};

const toneColors: Record<StatusTone, { background: string; color: string }> = {
  primary: { background: theme.colors.primarySoft, color: theme.colors.primaryDark },
  success: { background: theme.colors.secondarySoft, color: theme.colors.success },
  warning: { background: theme.colors.accentSoft, color: theme.colors.warning },
  danger: { background: '#FFE9E5', color: theme.colors.danger },
  neutral: { background: theme.colors.surfaceMuted, color: theme.colors.textMuted },
};

export function StatCard({ animationIndex, stat }: StatCardProps) {
  const tone = toneColors[stat.tone];

  return (
    <Card
      animationIndex={animationIndex}
      style={[styles.card, { backgroundColor: tone.background }]}
    >
      <Text style={[styles.label, { color: tone.color }]}>{stat.label}</Text>
      <Text style={styles.value}>{stat.value}</Text>
      <Text style={styles.change}>{stat.change}</Text>
    </Card>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    minHeight: 112,
    padding: theme.spacing.md,
  },
  label: {
    fontSize: 12,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  value: {
    color: theme.colors.text,
    fontSize: 24,
    fontWeight: '900',
    marginTop: theme.spacing.sm,
  },
  change: {
    color: theme.colors.textMuted,
    fontSize: 12,
    lineHeight: 17,
    marginTop: 4,
  },
});
