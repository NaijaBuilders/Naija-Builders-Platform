import React from 'react';
import { StyleProp, StyleSheet, ViewStyle } from 'react-native';
import Animated from 'react-native-reanimated';
import { fadeOut, layoutTransition, staggeredFadeScale } from '../animations';
import { theme } from '../theme';

type CardVariant = 'elevated' | 'flat' | 'outline';

type CardProps = {
  children: React.ReactNode;
  style?: StyleProp<ViewStyle>;
  animationIndex?: number;
  animated?: boolean;
  variant?: CardVariant;
};

export function Card({
  children,
  style,
  animationIndex = 0,
  animated = true,
  variant = 'elevated',
}: CardProps) {
  return (
    <Animated.View
      entering={animated ? staggeredFadeScale(animationIndex) : undefined}
      exiting={animated ? fadeOut : undefined}
      layout={layoutTransition}
      style={[styles.card, variantStyles[variant], style]}
    >
      {children}
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    padding: theme.spacing.lg,
  },
});

const variantStyles = StyleSheet.create({
  elevated: {
    ...theme.shadows.card,
  },
  flat: {
    backgroundColor: theme.colors.surfaceMuted,
    ...theme.shadows.soft,
  },
  outline: {
    backgroundColor: 'transparent',
    borderColor: theme.colors.border,
  },
});
