import { Ionicons } from '@expo/vector-icons';
import {
  type NavigationProp,
  type ParamListBase,
  useNavigation,
} from '@react-navigation/native';
import { BlurView, type BlurTint } from 'expo-blur';
import React, { useCallback } from 'react';
import {
  Platform,
  Pressable,
  StyleProp,
  StyleSheet,
  useColorScheme,
  ViewStyle,
} from 'react-native';
import Animated, {
  FadeIn,
  FadeOut,
  useAnimatedStyle,
  useSharedValue,
  withTiming,
} from 'react-native-reanimated';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { theme } from '../theme';

type FloatingBackButtonTint = 'auto' | 'light' | 'dark';

type FloatingBackButtonProps = {
  fallback?: () => void;
  hideWhenUnavailable?: boolean;
  offsetLeft?: number;
  offsetTop?: number;
  style?: StyleProp<ViewStyle>;
  tint?: FloatingBackButtonTint;
};

const AnimatedPressable = Animated.createAnimatedComponent(Pressable);

export function FloatingBackButton({
  fallback,
  hideWhenUnavailable = true,
  offsetLeft = theme.spacing.md,
  offsetTop = theme.spacing.sm,
  style,
  tint = 'auto',
}: FloatingBackButtonProps) {
  const navigation = useNavigation<NavigationProp<ParamListBase>>();
  const colorScheme = useColorScheme();
  const insets = useSafeAreaInsets();
  const scale = useSharedValue(1);
  const canGoBack = navigation.canGoBack();

  const resolvedTint =
    tint === 'auto' ? (colorScheme === 'dark' ? 'dark' : 'light') : tint;
  const blurTint: BlurTint =
    resolvedTint === 'dark'
      ? 'systemChromeMaterialDark'
      : 'systemChromeMaterialLight';
  const iconColor =
    resolvedTint === 'dark' ? theme.colors.white : theme.colors.text;
  const fallbackBackground =
    resolvedTint === 'dark'
      ? 'rgba(17, 24, 39, 0.58)'
      : 'rgba(255, 255, 255, 0.72)';
  const borderColor =
    resolvedTint === 'dark'
      ? 'rgba(255, 255, 255, 0.18)'
      : 'rgba(17, 24, 39, 0.08)';

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  const handlePress = useCallback(() => {
    if (navigation.canGoBack()) {
      navigation.goBack();
      return;
    }

    fallback?.();
  }, [fallback, navigation]);

  const handlePressIn = useCallback(() => {
    scale.value = withTiming(0.94, { duration: 80 });
  }, [scale]);

  const handlePressOut = useCallback(() => {
    scale.value = withTiming(1, { duration: 110 });
  }, [scale]);

  if (hideWhenUnavailable && !canGoBack && !fallback) {
    return null;
  }

  return (
    <AnimatedPressable
      accessibilityLabel="Go back"
      accessibilityRole="button"
      entering={FadeIn.duration(120)}
      exiting={FadeOut.duration(90)}
      hitSlop={12}
      onPress={handlePress}
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      style={[
        styles.button,
        {
          backgroundColor: fallbackBackground,
          borderColor,
          left: offsetLeft,
          top: insets.top + offsetTop,
        },
        animatedStyle,
        style,
      ]}
    >
      <BlurView
        experimentalBlurMethod={Platform.OS === 'android' ? 'none' : undefined}
        intensity={68}
        style={styles.blur}
        tint={blurTint}
      />
      <Ionicons color={iconColor} name="chevron-back" size={24} />
    </AnimatedPressable>
  );
}

const styles = StyleSheet.create({
  button: {
    alignItems: 'center',
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    elevation: 18,
    height: 44,
    justifyContent: 'center',
    overflow: 'hidden',
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
  },
});
