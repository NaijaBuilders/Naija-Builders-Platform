import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useCallback, useMemo, useState } from 'react';
import {
  FlatList,
  ListRenderItemInfo,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Animated from 'react-native-reanimated';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Badge, Header, Input, Loader, ProductCard } from '../components';
import { useCategories, useProducts } from '../hooks/useMarketplaceData';
import { useScreenAnimation } from '../hooks/useScreenAnimation';
import type { BrowseStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { Category, Product } from '../types';

type BrowseNavigation = NativeStackNavigationProp<
  BrowseStackParamList,
  'BrowseMain'
>;

export function BrowseScreen() {
  const navigation = useNavigation<BrowseNavigation>();
  const animatedStyle = useScreenAnimation();
  const [query, setQuery] = useState('');
  const [category, setCategory] = useState('All');
  const categoriesResource = useCategories();
  const productResource = useProducts('buyer', { category, search: query });

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

  return (
    <SafeAreaView style={styles.safeArea}>
      <Animated.View style={[styles.container, animatedStyle]}>
        <FlatList
          data={productResource.data}
          keyExtractor={(item) => item.id}
          ListEmptyComponent={
            productResource.loading ? (
              <Loader label="Loading products" />
            ) : (
              <Text style={styles.empty}>
                {productResource.error?.message || 'No matching materials found.'}
              </Text>
            )
          }
          ListHeaderComponent={
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
          }
          renderItem={renderProduct}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.content}
        />
      </Animated.View>
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
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    padding: theme.spacing.lg,
    textAlign: 'center',
  },
});
