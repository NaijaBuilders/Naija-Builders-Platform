import React, { useCallback, useState } from 'react';
import {
  RefreshControl,
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
  /** When provided, the screen supports pull-to-refresh. */
  onRefresh?: () => Promise<unknown> | void;
};

export function Screen({
  children,
  floating,
  scroll = true,
  contentContainerStyle,
  onRefresh,
}: ScreenProps) {
  const animatedStyle = useScreenAnimation();
  const [refreshing, setRefreshing] = useState(false);

  const handleRefresh = useCallback(async () => {
    if (!onRefresh) {
      return;
    }

    setRefreshing(true);
    try {
      await onRefresh();
    } finally {
      setRefreshing(false);
    }
  }, [onRefresh]);

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
          refreshControl={
            onRefresh ? (
              <RefreshControl
                onRefresh={handleRefresh}
                refreshing={refreshing}
                tintColor={theme.colors.primary}
                colors={[theme.colors.primary]}
              />
            ) : undefined
          }
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
