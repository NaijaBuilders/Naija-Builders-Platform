import React from 'react';
import {
  ScrollView,
  StyleProp,
  StyleSheet,
  View,
  ViewStyle,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import { theme } from '../theme';

type ScreenProps = {
  children: React.ReactNode;
  scroll?: boolean;
  contentContainerStyle?: StyleProp<ViewStyle>;
  floating?: React.ReactNode;
};

export function Screen({
  children,
  floating,
  scroll = true,
  contentContainerStyle,
}: ScreenProps) {
  const animatedStyle = useScreenAnimation();

  if (!scroll) {
    return (
      <View style={styles.safeArea}>
        <SafeAreaView style={styles.flex}>
          <Animated.View
            style={[
              styles.content,
              styles.flex,
              animatedStyle,
              contentContainerStyle,
            ]}
          >
            {children}
          </Animated.View>
        </SafeAreaView>
        {floating}
      </View>
    );
  }

  return (
    <View style={styles.safeArea}>
      <SafeAreaView style={styles.flex}>
        <ScrollView
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
          contentContainerStyle={[styles.content, contentContainerStyle]}
        >
          <Animated.View style={animatedStyle}>{children}</Animated.View>
        </ScrollView>
      </SafeAreaView>
      {floating}
    </View>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: theme.colors.background,
    flex: 1,
  },
  content: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
  },
  flex: {
    flex: 1,
  },
});
