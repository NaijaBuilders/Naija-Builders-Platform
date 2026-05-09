import { Ionicons } from '@expo/vector-icons';
import React from 'react';
import {
  Pressable,
  StyleProp,
  StyleSheet,
  Text,
  View,
  ViewStyle,
} from 'react-native';
import { theme } from '../theme';
import type { QuickAction } from '../types';
import { Card } from './Card';

type QuickActionCardProps = {
  action: QuickAction;
  animationIndex?: number;
  layout?: 'row' | 'tile';
  onPress?: (action: QuickAction) => void;
  style?: StyleProp<ViewStyle>;
};

type IconName = React.ComponentProps<typeof Ionicons>['name'];

export function QuickActionCard({
  action,
  animationIndex,
  layout = 'row',
  onPress,
  style,
}: QuickActionCardProps) {
  const isTile = layout === 'tile';

  return (
    <Card animationIndex={animationIndex} style={[styles.card, style]}>
      <Pressable
        accessibilityRole={onPress ? 'button' : undefined}
        disabled={!onPress}
        onPress={() => onPress?.(action)}
        style={({ pressed }) => [
          styles.pressable,
          isTile ? styles.tilePressable : null,
          pressed ? styles.pressed : null,
        ]}
      >
        <View style={[styles.icon, isTile ? styles.tileIcon : null]}>
          <Ionicons
            color={theme.colors.primary}
            name={action.icon as IconName}
            size={22}
          />
        </View>
        <View style={styles.copy}>
          <Text style={styles.title}>{action.title}</Text>
          <Text numberOfLines={isTile ? 3 : 2} style={styles.description}>
            {action.description}
          </Text>
        </View>
        {onPress ? (
          <Ionicons
            color={theme.colors.textSubtle}
            name="chevron-forward"
            size={18}
            style={isTile ? styles.tileChevron : null}
          />
        ) : null}
      </Pressable>
    </Card>
  );
}

const styles = StyleSheet.create({
  card: {
    marginBottom: theme.spacing.md,
    overflow: 'hidden',
    padding: 0,
  },
  pressable: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    minHeight: 82,
    padding: theme.spacing.md,
  },
  tilePressable: {
    alignItems: 'flex-start',
    flexDirection: 'column',
    minHeight: 140,
  },
  pressed: {
    opacity: 0.74,
  },
  icon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 44,
    justifyContent: 'center',
    width: 44,
  },
  tileIcon: {
    height: 40,
    width: 40,
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
  tileChevron: {
    position: 'absolute',
    right: theme.spacing.md,
    top: theme.spacing.md,
  },
});
