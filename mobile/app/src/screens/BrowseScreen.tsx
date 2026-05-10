import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import React, { useCallback, useMemo, useState } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import {
  Badge,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Loader,
  ProductCard,
} from '../components';
import { useAppState } from '../context/AppContext';
import {
  useCategories,
  useProducts,
  useSupplierListings,
} from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import type { BrowseStackParamList, MainTabParamList } from '../navigation/types';
import { theme } from '../theme';
import type { Category, ListingStatus, Product, SupplierListing } from '../types';
import { formatCurrency } from '../utils/format';

const listPerformanceProps = {
  initialNumToRender: 6,
  maxToRenderPerBatch: 6,
  removeClippedSubviews: Platform.OS === 'android',
  updateCellsBatchingPeriod: 50,
  windowSize: 7,
};

type BrowseNavigation = NativeStackNavigationProp<
  BrowseStackParamList,
  'BrowseMain'
>;

export function BrowseScreen() {
  const { currentRole } = useAppState();

  if (currentRole === 'supplier') {
    return <SupplierListingsScreen />;
  }

  return <BuyerBrowseScreen />;
}

function BuyerBrowseScreen() {
  const navigation = useNavigation<BrowseNavigation>();
  const animatedStyle = useScreenAnimation();
  const [query, setQuery] = useState('');
  const [category, setCategory] = useState('All');
  const categoriesResource = useCategories();
  const productResource = useProducts('buyer', { category, search: query });

  useFocusEffect(
    useCallback(() => {
      categoriesResource.refresh();
      productResource.refresh();
    }, [categoriesResource.refresh, productResource.refresh])
  );

  const categories = useMemo<Category[]>(
    () => [
      {
        id: 'cat-all',
        name: 'All',
        slug: 'all',
        description: 'All materials',
      },
      ...categoriesResource.data,
    ],
    [categoriesResource.data]
  );

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
  const goHome = useCallback(() => {
    navigation
      .getParent<BottomTabNavigationProp<MainTabParamList>>()
      ?.navigate('Home', { screen: 'HomeMain' });
  }, [navigation]);
  const header = useMemo(
    () => (
      <>
        <Header
          eyebrow="Marketplace"
          title="Browse materials"
          subtitle="Search verified products and compare suppliers."
        />
        <Input
          placeholder="Search by product, supplier, city..."
          value={query}
          onChangeText={setQuery}
        />
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.categoryRail}
        >
          {categories.map((item) => (
            <Pressable
              key={item.id}
              onPress={() => setCategory(item.name)}
              style={styles.categoryButton}
            >
              <Badge
                label={item.name}
                tone={item.name === category ? 'primary' : 'neutral'}
              />
            </Pressable>
          ))}
        </ScrollView>
        <View style={styles.resultsBar}>
          <Text style={styles.resultsTitle}>Available materials</Text>
          <Text style={styles.resultsMeta}>
            {productResource.loading
              ? 'Loading'
              : `${productResource.data.length} results`}
          </Text>
        </View>
      </>
    ),
    [
      categories,
      category,
      productResource.data.length,
      productResource.loading,
      query,
      setQuery,
    ]
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          {...listPerformanceProps}
          data={productResource.data}
          keyExtractor={keyExtractor}
          ListEmptyComponent={
            productResource.loading ? (
              <Loader label="Loading products" />
            ) : (
              <Text style={styles.empty}>
                {productResource.error?.message || 'No matching materials found.'}
              </Text>
            )
          }
          ListHeaderComponent={header}
          renderItem={renderProduct}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.contentWithFloatingBack}
        />
      </Animated.View>
      <FloatingBackButton fallback={goHome} hideWhenUnavailable={false} />
    </SafeAreaView>
  );
}

const statusFilters: Array<ListingStatus | 'All'> = [
  'All',
  'active',
  'out_of_stock',
  'inactive',
];

const listingStatusLabels: Record<ListingStatus | 'All', string> = {
  All: 'All',
  active: 'Active',
  inactive: 'Inactive',
  out_of_stock: 'Out of stock',
};

function SupplierListingsScreen() {
  const navigation = useNavigation<BrowseNavigation>();
  const animatedStyle = useScreenAnimation();
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState<ListingStatus | 'All'>('All');
  const {
    data: listings,
    error,
    loading,
    refresh,
  } = useSupplierListings('supplier', { search: query, status });

  useFocusEffect(
    useCallback(() => {
      refresh();
    }, [refresh])
  );

  const listingSummary = useMemo(
    () => [
      { label: 'Total', value: listings.length },
      {
        label: 'Active',
        value: listings.filter((listing) => listing.status === 'active').length,
      },
      {
        label: 'Low stock',
        value: listings.filter((listing) => listing.stockCount <= 5).length,
      },
    ],
    [listings]
  );

  const renderListing = useCallback(
    ({ item, index }: ListRenderItemInfo<SupplierListing>) => (
      <SupplierListingCard animationIndex={index} listing={item} />
    ),
    []
  );
  const keyExtractor = useCallback((item: SupplierListing) => item.id, []);
  const goHome = useCallback(() => {
    navigation
      .getParent<BottomTabNavigationProp<MainTabParamList>>()
      ?.navigate('Home', { screen: 'HomeMain' });
  }, [navigation]);
  const header = useMemo(
    () => (
      <>
        <Header
          eyebrow="Supplier storefront"
          title="Listings"
          subtitle="Manage catalog, pricing, and stock availability."
        />

        <Button
          onPress={() => navigation.navigate('CreateListing')}
          title="Create listing"
          style={styles.createListingButton}
        />

        <View style={styles.summaryStrip}>
          {listingSummary.map((item, index) => (
            <View key={item.label} style={styles.summaryItem}>
              <Text style={styles.summaryValue}>{item.value}</Text>
              <Text style={styles.summaryLabel}>{item.label}</Text>
              {index < listingSummary.length - 1 ? (
                <View style={styles.summaryDivider} />
              ) : null}
            </View>
          ))}
        </View>

        <Input
          placeholder="Search your listings..."
          value={query}
          onChangeText={setQuery}
        />

        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.categoryRail}
        >
          {statusFilters.map((item) => (
            <Pressable
              key={item}
              onPress={() => setStatus(item)}
              style={styles.categoryButton}
            >
              <Badge
                label={listingStatusLabels[item]}
                tone={item === status ? 'primary' : 'neutral'}
              />
            </Pressable>
          ))}
        </ScrollView>

        <View style={styles.resultsBar}>
          <Text style={styles.resultsTitle}>Inventory</Text>
          <Text style={styles.resultsMeta}>
            {loading ? 'Loading' : `${listings.length} listings`}
          </Text>
        </View>
      </>
    ),
    [listingSummary, listings.length, loading, navigation, query, status]
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          {...listPerformanceProps}
          data={listings}
          keyExtractor={keyExtractor}
          ListEmptyComponent={
            loading ? (
              <Loader label="Loading listings" />
            ) : (
              <Text style={styles.empty}>
                {error?.message || 'No supplier listings found.'}
              </Text>
            )
          }
          ListHeaderComponent={header}
          renderItem={renderListing}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.contentWithFloatingBack}
        />
      </Animated.View>
      <FloatingBackButton fallback={goHome} hideWhenUnavailable={false} />
    </SafeAreaView>
  );
}

const SupplierListingCard = React.memo(function SupplierListingCard({
  animationIndex,
  listing,
}: {
  animationIndex: number;
  listing: SupplierListing;
}) {
  const stockTone = listing.stockCount <= 5 ? 'warning' : 'success';
  const statusTone =
    listing.status === 'active'
      ? 'success'
      : listing.status === 'inactive'
        ? 'neutral'
        : 'warning';

  return (
    <Card animationIndex={animationIndex} style={styles.listingCard}>
      <View style={styles.listingTop}>
        <View style={styles.listingCopy}>
          <Text style={styles.listingCategory}>{listing.category}</Text>
          <Text numberOfLines={2} style={styles.listingTitle}>
            {listing.name}
          </Text>
        </View>
        <Badge label={listingStatusLabels[listing.status]} tone={statusTone} />
      </View>

      <View style={styles.listingMetrics}>
        <View style={styles.listingMetric}>
          <Text style={styles.metricLabel}>Price</Text>
          <Text style={styles.metricValue}>
            {formatCurrency(listing.price)}
          </Text>
          <Text style={styles.metricMeta}>per {listing.unit}</Text>
        </View>
        <View style={styles.metricDivider} />
        <View style={styles.listingMetric}>
          <Text style={styles.metricLabel}>Stock</Text>
          <Text style={styles.metricValue}>{listing.stockCount}</Text>
          <Badge
            label={listing.stockCount <= 5 ? 'Low stock' : 'In stock'}
            tone={stockTone}
          />
        </View>
      </View>

      {listing.negotiable ? (
        <Text style={styles.negotiable}>Negotiable pricing enabled</Text>
      ) : null}
    </Card>
  );
});

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
  categoryRail: {
    gap: theme.spacing.sm,
    paddingTop: theme.spacing.md,
    paddingBottom: theme.spacing.lg,
  },
  categoryButton: {
    minHeight: 38,
    justifyContent: 'center',
  },
  resultsBar: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: theme.spacing.md,
  },
  resultsTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
  },
  resultsMeta: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '800',
  },
  createListingButton: {
    marginBottom: theme.spacing.md,
  },
  summaryStrip: {
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
  listingCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  listingTop: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  listingCopy: {
    flex: 1,
  },
  listingCategory: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  listingTitle: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
    lineHeight: 21,
    marginTop: 4,
  },
  listingMetrics: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    padding: theme.spacing.md,
  },
  listingMetric: {
    flex: 1,
    gap: 4,
  },
  metricDivider: {
    backgroundColor: theme.colors.border,
    marginHorizontal: theme.spacing.md,
    width: 1,
  },
  metricLabel: {
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  metricValue: {
    color: theme.colors.text,
    fontSize: 17,
    fontWeight: '900',
  },
  metricMeta: {
    color: theme.colors.textMuted,
    fontSize: 12,
    fontWeight: '800',
  },
  negotiable: {
    color: theme.colors.success,
    fontSize: 12,
    fontWeight: '900',
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    padding: theme.spacing.lg,
    textAlign: 'center',
  },
});
