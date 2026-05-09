import { Ionicons } from '@expo/vector-icons';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import type { CompositeNavigationProp } from '@react-navigation/native';
import React, { useCallback, useMemo, useState } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import { fadeIn, fadeOut, layoutTransition } from '../animations';
import {
  Avatar,
  Header,
  Input,
  Loader,
  OrderCard,
  ProductCard,
  QuickActionCard,
  Screen,
  StatCard,
} from '../components';
import {
  useOrders,
  useProducts,
  useSupplierDashboard,
} from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import { useAppState } from '../context/AppContext';
import type { HomeStackParamList, MainTabParamList } from '../navigation/types';
import { theme } from '../theme';
import type { DashboardStat, Order, Product, QuickAction } from '../types';

const listPerformanceProps = {
  initialNumToRender: 6,
  maxToRenderPerBatch: 6,
  removeClippedSubviews: Platform.OS === 'android',
  updateCellsBatchingPeriod: 50,
  windowSize: 7,
};

export function HomeScreen() {
  const { currentRole, user } = useAppState();
  const [query, setQuery] = useState('');
  const productResource = useProducts(currentRole);
  const orderResource = useOrders(currentRole);
  const dashboardResource = useSupplierDashboard(currentRole);

  const filteredProducts = useMemo(() => {
    const normalizedQuery = query.trim().toLowerCase();

    if (!normalizedQuery) {
      return productResource.data;
    }

    return productResource.data.filter((product) =>
      [product.name, product.category, product.supplierName, product.location]
        .join(' ')
        .toLowerCase()
        .includes(normalizedQuery)
    );
  }, [productResource.data, query]);

  if (!user) {
    return (
      <Screen scroll={false} contentContainerStyle={styles.center}>
        <Loader label="Loading account" />
      </Screen>
    );
  }

  return (
    <Animated.View
      key={currentRole}
      entering={fadeIn}
      exiting={fadeOut}
      layout={layoutTransition}
      style={styles.roleContainer}
    >
      {currentRole === 'supplier' ? (
        <SupplierHome
          firstName={user.name.split(' ')[0]}
          orderList={orderResource.data.slice(0, 2)}
          quickActions={dashboardResource.data.quickActions}
          stats={dashboardResource.data.stats}
          error={dashboardResource.error?.message}
          userName={user.name}
        />
      ) : (
        <BuyerHome
          firstName={user.name.split(' ')[0]}
          loading={productResource.loading}
          products={filteredProducts}
          query={query}
          setQuery={setQuery}
          error={productResource.error?.message}
          userName={user.name}
        />
      )}
    </Animated.View>
  );
}

type SupplierHomeProps = {
  firstName: string;
  orderList: Order[];
  quickActions: QuickAction[];
  stats: DashboardStat[];
  error?: string;
  userName: string;
};

type SupplierHomeNavigation = CompositeNavigationProp<
  NativeStackNavigationProp<HomeStackParamList, 'HomeMain'>,
  BottomTabNavigationProp<MainTabParamList>
>;

function SupplierHome({
  error,
  firstName,
  orderList,
  quickActions,
  stats,
  userName,
}: SupplierHomeProps) {
  const navigation = useNavigation<SupplierHomeNavigation>();
  const tabNavigation =
    navigation.getParent<BottomTabNavigationProp<MainTabParamList>>();
  const statLookup = useMemo(
    () => new Map(stats.map((stat) => [stat.id, stat])),
    [stats]
  );
  const listingsStat = statLookup.get('stat-listings');
  const stockStat = statLookup.get('stat-stock');
  const inventoryStat = statLookup.get('stat-inventory');
  const messagesStat = statLookup.get('stat-messages');
  const openOrderCount = orderList.filter((order) =>
    ['Pending', 'Processing'].includes(order.status)
  ).length;

  const handleQuickAction = useCallback(
    (action: QuickAction) => {
      if (action.target === 'createListing') {
        tabNavigation?.navigate('Browse', { screen: 'CreateListing' });
        return;
      }

      if (action.target === 'manageStock') {
        tabNavigation?.navigate('Browse', { screen: 'BrowseMain' });
        return;
      }

      if (action.target === 'orders') {
        tabNavigation?.navigate('Orders', { screen: 'OrdersMain' });
        return;
      }

      tabNavigation?.navigate('Messages', { screen: 'MessagesMain' });
    },
    [tabNavigation]
  );

  const openOrderDetail = useCallback(
    (order: Order) => {
      tabNavigation?.navigate('Orders', {
        screen: 'OrderDetail',
        params: { orderId: order.id },
      });
    },
    [tabNavigation]
  );

  return (
    <Screen>
      <Header
        eyebrow="Supplier workspace"
        title={`Hi, ${firstName}`}
        subtitle="Inventory, orders, and buyer enquiries at a glance."
        right={<Avatar name={userName} />}
      />

      <View style={styles.supplierHero}>
        <View style={styles.heroTop}>
          <View style={styles.heroBadge}>
            <Ionicons
              color={theme.colors.secondary}
              name="storefront-outline"
              size={16}
            />
            <Text style={styles.heroBadgeText}>Marketplace live</Text>
          </View>
          <Text style={styles.heroMetric}>
            {listingsStat?.value ?? '0'} listings
          </Text>
        </View>
        <Text style={styles.heroTitle}>Supplier dashboard</Text>
        <Text style={styles.heroSubtitle}>
          {inventoryStat?.value ?? 'NGN 0'} inventory value
        </Text>
        <View style={styles.heroStats}>
          <View style={styles.heroStat}>
            <Text style={styles.heroStatValue}>{stockStat?.value ?? '0'}</Text>
            <Text style={styles.heroStatLabel}>Stock units</Text>
          </View>
          <View style={styles.heroDivider} />
          <View style={styles.heroStat}>
            <Text style={styles.heroStatValue}>
              {messagesStat?.value ?? '0'}
            </Text>
            <Text style={styles.heroStatLabel}>Unread messages</Text>
          </View>
          <View style={styles.heroDivider} />
          <View style={styles.heroStat}>
            <Text style={styles.heroStatValue}>{openOrderCount}</Text>
            <Text style={styles.heroStatLabel}>Open orders</Text>
          </View>
        </View>
      </View>

      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Performance</Text>
        <Text style={styles.count}>{stats.length} metrics</Text>
      </View>
      <View style={styles.statsGrid}>
        {stats.map((stat, index) => (
          <View key={stat.id} style={styles.statCell}>
            <StatCard animationIndex={index} stat={stat} />
          </View>
        ))}
      </View>
      {error ? <Text style={styles.empty}>{error}</Text> : null}

      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Quick actions</Text>
        <Text style={styles.count}>{quickActions.length} tools</Text>
      </View>
      <View style={styles.actionGrid}>
        {quickActions.map((action, index) => (
          <View key={action.id} style={styles.actionCell}>
            <QuickActionCard
              action={action}
              animationIndex={index}
              layout="tile"
              onPress={handleQuickAction}
              style={styles.actionTile}
            />
          </View>
        ))}
      </View>

      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Recent orders</Text>
        <Pressable
          accessibilityRole="button"
          onPress={() =>
            tabNavigation?.navigate('Orders', { screen: 'OrdersMain' })
          }
        >
          <Text style={styles.linkText}>View all</Text>
        </Pressable>
      </View>
      {orderList.map((order, index) => (
        <OrderCard
          key={order.id}
          order={order}
          animationIndex={index}
          onPress={openOrderDetail}
        />
      ))}
    </Screen>
  );
}

type BuyerHomeProps = {
  firstName: string;
  loading: boolean;
  products: Product[];
  query: string;
  setQuery: (query: string) => void;
  error?: string;
  userName: string;
};

function BuyerHome({
  error,
  firstName,
  loading,
  products,
  query,
  setQuery,
  userName,
}: BuyerHomeProps) {
  const navigation =
    useNavigation<NativeStackNavigationProp<HomeStackParamList, 'HomeMain'>>();
  const animatedStyle = useScreenAnimation();
  const renderProduct = useCallback(
    ({ item, index }: ListRenderItemInfo<Product>) => (
      <ProductCard
        animationIndex={index}
        product={item}
        onPress={(product) =>
          navigation.navigate('ProductDetail', { productId: product.id })
        }
      />
    ),
    [navigation]
  );
  const keyExtractor = useCallback((item: Product) => item.id, []);
  const header = useMemo(
    () => (
      <>
        <Header
          eyebrow="Buyer marketplace"
          title={`Welcome, ${firstName}`}
          subtitle="Browse verified materials and suppliers for your next project."
          right={<Avatar name={userName} />}
        />

        <Input
          placeholder="Search cement, steel, tiles..."
          value={query}
          onChangeText={setQuery}
          containerStyle={styles.search}
        />

        <View style={styles.listHeader}>
          <Text style={styles.sectionTitle}>Featured products</Text>
          <Text style={styles.count}>
            {loading ? 'Loading' : `${products.length} items`}
          </Text>
        </View>
      </>
    ),
    [firstName, loading, products.length, query, setQuery, userName]
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.roleContainer, animatedStyle]}>
        <FlatList
          {...listPerformanceProps}
          data={products}
          keyExtractor={keyExtractor}
          keyboardShouldPersistTaps="handled"
          ListEmptyComponent={
            !loading ? (
              <Text style={styles.empty}>
                {error || 'No products match your search.'}
              </Text>
            ) : null
          }
          ListHeaderComponent={header}
          renderItem={renderProduct}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.flatListContent}
        />
      </Animated.View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  roleContainer: {
    flex: 1,
  },
  safeArea: {
    backgroundColor: theme.colors.background,
    flex: 1,
  },
  flatListContent: {
    padding: theme.spacing.lg,
    paddingBottom: theme.spacing.xxl,
  },
  supplierHero: {
    backgroundColor: theme.colors.primaryDark,
    borderRadius: theme.radius.md,
    marginBottom: theme.spacing.lg,
    overflow: 'hidden',
    padding: theme.spacing.lg,
  },
  heroTop: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  heroBadge: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.12)',
    borderRadius: theme.radius.pill,
    flexDirection: 'row',
    gap: theme.spacing.xs,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: 7,
  },
  heroBadgeText: {
    color: theme.colors.white,
    fontSize: 12,
    fontWeight: '900',
  },
  heroMetric: {
    color: theme.colors.white,
    fontSize: 13,
    fontWeight: '900',
  },
  heroTitle: {
    color: theme.colors.white,
    fontSize: 24,
    fontWeight: '900',
    lineHeight: 30,
    marginTop: theme.spacing.lg,
  },
  heroSubtitle: {
    color: '#DCEAFF',
    fontSize: 15,
    fontWeight: '800',
    marginTop: 5,
  },
  heroStats: {
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    borderColor: 'rgba(255, 255, 255, 0.18)',
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    marginTop: theme.spacing.lg,
    paddingVertical: theme.spacing.md,
  },
  heroStat: {
    alignItems: 'center',
    flex: 1,
    paddingHorizontal: theme.spacing.xs,
  },
  heroStatValue: {
    color: theme.colors.white,
    fontSize: 17,
    fontWeight: '900',
  },
  heroStatLabel: {
    color: '#BFD5F6',
    fontSize: 11,
    fontWeight: '800',
    marginTop: 4,
    textAlign: 'center',
    textTransform: 'uppercase',
  },
  heroDivider: {
    backgroundColor: 'rgba(255, 255, 255, 0.18)',
    width: 1,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.md,
    marginBottom: theme.spacing.lg,
  },
  statCell: {
    width: '47.8%',
  },
  sectionHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: theme.spacing.lg,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
  },
  listHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  count: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '800',
    marginBottom: theme.spacing.md,
  },
  actionGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.md,
  },
  actionCell: {
    flexBasis: '47.5%',
    flexGrow: 1,
  },
  actionTile: {
    marginBottom: 0,
  },
  linkText: {
    color: theme.colors.primary,
    fontSize: 13,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
  },
  search: {
    marginBottom: theme.spacing.md,
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    marginTop: theme.spacing.md,
    textAlign: 'center',
  },
});
