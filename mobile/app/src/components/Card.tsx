import React from 'react';
import { StyleProp, StyleSheet, ViewStyle } from 'react-native';
import Animated from 'react-native-reanimated';
import { fadeOut, layoutTransition, staggeredFadeScale } from '../animations';
import { theme } from '../theme';

type CardProps = {
  children: React.ReactNode;
  style?: StyleProp<ViewStyle>;
  animationIndex?: number;
  animated?: boolean;
};

export function Card({
  children,
  style,
  animationIndex = 0,
  animated = true,
}: CardProps) {
  return (
    <Animated.View
      entering={animated ? staggeredFadeScale(animationIndex) : undefined}
      exiting={animated ? fadeOut : undefined}
      layout={layoutTransition}
      style={[styles.card, style]}
    >
      {children}
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    padding: theme.spacing.md,
    ...theme.shadows.card,
  },
});
