import React, { useEffect, useState } from 'react';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Card, EmptyState, ErrorBanner, LoadingState, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, radius, spacing } from '../styles/theme';
import { formatMoney, imageUrl } from '../utils/format';

// Maps to /api/mobile/cart endpoints (Mobile/CartController).
export default function CartScreen() {
  const [items, setItems] = useState([]);
  const [total, setTotal] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [notice, setNotice] = useState('');
  const [error, setError] = useState('');

  const loadCart = async (refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getCart();
      setItems(response.data.items || []);
      setTotal(response.data.total || 0);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load cart.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadCart();
  }, []);

  const updateQuantity = async (materialId, nextQuantity) => {
    try {
      await api.updateCartItem(materialId, { quantity: nextQuantity });
      await loadCart(true);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to update item.');
    }
  };

  const removeItem = async (materialId) => {
    try {
      await api.removeCartItem(materialId);
      await loadCart(true);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to remove item.');
    }
  };

  const handleCheckout = () => {
    setNotice('Checkout, payment routing, escrow, and delivery confirmation are next backend-backed mobile phases.');
  };

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadCart(true)}>
      <ScreenHeader title="Your Cart" subtitle="Review quantities before checkout." />
      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading cart..." /> : null}
      {!isLoading && items.length === 0 ? <EmptyState title="Cart is empty" body="Add materials from the marketplace when you are ready to compare prices." /> : null}
      {items.map((item) => (
        <Card key={item.id} style={styles.cartItem}>
          <View style={styles.itemRow}>
            {imageUrl(item.image_path) ? <Image source={{ uri: imageUrl(item.image_path) }} style={styles.itemImage} /> : <View style={styles.imageFallback}><Text style={styles.imageFallbackText}>NB</Text></View>}
            <View style={styles.itemBody}>
              <Text style={styles.cardTitle}>{item.name}</Text>
              <Text style={styles.meta}>Supplier: {item.company || 'Supplier'}</Text>
              <Text style={styles.meta}>Price: {formatMoney(item.price)}</Text>
            </View>
          </View>
          <View style={styles.row}>
            <Pressable style={styles.qtyButton} onPress={() => updateQuantity(item.id, Math.max(1, item.quantity - 1))}>
              <Text style={styles.qtyText}>-</Text>
            </Pressable>
            <Text style={styles.qtyValue}>{item.quantity}</Text>
            <Pressable style={styles.qtyButton} onPress={() => updateQuantity(item.id, item.quantity + 1)}>
              <Text style={styles.qtyText}>+</Text>
            </Pressable>
            <Text style={styles.lineTotal}>{formatMoney(item.line_total)}</Text>
            <Pressable style={styles.removeButton} onPress={() => removeItem(item.id)}>
              <Text style={styles.removeText}>Remove</Text>
            </Pressable>
          </View>
        </Card>
      ))}
      <Card style={styles.totalCard}>
        <Text style={styles.totalLabel}>Total</Text>
        <Text style={styles.totalValue}>{formatMoney(total)}</Text>
        <AppButton label="Proceed to Checkout" onPress={handleCheckout} disabled={items.length === 0} />
        {notice ? <Text style={styles.notice}>{notice}</Text> : null}
      </Card>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  cartItem: {
    marginBottom: spacing.md,
  },
  itemRow: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  itemImage: {
    backgroundColor: '#EEE6DC',
    borderRadius: radius.sm,
    height: 82,
    width: 82,
  },
  imageFallback: {
    alignItems: 'center',
    backgroundColor: colors.accentSoft,
    borderRadius: radius.sm,
    height: 82,
    justifyContent: 'center',
    width: 82,
  },
  imageFallbackText: {
    color: colors.accent,
    fontWeight: '800',
  },
  itemBody: {
    flex: 1,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: colors.ink,
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  row: {
    marginTop: 10,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    flexWrap: 'wrap',
  },
  qtyButton: {
    width: 34,
    height: 34,
    borderRadius: 8,
    backgroundColor: colors.accentSoft,
    alignItems: 'center',
    justifyContent: 'center',
  },
  qtyText: {
    fontWeight: '700',
    color: colors.accent,
  },
  qtyValue: {
    minWidth: 24,
    textAlign: 'center',
    fontWeight: '600',
  },
  lineTotal: {
    color: colors.ink,
    fontWeight: '800',
    marginLeft: 'auto',
  },
  removeButton: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 10,
    backgroundColor: '#F6DADA',
  },
  removeText: {
    color: '#8B2F2F',
  },
  totalCard: {
    marginTop: 8,
    gap: spacing.sm,
  },
  totalLabel: {
    color: colors.muted,
  },
  totalValue: {
    fontSize: 24,
    fontWeight: '700',
    color: colors.ink,
  },
  notice: {
    color: colors.muted,
    lineHeight: 20,
  },
});
