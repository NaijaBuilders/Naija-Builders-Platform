import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Badge, Card, Header, Loader, Screen } from '../components';
import { useOrder } from '../hooks/useMarketplaceData';
import { theme } from '../theme';
import { formatCurrency } from '../utils/format';

type OrderDetailScreenProps = {
  route: {
    params: {
      orderId: string;
    };
  };
};

export function OrderDetailScreen({ route }: OrderDetailScreenProps) {
  const { data: order, error, loading } = useOrder(route.params.orderId);

  if (loading) {
    return (
      <Screen scroll={false} contentContainerStyle={styles.center}>
        <Loader label="Loading order" />
      </Screen>
    );
  }

  if (!order) {
    return (
      <Screen scroll={false} contentContainerStyle={styles.center}>
        <Text style={styles.empty}>
          {error?.message || 'Order details could not be loaded.'}
        </Text>
      </Screen>
    );
  }

  return (
    <Screen>
      <Header
        eyebrow={order.reference}
        title={order.title}
        subtitle={`${order.buyerName} - ${order.supplierName}`}
      />

      <Card style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.total}>{formatCurrency(order.total)}</Text>
          <Badge label={order.status} tone={order.status === 'Delivered' ? 'success' : 'primary'} />
        </View>
        <View style={styles.detailGrid}>
          <View style={styles.detailCell}>
            <Text style={styles.detailLabel}>Items</Text>
            <Text style={styles.detailValue}>{order.itemCount}</Text>
          </View>
          <View style={styles.detailCell}>
            <Text style={styles.detailLabel}>Placed</Text>
            <Text style={styles.detailValue}>{order.placedAt}</Text>
          </View>
        </View>
        <Text style={styles.meta}>Delivery: {order.delivery_address}</Text>
        <Text style={styles.meta}>Created: {order.created_at}</Text>
      </Card>

      <Text style={styles.sectionTitle}>Items</Text>
      {order.items.map((item) => (
        <Card key={item.id} style={styles.itemCard}>
          <View style={styles.itemRow}>
            <View style={styles.itemCopy}>
              <Text style={styles.itemName}>{item.name}</Text>
              <Text style={styles.meta}>
                {item.quantity} x {formatCurrency(item.unit_price)}
              </Text>
            </View>
            <Text style={styles.itemTotal}>
              {formatCurrency(item.quantity * item.unit_price)}
            </Text>
          </View>
        </Card>
      ))}
    </Screen>
  );
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  card: {
    gap: theme.spacing.md,
  },
  row: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  total: {
    color: theme.colors.primaryDark,
    fontSize: 24,
    fontWeight: '900',
  },
  detailGrid: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  detailCell: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    flex: 1,
    padding: theme.spacing.md,
  },
  detailLabel: {
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  detailValue: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
    marginTop: 4,
  },
  meta: {
    color: theme.colors.textMuted,
    lineHeight: 21,
  },
  empty: {
    color: theme.colors.textMuted,
    lineHeight: 22,
    textAlign: 'center',
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
    marginTop: theme.spacing.lg,
  },
  itemCard: {
    marginBottom: theme.spacing.md,
  },
  itemRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  itemCopy: {
    flex: 1,
  },
  itemName: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  itemTotal: {
    color: theme.colors.primaryDark,
    fontSize: 15,
    fontWeight: '900',
  },
});
