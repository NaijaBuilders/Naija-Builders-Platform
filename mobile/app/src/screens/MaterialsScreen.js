import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, EmptyState, ErrorBanner, LoadingState, ProductCard, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';

// Maps to GET /api/mobile/materials (Mobile/MaterialsController@index).
export default function MaterialsScreen({ navigation }) {
  const [materials, setMaterials] = useState([]);
  const [meta, setMeta] = useState(null);
  const [lookups, setLookups] = useState({ categories: [], locations: [], price_units: [] });
  const [savedIds, setSavedIds] = useState([]);
  const [search, setSearch] = useState('');
  const [sortBy, setSortBy] = useState('newest');
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadMaterials = async (nextPage = page, options = {}) => {
    if (options.refreshing) {
      setIsRefreshing(true);
    } else {
      setIsLoading(true);
    }
    setError('');

    try {
      const response = await api.getMaterials({
        page: nextPage,
        search,
        sort_by: sortBy,
        per_page: 12,
      });
      setMaterials(response.data.data || []);
      setMeta(response.data.meta || null);
      setLookups(response.data.lookups || {});
      setSavedIds(response.data.saved_material_ids || []);
      setPage(nextPage);
    } catch (err) {
      const message = err?.response?.data?.message || 'Unable to load materials.';
      setError(message);
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadMaterials(1);
  }, [sortBy]);

  const handleSearch = () => loadMaterials(1);

  const handleSave = async (item) => {
    try {
      await api.saveProduct({ material_id: item.id });
      setSavedIds((current) => (current.includes(item.id) ? current : [...current, item.id]));
    } catch (err) {
      setError(err?.response?.data?.message || 'Sign in to save products.');
    }
  };

  const handleCart = async (item) => {
    try {
      await api.addCartItem({ material_id: item.id, quantity: 1 });
      navigation.navigate('Cart');
    } catch (err) {
      setError(err?.response?.data?.message || 'Sign in to add products to cart.');
    }
  };

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadMaterials(1, { refreshing: true })}>
      <ScreenHeader title="Materials Marketplace" subtitle="Browse active listings from suppliers across Nigeria." />
      <View style={styles.searchCard}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search cement, steel, gravel..."
          placeholderTextColor={colors.mutedSoft}
          value={search}
          onChangeText={setSearch}
          returnKeyType="search"
          onSubmitEditing={handleSearch}
        />
        <AppButton label="Search" onPress={handleSearch} size="sm" />
      </View>

      <View style={styles.sortRow}>
        {[
          ['newest', 'Newest'],
          ['price_low', 'Price Low'],
          ['price_high', 'Price High'],
          ['top_sellers', 'Top Rated'],
        ].map(([value, label]) => (
          <Pressable
            key={value}
            onPress={() => setSortBy(value)}
            style={[styles.sortChip, sortBy === value ? styles.sortChipActive : null]}
          >
            <Text style={[styles.sortText, sortBy === value ? styles.sortTextActive : null]}>{label}</Text>
          </Pressable>
        ))}
      </View>

      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading materials..." /> : null}
      {!isLoading && materials.length === 0 ? (
        <EmptyState title="No Materials Found" body="Try a different search or clear your filters." actionLabel="Clear Search" onAction={() => { setSearch(''); loadMaterials(1); }} />
      ) : null}

      {!isLoading && materials.map((item) => (
        <ProductCard
          key={item.id}
          item={item}
          isSaved={savedIds.includes(item.id)}
          onPress={() => navigation.navigate('MaterialDetail', { materialId: item.id })}
          onSave={() => handleSave(item)}
          onCart={() => handleCart(item)}
        />
      ))}

      {meta && meta.last_page > 1 ? (
        <View style={styles.pagination}>
          <AppButton label="Previous" variant="outline" size="sm" disabled={page <= 1} onPress={() => loadMaterials(page - 1)} />
          <Text style={styles.metaText}>Page {meta.current_page} of {meta.last_page}</Text>
          <AppButton label="Next" variant="outline" size="sm" disabled={page >= meta.last_page} onPress={() => loadMaterials(page + 1)} />
        </View>
      ) : null}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  searchCard: {
    alignItems: 'center',
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderRadius: 16,
    borderWidth: 1,
    flexDirection: 'row',
    gap: spacing.sm,
    marginBottom: spacing.sm,
    padding: 8,
  },
  searchInput: {
    color: colors.ink,
    flex: 1,
    paddingHorizontal: 8,
    paddingVertical: 9,
  },
  sortRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: spacing.md,
  },
  sortChip: {
    backgroundColor: colors.card,
    borderColor: colors.border,
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: 11,
    paddingVertical: 7,
  },
  sortChipActive: {
    backgroundColor: colors.accentSoft,
    borderColor: colors.accent,
  },
  sortText: {
    color: colors.muted,
    fontSize: 12,
    fontWeight: '700',
  },
  sortTextActive: {
    color: colors.accent,
  },
  pagination: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: spacing.sm,
  },
  metaText: {
    textAlign: 'center',
    color: colors.muted,
  },
});
