import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useCallback, useMemo } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
  Platform,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import { FloatingBackButton, Header, Loader, OrderCard } from '../components';
import { useOrders } from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import { useAppState } from '../context/AppContext';
import type { MainTabParamList, OrdersStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { Order } from '../types';

const listPerformanceProps = {
  initialNumToRender: 7,
  maxToRenderPerBatch: 7,
  removeClippedSubviews: Platform.OS === 'android',
  updateCellsBatchingPeriod: 50,
  windowSize: 7,
};

export function OrdersScreen() {
  const navigation =
    useNavigation<NativeStackNavigationProp<OrdersStackParamList, 'OrdersMain'>>();
  const { currentRole } = useAppState();
  const { data: orderList, error, loading } = useOrders(currentRole);
  const animatedStyle = useScreenAnimation();
  const orderSummary = useMemo(
    () => [
      { label: 'Total', value: orderList.length },
      {
        label: 'Open',
        value: orderList.filter((order) =>
          ['Pending', 'Processing'].includes(order.status)
        ).length,
      },
      {
        label: 'Delivered',
        value: orderList.filter((order) => order.status === 'Delivered').length,
      },
    ],
    [orderList]
  );
  const keyExtractor = useCallback((item: Order) => item.id, []);
  const openOrder = useCallback(
    (order: Order) => {
      navigation.navigate('OrderDetail', { orderId: order.id });
    },
    [navigation]
  );
  const goHome = useCallback(() => {
    navigation
      .getParent<BottomTabNavigationProp<MainTabParamList>>()
      ?.navigate('Home', { screen: 'HomeMain' });
  }, [navigation]);
  const renderOrder = useCallback(
    ({ item, index }: ListRenderItemInfo<Order>) => (
      <OrderCard animationIndex={index} order={item} onPress={openOrder} />
    ),
    [openOrder]
  );
  const header = useMemo(
    () => (
      <>
        <Header
          eyebrow={currentRole}
          title="Orders"
          subtitle="Orders connected to your account."
        />
        <View style={styles.summaryStrip}>
          {orderSummary.map((item, index) => (
            <View key={item.label} style={styles.summaryItem}>
              <Text style={styles.summaryValue}>{item.value}</Text>
              <Text style={styles.summaryLabel}>{item.label}</Text>
              {index < orderSummary.length - 1 ? (
                <View style={styles.summaryDivider} />
              ) : null}
            </View>
          ))}
        </View>
        <Text style={styles.sectionTitle}>Recent orders</Text>
      </>
    ),
    [currentRole, orderSummary]
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          {...listPerformanceProps}
          data={orderList}
          keyExtractor={keyExtractor}
          ListEmptyComponent={
            loading ? (
              <Loader label="Loading orders" />
            ) : (
              <Text style={styles.empty}>
                {error?.message || 'No orders found for this account.'}
              </Text>
            )
          }
          ListHeaderComponent={header}
          renderItem={renderOrder}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.contentWithFloatingBack}
        />
      </Animated.View>
      <FloatingBackButton fallback={goHome} hideWhenUnavailable={false} />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: theme.colors.background,
    flex: 1,
  },
  container: {
    flex: 1,
  },
  content: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
  },
  contentWithFloatingBack: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
    paddingTop: 86,
  },
  summaryStrip: {
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    marginBottom: theme.spacing.lg,
    paddingVertical: theme.spacing.md,
  },
  summaryItem: {
    alignItems: 'center',
    flex: 1,
  },
  summaryDivider: {
    backgroundColor: theme.colors.border,
    height: 36,
    position: 'absolute',
    right: 0,
    top: 4,
    width: 1,
  },
  summaryValue: {
    color: theme.colors.primaryDark,
    fontSize: 18,
    fontWeight: '900',
  },
  summaryLabel: {
    color: theme.colors.textMuted,
    fontSize: 11,
    fontWeight: '800',
    marginTop: 3,
    textTransform: 'uppercase',
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    textAlign: 'center',
  },
});
