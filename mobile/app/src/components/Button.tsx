import React from 'react';
import {
  ActivityIndicator,
  Pressable,
  StyleProp,
  StyleSheet,
  Text,
  ViewStyle,
} from 'react-native';
import Animated, {
  useAnimatedStyle,
  useSharedValue,
  withSpring,
} from 'react-native-reanimated';
import { animationSpring, layoutTransition } from '../animations';
import { theme } from '../theme';

type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'ghost';
type ButtonSize = 'md' | 'lg';

type ButtonProps = {
  title: string;
  onPress?: () => void;
  variant?: ButtonVariant;
  size?: ButtonSize;
  disabled?: boolean;
  loading?: boolean;
  style?: StyleProp<ViewStyle>;
};

const variantStyles: Record<
  ButtonVariant,
  { backgroundColor: string; borderColor: string; color: string }
> = {
  primary: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
    color: theme.colors.white,
  },
  secondary: {
    backgroundColor: theme.colors.secondary,
    borderColor: theme.colors.secondary,
    color: theme.colors.white,
  },
  outline: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.borderStrong,
    color: theme.colors.primaryDark,
  },
  ghost: {
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    color: theme.colors.text,
  },
};

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

export function Button({
  title,
  onPress,
  variant = 'primary',
  size = 'md',
  disabled,
  loading,
  style,
}: ButtonProps) {
  const palette = variantStyles[variant];
  const scale = useSharedValue(1);
  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));
  const isSolid = variant === 'primary' || variant === 'secondary';

  return (
    <AnimatedPressable
      accessibilityRole="button"
      disabled={disabled || loading}
      onPress={onPress}
      onPressIn={() => {
        scale.value = withSpring(0.96, animationSpring);
      }}
      onPressOut={() => {
        scale.value = withSpring(1, animationSpring);
      }}
      layout={layoutTransition}
      style={[
        styles.button,
        size === 'lg' ? styles.buttonLg : null,
        isSolid ? styles.solidShadow : null,
        {
          backgroundColor: palette.backgroundColor,
          borderColor: palette.borderColor,
          opacity: disabled ? 0.5 : 1,
        },
        animatedStyle,
        style,
      ]}
    >
      {loading ? (
        <ActivityIndicator color={palette.color} />
      ) : (
        <Text
          style={[
            styles.text,
            size === 'lg' ? styles.textLg : null,
            { color: palette.color },
          ]}
        >
          {title}
        </Text>
      )}
    </AnimatedPressable>
  );
}

const styles = StyleSheet.create({
  button: {
    alignItems: 'center',
    borderRadius: theme.radius.md,
    borderWidth: 1.5,
    justifyContent: 'center',
    minHeight: 50,
    paddingHorizontal: theme.spacing.lg,
  },
  buttonLg: {
    minHeight: 58,
    borderRadius: theme.radius.lg,
  },
  solidShadow: {
    shadowColor: theme.colors.primary,
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.24,
    shadowRadius: 16,
    elevation: 6,
  },
  text: {
    fontSize: 14,
    fontWeight: '800',
    letterSpacing: 0.3,
  },
  textLg: {
    fontSize: 16,
  },
});
