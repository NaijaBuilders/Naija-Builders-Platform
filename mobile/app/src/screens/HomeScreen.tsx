import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useCallback, useMemo, useState } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
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
import type { HomeStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { DashboardStat, Order, Product, QuickAction } from '../types';

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

function SupplierHome({
  error,
  firstName,
  orderList,
  quickActions,
  stats,
  userName,
}: SupplierHomeProps) {
  return (
    <Screen>
      <Header
        eyebrow="Supplier dashboard"
        title={`Hi, ${firstName}`}
        subtitle="Track orders, inventory, and buyer activity from one place."
        right={<Avatar name={userName} />}
      />

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
      {quickActions.map((action, index) => (
        <QuickActionCard key={action.id} action={action} animationIndex={index} />
      ))}

      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Recent orders</Text>
        <Text style={styles.count}>{orderList.length} recent</Text>
      </View>
      {orderList.map((order, index) => (
        <OrderCard key={order.id} order={order} animationIndex={index} />
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

        <View style={styles.summaryStrip}>
          <View style={styles.summaryItem}>
            <Text style={styles.summaryValue}>{products.length}</Text>
            <Text style={styles.summaryLabel}>Materials</Text>
          </View>
          <View style={styles.summaryDivider} />
          <View style={styles.summaryItem}>
            <Text style={styles.summaryValue}>
              {new Set(products.map((product) => product.category)).size}
            </Text>
            <Text style={styles.summaryLabel}>Categories</Text>
          </View>
          <View style={styles.summaryDivider} />
          <View style={styles.summaryItem}>
            <Text style={styles.summaryValue}>
              {products.filter((product) => product.inStock).length}
            </Text>
            <Text style={styles.summaryLabel}>In stock</Text>
          </View>
        </View>

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
  search: {
    marginBottom: theme.spacing.md,
  },
  summaryStrip: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    marginBottom: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  summaryItem: {
    alignItems: 'center',
    flex: 1,
  },
  summaryDivider: {
    backgroundColor: theme.colors.border,
    height: 34,
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
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    marginTop: theme.spacing.md,
    textAlign: 'center',
  },
});
