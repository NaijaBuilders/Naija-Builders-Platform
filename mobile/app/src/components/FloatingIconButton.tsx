import { Ionicons } from '@expo/vector-icons';
import { BlurView } from 'expo-blur';
import React from 'react';
import { Platform, Pressable, StyleSheet, Text } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { theme } from '../theme';

type IconName = React.ComponentProps<typeof Ionicons>['name'];

type FloatingIconButtonProps = {
  icon: IconName;
  onPress: () => void;
  accessibilityLabel: string;
  /** Distance from the right edge. */
  offsetRight?: number;
  offsetTop?: number;
  badgeCount?: number;
  iconColor?: string;
};

export function FloatingIconButton({
  icon,
  onPress,
  accessibilityLabel,
  offsetRight = theme.spacing.md,
  offsetTop = theme.spacing.sm,
  badgeCount = 0,
  iconColor = theme.colors.text,
}: FloatingIconButtonProps) {
  const insets = useSafeAreaInsets();

  return (
    <Pressable
      accessibilityLabel={accessibilityLabel}
      accessibilityRole="button"
      hitSlop={12}
      onPress={onPress}
      style={[
        styles.button,
        {
          right: offsetRight,
          top: insets.top + offsetTop,
        },
      ]}
    >
      <BlurView
        experimentalBlurMethod={Platform.OS === 'android' ? 'none' : undefined}
        intensity={68}
        style={styles.blur}
        tint="systemChromeMaterialLight"
      />
      <Ionicons color={iconColor} name={icon} size={21} />
      {badgeCount > 0 ? (
        <Text style={styles.badge}>{badgeCount > 99 ? '99+' : badgeCount}</Text>
      ) : null}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  button: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.72)',
    borderColor: 'rgba(17, 24, 39, 0.08)',
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    elevation: 18,
    height: 44,
    justifyContent: 'center',
    overflow: 'visible',
    position: 'absolute',
    shadowColor: theme.colors.shadow,
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.16,
    shadowRadius: 18,
    width: 44,
    zIndex: 50,
  },
  blur: {
    ...StyleSheet.absoluteFillObject,
    borderRadius: theme.radius.pill,
    overflow: 'hidden',
  },
  badge: {
    backgroundColor: theme.colors.secondary,
    borderRadius: theme.radius.pill,
    color: theme.colors.white,
    fontSize: 10,
    fontWeight: '900',
    minWidth: 18,
    overflow: 'hidden',
    paddingHorizontal: 4,
    paddingVertical: 2,
    position: 'absolute',
    right: -4,
    textAlign: 'center',
    top: -4,
  },
});
