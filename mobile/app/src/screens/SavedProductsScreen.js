import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, EmptyState, ErrorBanner, LoadingState, ProductCard, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';

// Maps to /api/mobile/saved-products (Mobile/SavedMaterialController).
export default function SavedProductsScreen({ navigation }) {
  const [saved, setSaved] = useState([]);
  const [categories, setCategories] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadSaved = async (refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getSavedProducts();
      setSaved(response.data.saved_materials || []);
      setCategories(response.data.saved_categories || []);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load saved products.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadSaved();
  }, []);

  const handleRemove = async (item) => {
    try {
      await api.removeSavedProduct(item.material_id);
      setSaved((current) => current.filter((entry) => entry.material_id !== item.material_id));
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to remove saved product.');
    }
  };

  const handleCart = async (item) => {
    try {
      await api.addCartItem({ material_id: item.material_id, quantity: 1 });
      navigation.navigate('Cart');
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to add item to cart.');
    }
  };

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadSaved(true)}>
      <ScreenHeader title="Saved Products" subtitle="Your project shortlist, grouped into collections from the website." />
      <ErrorBanner message={error} />
      {categories.length > 0 ? <Text style={styles.collectionCount}>{categories.length} collection{categories.length === 1 ? '' : 's'}</Text> : null}
      {isLoading ? <LoadingState label="Loading saved products..." /> : null}
      {!isLoading && saved.length === 0 ? <EmptyState title="No saved products yet" body="Use Save Product on materials you want to revisit." actionLabel="Browse Materials" onAction={() => navigation.navigate('Materials')} /> : null}
      {saved.map((item) => (
        <View key={item.material_id} style={styles.savedWrap}>
          <ProductCard
            item={{ ...item, id: item.material_id }}
            isSaved
            onPress={() => navigation.navigate('MaterialDetail', { materialId: item.material_id })}
            onCart={() => handleCart(item)}
          />
          <View style={styles.savedActions}>
            <Text style={styles.collectionText}>Saved to: {item.saved_category_name || 'General Saves'}</Text>
            <AppButton label="Remove" variant="danger" size="sm" onPress={() => handleRemove(item)} />
          </View>
        </View>
      ))}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  collectionCount: {
    color: colors.muted,
    marginBottom: spacing.sm,
  },
  savedWrap: {
    marginBottom: spacing.md,
  },
  savedActions: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: spacing.sm,
    justifyContent: 'space-between',
    marginTop: -8,
    paddingHorizontal: spacing.sm,
  },
  collectionText: {
    color: colors.muted,
    flex: 1,
    fontSize: 12,
  },
});
