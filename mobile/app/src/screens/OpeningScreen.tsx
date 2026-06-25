import React, { useEffect } from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import Animated, {
  Easing,
  useAnimatedStyle,
  useSharedValue,
  withDelay,
  withRepeat,
  withSpring,
  withTiming,
} from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import { animationSpring, animationTiming } from '../animations';
import { theme } from '../theme';

const logo = require('../../assets/images/logo.png');

export function OpeningScreen() {
  const logoOpacity = useSharedValue(0);
  const logoScale = useSharedValue(0.96);
  const spinnerOpacity = useSharedValue(0);
  const rotation = useSharedValue(0);

  useEffect(() => {
    logoOpacity.value = withTiming(1, {
      duration: animationTiming.normal,
      easing: Easing.out(Easing.cubic),
    });
    logoScale.value = withSpring(1, animationSpring);
    spinnerOpacity.value = withDelay(
      180,
      withTiming(1, {
        duration: animationTiming.normal,
        easing: Easing.out(Easing.cubic),
      })
    );
    rotation.value = withRepeat(
      withTiming(1, {
        duration: 900,
        easing: Easing.linear,
      }),
      -1,
      false
    );
  }, [logoOpacity, logoScale, rotation, spinnerOpacity]);

  const logoStyle = useAnimatedStyle(() => ({
    opacity: logoOpacity.value,
    transform: [{ scale: logoScale.value }],
  }));

  const spinnerWrapStyle = useAnimatedStyle(() => ({
    opacity: spinnerOpacity.value,
  }));

  const spinnerStyle = useAnimatedStyle(() => ({
    transform: [{ rotate: `${rotation.value * 360}deg` }],
  }));

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.content}>
        <Animated.View style={[styles.logoWrap, logoStyle]}>
          <Image source={logo} resizeMode="contain" style={styles.logo} />
          <Text style={styles.tagline}>Construction materials marketplace</Text>
        </Animated.View>

        <Animated.View style={[styles.loadingWrap, spinnerWrapStyle]}>
          <Animated.View style={[styles.spinner, spinnerStyle]} />
          <Text style={styles.loadingText}>Preparing marketplace</Text>
        </Animated.View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: theme.colors.background,
    flex: 1,
  },
  content: {
    alignItems: 'center',
    flex: 1,
    justifyContent: 'center',
    paddingHorizontal: theme.spacing.xl,
  },
  logoWrap: {
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
  },
  logo: {
    height: 104,
    maxWidth: 340,
    width: '92%',
  },
  tagline: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '800',
    letterSpacing: 0.3,
    marginTop: theme.spacing.md,
    textAlign: 'center',
  },
  loadingWrap: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    marginTop: theme.spacing.xl,
  },
  spinner: {
    borderColor: theme.colors.border,
    borderRadius: 17,
    borderRightColor: theme.colors.secondary,
    borderTopColor: theme.colors.accent,
    borderWidth: 3,
    height: 34,
    width: 34,
  },
  loadingText: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.small,
    fontWeight: '800',
  },
});
