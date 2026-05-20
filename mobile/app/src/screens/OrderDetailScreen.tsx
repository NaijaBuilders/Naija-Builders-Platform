import React, { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import {
  Badge,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Loader,
  Screen,
} from '../components';
import { useOrder } from '../hooks/useMarketplaceData';
import { orderService } from '../services';
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
  const { data: order, error, loading, refresh } = useOrder(route.params.orderId);
  const [handoverCode, setHandoverCode] = useState('');
  const [disputeReason, setDisputeReason] = useState('');
  const [actionLoading, setActionLoading] = useState('');
  const [actionMessage, setActionMessage] = useState('');
  const [actionError, setActionError] = useState('');

  const confirmHandover = async () => {
    setActionLoading('otp');
    setActionMessage('');
    setActionError('');
    try {
      await orderService.confirmDeliveryOtp(order?.id ?? route.params.orderId, handoverCode);
      setActionMessage('Delivery confirmed.');
      setHandoverCode('');
      refresh();
    } catch (reason) {
      setActionError(reason instanceof Error ? reason.message : 'Could not confirm delivery.');
    } finally {
      setActionLoading('');
    }
  };

  const raiseDispute = async () => {
    setActionLoading('dispute');
    setActionMessage('');
    setActionError('');
    try {
      await orderService.raiseDispute(order?.id ?? route.params.orderId, disputeReason);
      setActionMessage('Dispute raised. Escrow release is on hold.');
      setDisputeReason('');
      refresh();
    } catch (reason) {
      setActionError(reason instanceof Error ? reason.message : 'Could not raise dispute.');
    } finally {
      setActionLoading('');
    }
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading order" />
      </Screen>
    );
  }

  if (!order) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Text style={styles.empty}>
          {error?.message || 'Order details could not be loaded.'}
        </Text>
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
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

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Order progress</Text>
        <Text style={styles.meta}>{reviewMessage(order.verificationStatus)}</Text>
        {order.paymentProvider ? (
          <Text style={styles.meta}>
            Payment: {providerName(order.paymentProvider)}
            {order.paymentCurrency && order.paymentAmount
              ? ` ${order.paymentCurrency} ${order.paymentAmount.toLocaleString()}`
              : ''}
          </Text>
        ) : null}
        {order.recipient?.name ? (
          <Text style={styles.meta}>
            Recipient: {order.recipient.name} ({order.recipient.phone})
          </Text>
        ) : null}
        {order.delivery?.dispute_window_ends_at ? (
          <Text style={styles.meta}>
            {disputeWindowText(order.delivery.dispute_window_ends_at)}
          </Text>
        ) : null}
      </Card>

      {order.verificationStatus === 'requires_kyc' ? (
        <Card style={styles.card}>
          <Text style={styles.sectionTitle}>More information needed</Text>
          <Text style={styles.meta}>
            We need a little more information before completing this order.
          </Text>
        </Card>
      ) : null}

      {order.delivery?.photos.length ? (
        <>
          <Text style={styles.sectionTitle}>Delivery photos</Text>
          {order.delivery.photos.map((photo, index) => (
            <Card key={photo.id || `${photo.path}-${index}`} style={styles.itemCard}>
              <Text style={styles.itemName}>Photo {index + 1}</Text>
              <Text style={styles.meta}>{photo.captured_at || 'Timestamp pending'}</Text>
              {photo.gps_lat && photo.gps_lng ? (
                <Text style={styles.meta}>
                  GPS: {photo.gps_lat}, {photo.gps_lng}
                </Text>
              ) : null}
            </Card>
          ))}
        </>
      ) : null}

      {order.delivery?.status === 'otp_sent' ? (
        <Card style={styles.card}>
          <Text style={styles.sectionTitle}>Handover code</Text>
          <Input
            keyboardType="number-pad"
            label="Code from recipient"
            onChangeText={setHandoverCode}
            value={handoverCode}
          />
          <Button
            title="Confirm handover"
            onPress={confirmHandover}
            loading={actionLoading === 'otp'}
            disabled={handoverCode.trim().length !== 6}
          />
        </Card>
      ) : null}

      {order.delivery?.dispute_status === 'window_open' ? (
        <Card style={styles.card}>
          <Text style={styles.sectionTitle}>Need help with delivery?</Text>
          <Text style={styles.meta}>
            You can raise a dispute before the countdown ends.
          </Text>
          <Input
            label="What happened?"
            multiline
            onChangeText={setDisputeReason}
            value={disputeReason}
          />
          <Button
            title="Raise dispute"
            onPress={raiseDispute}
            loading={actionLoading === 'dispute'}
            disabled={disputeReason.trim().length < 10}
            variant="outline"
          />
        </Card>
      ) : null}

      {actionMessage ? <Text style={styles.successText}>{actionMessage}</Text> : null}
      {actionError ? <Text style={styles.errorText}>{actionError}</Text> : null}

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
  contentWithFloatingBack: {
    paddingTop: 70,
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
  successText: {
    color: theme.colors.success,
    fontWeight: '800',
    lineHeight: 21,
    textAlign: 'center',
  },
  errorText: {
    color: theme.colors.danger,
    fontWeight: '800',
    lineHeight: 21,
    textAlign: 'center',
  },
});

function reviewMessage(status?: string) {
  if (status === 'requires_kyc') {
    return 'We need a little more information before completing this order.';
  }

  if (status === 'manual_review' || status === 'more_info_requested') {
    return 'Your order is being reviewed.';
  }

  if (status === 'rejected') {
    return 'This order could not be completed.';
  }

  return 'Your order is moving ahead.';
}

function providerName(provider: string) {
  return provider === 'paystack' ? 'Paystack' : provider === 'stripe' ? 'Stripe' : provider;
}

function disputeWindowText(value: string) {
  const end = new Date(value).getTime();
  const remainingMs = end - Date.now();
  const hours = Math.max(0, Math.ceil(remainingMs / (60 * 60 * 1000)));

  return `You have ${hours} hours to raise a dispute.`;
}
