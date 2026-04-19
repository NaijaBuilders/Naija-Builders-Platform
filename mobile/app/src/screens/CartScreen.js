import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to /api/mobile/cart endpoints (Mobile/CartController).
export default function CartScreen() {
  const [items, setItems] = useState([]);
  const [total, setTotal] = useState(0);
  const [error, setError] = useState('');

  const loadCart = async () => {
    try {
      const response = await api.getCart();
      setItems(response.data.items || []);
      setTotal(response.data.total || 0);
    } catch (err) {
      setError('Unable to load cart.');
    }
  };

  useEffect(() => {
    loadCart();
  }, []);

  const updateQuantity = async (materialId, nextQuantity) => {
    try {
      await api.updateCartItem(materialId, { quantity: nextQuantity });
      await loadCart();
    } catch (err) {
      setError('Unable to update item.');
    }
  };

  const removeItem = async (materialId) => {
    try {
      await api.removeCartItem(materialId);
      await loadCart();
    } catch (err) {
      setError('Unable to remove item.');
    }
  };

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Your Cart</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {items.length === 0 ? <Text style={styles.meta}>Cart is empty.</Text> : null}
      {items.map((item) => (
        <View key={item.id} style={styles.card}>
          <Text style={styles.cardTitle}>{item.name}</Text>
          <Text style={styles.meta}>Supplier: {item.company || 'Supplier'}</Text>
          <Text style={styles.meta}>Price: NGN {item.price}</Text>
          <View style={styles.row}>
            <Pressable
              style={styles.qtyButton}
              onPress={() => updateQuantity(item.id, Math.max(1, item.quantity - 1))}
            >
              <Text style={styles.qtyText}>-</Text>
            </Pressable>
            <Text style={styles.qtyValue}>{item.quantity}</Text>
            <Pressable style={styles.qtyButton} onPress={() => updateQuantity(item.id, item.quantity + 1)}>
              <Text style={styles.qtyText}>+</Text>
            </Pressable>
            <Pressable style={styles.removeButton} onPress={() => removeItem(item.id)}>
              <Text style={styles.removeText}>Remove</Text>
            </Pressable>
          </View>
        </View>
      ))}
      <View style={styles.totalCard}>
        <Text style={styles.totalLabel}>Total</Text>
        <Text style={styles.totalValue}>NGN {total}</Text>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
    marginBottom: 12,
  },
  card: {
    padding: 16,
    marginBottom: 12,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  cardTitle: {
    fontFamily: fonts.heading,
    fontSize: 16,
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
  removeButton: {
    marginLeft: 'auto',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 10,
    backgroundColor: '#F6DADA',
  },
  removeText: {
    color: '#8B2F2F',
  },
  totalCard: {
    padding: 16,
    backgroundColor: colors.highlight,
    borderRadius: 16,
    marginTop: 8,
  },
  totalLabel: {
    color: colors.muted,
  },
  totalValue: {
    fontSize: 18,
    fontWeight: '700',
    color: colors.ink,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
});
