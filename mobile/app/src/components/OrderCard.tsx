import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { theme } from '../theme';
import type { Order, OrderStatus } from '../types';
import { formatCurrency } from '../utils/format';
import { Card } from './Card';

type OrderCardProps = {
  order: Order;
  animationIndex?: number;
  onPress?: (order: Order) => void;
};

const statusColors: Record<OrderStatus, { background: string; color: string }> = {
  Pending: { background: theme.colors.accentSoft, color: theme.colors.warning },
  Processing: { background: theme.colors.primarySoft, color: theme.colors.primary },
  Delivered: { background: theme.colors.secondarySoft, color: theme.colors.success },
  Cancelled: { background: '#FFE9E5', color: theme.colors.danger },
};

export const OrderCard = React.memo(function OrderCard({
  animationIndex,
  onPress,
  order,
}: OrderCardProps) {
  const status = statusColors[order.status];

  return (
    <Card animationIndex={animationIndex} style={styles.card}>
      <Pressable disabled={!onPress} onPress={() => onPress?.(order)}>
        <View style={styles.top}>
          <View style={styles.copy}>
            <Text style={styles.reference}>{order.reference}</Text>
            <Text style={styles.title}>{order.title}</Text>
          </View>
          <View style={[styles.badge, { backgroundColor: status.background }]}>
            <Text style={[styles.badgeText, { color: status.color }]}>
              {order.status}
            </Text>
          </View>
        </View>
        <View style={styles.metaRow}>
          <Text style={styles.meta}>{order.itemCount} items</Text>
          <Text style={styles.meta}>{order.placedAt}</Text>
        </View>
        <View style={styles.bottom}>
          <Text numberOfLines={1} style={styles.parties}>
            {order.buyerName} - {order.supplierName}
          </Text>
          <Text style={styles.total}>{formatCurrency(order.total)}</Text>
        </View>
      </Pressable>
    </Card>
  );
});

const styles = StyleSheet.create({
  card: {
    marginBottom: theme.spacing.md,
    padding: theme.spacing.md,
  },
  top: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  copy: {
    flex: 1,
  },
  reference: {
    color: theme.colors.primary,
    fontSize: 12,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  title: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
    lineHeight: 21,
    marginTop: 4,
  },
  badge: {
    borderRadius: theme.radius.pill,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: 6,
  },
  badgeText: {
    fontSize: 11,
    fontWeight: '900',
  },
  metaRow: {
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginTop: theme.spacing.sm,
  },
  meta: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '800',
  },
  bottom: {
    alignItems: 'flex-end',
    borderTopColor: theme.colors.border,
    borderTopWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
    marginTop: theme.spacing.md,
    paddingTop: theme.spacing.sm,
  },
  parties: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13,
    lineHeight: 19,
  },
  total: {
    color: theme.colors.text,
    fontSize: 17,
    fontWeight: '900',
  },
});
