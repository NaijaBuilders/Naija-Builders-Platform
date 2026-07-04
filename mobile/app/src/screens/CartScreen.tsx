import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import React, { useCallback, useState } from 'react';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import { useCart } from '../context/CartContext';
import type { HomeStackParamList } from '../navigation/types';
import { assetSource } from '../services/adapters';
import { theme } from '../theme';
import { formatCurrency } from '../utils/format';
import type { CartItem } from '../types';

type CartNavigation = NativeStackNavigationProp<HomeStackParamList, 'Cart'>;

export function CartScreen() {
  const navigation = useNavigation<CartNavigation>();
  const { cart, loading, updateQuantity, removeFromCart } = useCart();
  const [busyItemId, setBusyItemId] = useState('');

  const changeQuantity = useCallback(
    async (item: CartItem, nextQuantity: number) => {
      setBusyItemId(item.id);
      try {
        if (nextQuantity < 1) {
          await removeFromCart(item.id);
        } else {
          await updateQuantity(item.id, nextQuantity);
        }
      } catch {
        // Cart state stays as-is when the server rejects the change.
      } finally {
        setBusyItemId('');
      }
    },
    [removeFromCart, updateQuantity]
  );

  if (loading && cart.items.length === 0) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading cart" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Checkout"
        title="Your cart"
        subtitle={
          cart.items.length > 0
            ? `${cart.items.length} material${cart.items.length === 1 ? '' : 's'} ready for checkout.`
            : 'Materials you add will appear here.'
        }
      />

      {cart.items.length === 0 ? (
        <Card style={styles.emptyCard}>
          <View style={styles.emptyIcon}>
            <Ionicons color={theme.colors.primary} name="cart-outline" size={30} />
          </View>
          <Text style={styles.emptyTitle}>Your cart is empty</Text>
          <Text style={styles.emptyText}>
            Browse materials and tap “Add to cart” to start an order.
          </Text>
        </Card>
      ) : (
        <>
          {cart.items.map((item) => (
            <Card key={item.id} style={styles.itemCard}>
              <View style={styles.itemRow}>
                <Image
                  resizeMode="cover"
                  source={assetSource(item.imagePath)}
                  style={styles.itemImage}
                />
                <View style={styles.itemCopy}>
                  <Text numberOfLines={1} style={styles.itemName}>
                    {item.name}
                  </Text>
                  <Text numberOfLines={1} style={styles.itemCompany}>
                    {item.company || item.category}
                  </Text>
                  <Text style={styles.itemPrice}>{formatCurrency(item.price)}</Text>
                </View>
                <Pressable
                  accessibilityRole="button"
                  hitSlop={8}
                  onPress={() => changeQuantity(item, 0)}
                  style={styles.removeButton}
                >
                  <Ionicons
                    color={theme.colors.danger}
                    name="trash-outline"
                    size={18}
                  />
                </Pressable>
              </View>
              <View style={styles.quantityRow}>
                <View style={styles.stepper}>
                  <Pressable
                    accessibilityRole="button"
                    disabled={busyItemId === item.id}
                    onPress={() => changeQuantity(item, item.quantity - 1)}
                    style={styles.stepButton}
                  >
                    <Ionicons color={theme.colors.text} name="remove" size={18} />
                  </Pressable>
                  <Text style={styles.stepValue}>{item.quantity}</Text>
                  <Pressable
                    accessibilityRole="button"
                    disabled={
                      busyItemId === item.id || item.quantity >= item.stockQty
                    }
                    onPress={() => changeQuantity(item, item.quantity + 1)}
                    style={styles.stepButton}
                  >
                    <Ionicons color={theme.colors.text} name="add" size={18} />
                  </Pressable>
                </View>
                <Text style={styles.lineTotal}>{formatCurrency(item.lineTotal)}</Text>
              </View>
            </Card>
          ))}

          <Card style={styles.summaryCard}>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Subtotal</Text>
              <Text style={styles.summaryValue}>{formatCurrency(cart.total)}</Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Delivery</Text>
              <Text style={styles.summaryMuted}>Confirmed with supplier</Text>
            </View>
            <View style={[styles.summaryRow, styles.summaryTotalRow]}>
              <Text style={styles.summaryTotalLabel}>Total</Text>
              <Text style={styles.summaryTotal}>{formatCurrency(cart.total)}</Text>
            </View>
            <Button
              onPress={() => navigation.navigate('Checkout')}
              size="lg"
              title="Continue to checkout"
            />
          </Card>
        </>
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  emptyCard: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.xl,
  },
  emptyIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    height: 64,
    justifyContent: 'center',
    marginBottom: theme.spacing.xs,
    width: 64,
  },
  emptyTitle: {
    color: theme.colors.text,
    fontSize: 17,
    fontWeight: '900',
  },
  emptyText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    textAlign: 'center',
  },
  itemCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  itemRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  itemImage: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    height: 58,
    width: 58,
  },
  itemCopy: {
    flex: 1,
  },
  itemName: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  itemCompany: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '800',
    marginTop: 2,
  },
  itemPrice: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '700',
    marginTop: 2,
  },
  removeButton: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.md,
    height: 34,
    justifyContent: 'center',
    width: 34,
  },
  quantityRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  stepper: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.pill,
    flexDirection: 'row',
    gap: theme.spacing.md,
    paddingHorizontal: theme.spacing.xs,
    paddingVertical: theme.spacing.xs,
  },
  stepButton: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderRadius: theme.radius.pill,
    height: 30,
    justifyContent: 'center',
    width: 30,
  },
  stepValue: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
    minWidth: 24,
    textAlign: 'center',
  },
  lineTotal: {
    color: theme.colors.primaryDark,
    fontSize: 16,
    fontWeight: '900',
  },
  summaryCard: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.sm,
  },
  summaryRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  summaryLabel: {
    color: theme.colors.textMuted,
    fontSize: 14,
    fontWeight: '700',
  },
  summaryValue: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '900',
  },
  summaryMuted: {
    color: theme.colors.textSubtle,
    fontSize: 13,
    fontWeight: '700',
  },
  summaryTotalRow: {
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    paddingTop: theme.spacing.md,
  },
  summaryTotalLabel: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  summaryTotal: {
    color: theme.colors.primaryDark,
    fontSize: 20,
    fontWeight: '900',
  },
});
