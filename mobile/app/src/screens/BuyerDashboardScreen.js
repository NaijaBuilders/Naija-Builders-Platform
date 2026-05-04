import React, { useContext, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { EmptyState, ErrorBanner, LoadingState, MetricCard, ProductCard, QuickAction, ScreenHeader } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';
import { formatMoney } from '../utils/format';

// Maps to GET /api/mobile/dashboard/buyer (Mobile/DashboardController@buyer).
export default function BuyerDashboardScreen({ navigation }) {
  const { user } = useContext(AuthContext);
  const [metrics, setMetrics] = useState(null);
  const [recommended, setRecommended] = useState([]);
  const [savedIds, setSavedIds] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadBuyerDashboard = async (refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getBuyerDashboard();
      setMetrics(response.data.metrics || null);
      setRecommended(response.data.recommended_materials || []);
      setSavedIds(response.data.saved_material_ids || []);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load buyer dashboard.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadBuyerDashboard();
  }, []);

  const handleSave = async (item) => {
    try {
      await api.saveProduct({ material_id: item.id });
      setSavedIds((current) => (current.includes(item.id) ? current : [...current, item.id]));
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to save product.');
    }
  };

  const handleCart = async (item) => {
    try {
      await api.addCartItem({ material_id: item.id, quantity: 1 });
      navigation.navigate('Cart');
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to add item to cart.');
    }
  };

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadBuyerDashboard(true)}>
      <ScreenHeader title={`Hi, ${user?.name || 'Buyer'}`} subtitle="Track your cart, saved materials, orders, and supplier conversations." />
      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading buyer dashboard..." /> : null}
      {metrics ? (
        <View style={styles.metricGrid}>
          <MetricCard label="Cart Items" value={metrics.cart_item_count} tone="accent" />
          <MetricCard label="Orders" value={metrics.order_count} />
          <MetricCard label="Pending Orders" value={metrics.pending_orders} tone="warning" />
          <MetricCard label="Unread Messages" value={metrics.unread_messages} />
          <MetricCard label="Total Spent" value={formatMoney(metrics.total_spent)} tone="success" />
        </View>
      ) : null}

      <View style={styles.quickGrid}>
        <QuickAction label="Browse Materials" detail="Find current listings." onPress={() => navigation.navigate('Materials')} />
        <QuickAction label="Cart" detail="Review quantities." onPress={() => navigation.navigate('Cart')} />
        <QuickAction label="Saved Products" detail="Open your shortlist." onPress={() => navigation.navigate('SavedProducts')} />
        <QuickAction label="Messages" detail="Talk to suppliers." onPress={() => navigation.navigate('Messages')} />
      </View>

      <Text style={styles.sectionTitle}>Recommended Materials</Text>
      {!isLoading && recommended.length === 0 ? <EmptyState title="No recommendations yet" body="Browse materials to discover products for your project." /> : null}
      {recommended.map((item) => (
        <ProductCard
          key={item.id}
          item={item}
          isSaved={savedIds.includes(item.id)}
          onPress={() => navigation.navigate('MaterialDetail', { materialId: item.id })}
          onSave={() => handleSave(item)}
          onCart={() => handleCart(item)}
        />
      ))}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  metricGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    color: colors.ink,
    fontSize: 19,
    fontWeight: '800',
    marginBottom: spacing.sm,
  },
});
