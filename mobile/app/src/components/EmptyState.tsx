import { Ionicons } from '@expo/vector-icons';
import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import { Button } from './Button';
import { Card } from './Card';

type IconName = React.ComponentProps<typeof Ionicons>['name'];

type EmptyStateProps = {
  icon: IconName;
  title: string;
  message: string;
  /** Smaller companion icons orbiting the main one. */
  accessoryIcons?: [IconName, IconName];
  tone?: 'primary' | 'danger' | 'success' | 'warning';
  actionTitle?: string;
  onAction?: () => void;
};

const TONE_STYLES = {
  primary: { bg: theme.colors.primarySoft, fg: theme.colors.primary },
  danger: { bg: theme.colors.dangerSoft, fg: theme.colors.danger },
  success: { bg: theme.colors.secondarySoft, fg: theme.colors.secondary },
  warning: { bg: theme.colors.accentSoft, fg: theme.colors.warning },
} as const;

export function EmptyState({
  icon,
  title,
  message,
  accessoryIcons,
  tone = 'primary',
  actionTitle,
  onAction,
}: EmptyStateProps) {
  const palette = TONE_STYLES[tone];

  return (
    <Card style={styles.card}>
      <View style={styles.illustration}>
        <View style={[styles.halo, { backgroundColor: palette.bg }]} />
        <View style={[styles.iconCircle, { backgroundColor: palette.bg }]}>
          <Ionicons color={palette.fg} name={icon} size={34} />
        </View>
        {accessoryIcons ? (
          <>
            <View style={[styles.accessory, styles.accessoryLeft]}>
              <Ionicons
                color={theme.colors.textSubtle}
                name={accessoryIcons[0]}
                size={15}
              />
            </View>
            <View style={[styles.accessory, styles.accessoryRight]}>
              <Ionicons
                color={theme.colors.textSubtle}
                name={accessoryIcons[1]}
                size={15}
              />
            </View>
          </>
        ) : null}
      </View>
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.message}>{message}</Text>
      {actionTitle && onAction ? (
        <Button onPress={onAction} title={actionTitle} variant="outline" />
      ) : null}
    </Card>
  );
}

const styles = StyleSheet.create({
  card: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.xl,
  },
  illustration: {
    alignItems: 'center',
    height: 110,
    justifyContent: 'center',
    marginBottom: theme.spacing.xs,
    width: 160,
  },
  halo: {
    borderRadius: theme.radius.pill,
    height: 104,
    opacity: 0.45,
    position: 'absolute',
    width: 104,
  },
  iconCircle: {
    alignItems: 'center',
    borderRadius: theme.radius.pill,
    height: 76,
    justifyContent: 'center',
    width: 76,
  },
  accessory: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    height: 32,
    justifyContent: 'center',
    position: 'absolute',
    width: 32,
    ...theme.shadows.soft,
  },
  accessoryLeft: {
    left: 8,
    top: 10,
  },
  accessoryRight: {
    bottom: 6,
    right: 8,
  },
  title: {
    color: theme.colors.text,
    fontSize: 17,
    fontWeight: '900',
  },
  message: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    marginBottom: theme.spacing.xs,
    textAlign: 'center',
  },
});
