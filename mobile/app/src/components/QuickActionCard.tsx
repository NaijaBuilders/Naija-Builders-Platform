import { Ionicons } from '@expo/vector-icons';
import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { QuickAction } from '../types';
import { Card } from './Card';

type QuickActionCardProps = {
  action: QuickAction;
  animationIndex?: number;
};

type IconName = React.ComponentProps<typeof Ionicons>['name'];

export function QuickActionCard({ action, animationIndex }: QuickActionCardProps) {
  return (
    <Card animationIndex={animationIndex} style={styles.card}>
      <View style={styles.icon}>
        <Ionicons
          color={theme.colors.primary}
          name={action.icon as IconName}
          size={22}
        />
      </View>
      <View style={styles.copy}>
        <Text style={styles.title}>{action.title}</Text>
        <Text style={styles.description}>{action.description}</Text>
      </View>
    </Card>
  );
}

const styles = StyleSheet.create({
  card: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  icon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 44,
    justifyContent: 'center',
    width: 44,
  },
  copy: {
    flex: 1,
  },
  title: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  description: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: 2,
  },
});
