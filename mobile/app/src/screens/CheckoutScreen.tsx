import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import React, { useEffect, useState } from 'react';
import { Modal, Pressable, StyleSheet, Text, View } from 'react-native';
import Animated, { FadeIn, ZoomIn } from 'react-native-reanimated';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Screen,
} from '../components';
import { useAppState } from '../context/AppContext';
import { useCart } from '../context/CartContext';
import type { HomeStackParamList, MainTabParamList } from '../navigation/types';
import { addressService, cartService, orderService } from '../services';
import { theme } from '../theme';
import type { DeliveryAddress, PaymentMethodChoice, PlaceOrderResult } from '../types';
import { formatCurrency } from '../utils/format';
import { haptics } from '../utils/haptics';

type CheckoutNavigation = NativeStackNavigationProp<
  HomeStackParamList,
  'Checkout'
>;

const PAYMENT_METHODS: Array<{
  value: PaymentMethodChoice;
  label: string;
  description: string;
  icon: React.ComponentProps<typeof Ionicons>['name'];
}> = [
  {
    value: 'pay_on_delivery',
    label: 'Pay on delivery',
    description: 'Pay cash or transfer when the materials arrive.',
    icon: 'cash-outline',
  },
  {
    value: 'bank_transfer',
    label: 'Bank transfer',
    description: 'Transfer to the supplier account before dispatch.',
    icon: 'swap-horizontal-outline',
  },
  {
    value: 'card',
    label: 'Card (Paystack)',
    description: 'Secure card payment. Activating soon.',
    icon: 'card-outline',
  },
];

export function CheckoutScreen() {
  const navigation = useNavigation<CheckoutNavigation>();
  const { user } = useAppState();
  const { cart, refreshCart } = useCart();

  const [addresses, setAddresses] = useState<DeliveryAddress[]>([]);
  const [selectedAddressId, setSelectedAddressId] = useState('');
  const [useNewAddress, setUseNewAddress] = useState(false);
  const [newAddress, setNewAddress] = useState('');
  const [newState, setNewState] = useState('');
  const [saveNewAddress, setSaveNewAddress] = useState(true);
  const [recipientName, setRecipientName] = useState(user?.name ?? '');
  const [recipientPhone, setRecipientPhone] = useState(user?.phone ?? '');
  const [paymentMethod, setPaymentMethod] =
    useState<PaymentMethodChoice>('pay_on_delivery');
  const [placing, setPlacing] = useState(false);
  const [error, setError] = useState('');
  const [successOrder, setSuccessOrder] = useState<PlaceOrderResult | null>(null);

  useEffect(() => {
    let mounted = true;

    addressService
      .list()
      .then((saved) => {
        if (!mounted) {
          return;
        }

        setAddresses(saved);
        const preferred = saved.find((address) => address.isDefault) ?? saved[0];
        if (preferred) {
          setSelectedAddressId(preferred.id);
        } else {
          setUseNewAddress(true);
        }
      })
      .catch(() => {
        if (mounted) {
          setUseNewAddress(true);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

  const resolveDeliveryAddress = (): string => {
    if (!useNewAddress) {
      const selected = addresses.find(
        (address) => address.id === selectedAddressId
      );
      if (selected) {
        return [selected.address, selected.lga, selected.state]
          .filter(Boolean)
          .join(', ');
      }
    }

    return [newAddress.trim(), newState.trim()].filter(Boolean).join(', ');
  };

  const placeOrder = async () => {
    setError('');
    const deliveryAddress = resolveDeliveryAddress();

    if (cart.items.length === 0) {
      setError('Your cart is empty.');
      return;
    }

    if (deliveryAddress.length < 5) {
      setError('Please choose or enter a delivery address.');
      return;
    }

    if (recipientName.trim().length < 2 || recipientPhone.trim().length < 7) {
      setError('Please enter the recipient name and phone number.');
      return;
    }

    setPlacing(true);
    try {
      if (useNewAddress && saveNewAddress && newAddress.trim().length >= 5) {
        await addressService
          .create({
            address: newAddress.trim(),
            state: newState.trim() || undefined,
            label: 'Site address',
            contactName: recipientName.trim(),
            contactPhone: recipientPhone.trim(),
          })
          .catch(() => undefined);
      }

      const result = await orderService.placeOrder({
        items: cart.items.map((item) => ({
          materialId: item.id,
          quantity: item.quantity,
        })),
        deliveryAddress,
        recipientName: recipientName.trim(),
        recipientPhone: recipientPhone.trim(),
        paymentMethodType: paymentMethod,
      });

      for (const item of cart.items) {
        // The server cart is cleared item-by-item; failures are harmless
        // because checkout already succeeded.
        await cartService.removeItem(item.id).catch(() => undefined);
      }
      await refreshCart();

      haptics.success();
      setSuccessOrder(result);
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not place the order.'
      );
    } finally {
      setPlacing(false);
    }
  };

  const viewOrder = () => {
    const orderId = successOrder?.orderId ?? '';
    setSuccessOrder(null);
    navigation.popToTop();
    navigation
      .getParent<BottomTabNavigationProp<MainTabParamList>>()
      ?.navigate('Orders', {
        screen: 'OrderDetail',
        params: { orderId },
      });
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Modal animationType="fade" transparent visible={successOrder !== null}>
        <View style={styles.successBackdrop}>
          <Animated.View entering={FadeIn.duration(150)} style={styles.successCard}>
            <Animated.View
              entering={ZoomIn.springify().damping(11).delay(120)}
              style={styles.successRing}
            >
              <View style={styles.successCircle}>
                <Ionicons color={theme.colors.white} name="checkmark" size={44} />
              </View>
            </Animated.View>
            <Text style={styles.successTitle}>Order placed!</Text>
            <Text style={styles.successReference}>
              {successOrder?.reference ?? ''}
            </Text>
            <Text style={styles.successMessage}>
              {successOrder?.message ??
                'The supplier has been notified and will confirm shortly.'}
            </Text>
            <Button onPress={viewOrder} size="lg" title="Track my order" />
          </Animated.View>
        </View>
      </Modal>
      <Header
        eyebrow="Checkout"
        title="Delivery & payment"
        subtitle="Confirm where the materials go and how you want to pay."
      />

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Delivery address</Text>

        {addresses.map((address) => {
          const active = !useNewAddress && selectedAddressId === address.id;

          return (
            <Pressable
              accessibilityRole="button"
              key={address.id}
              onPress={() => {
                setSelectedAddressId(address.id);
                setUseNewAddress(false);
              }}
              style={[styles.addressOption, active ? styles.optionActive : null]}
            >
              <Ionicons
                color={active ? theme.colors.primary : theme.colors.textSubtle}
                name={active ? 'radio-button-on' : 'radio-button-off'}
                size={20}
              />
              <View style={styles.addressCopy}>
                <Text style={styles.addressLabel}>
                  {address.label || 'Saved address'}
                  {address.isDefault ? '  ·  Default' : ''}
                </Text>
                <Text numberOfLines={2} style={styles.addressText}>
                  {[address.address, address.lga, address.state]
                    .filter(Boolean)
                    .join(', ')}
                </Text>
              </View>
            </Pressable>
          );
        })}

        <Pressable
          accessibilityRole="button"
          onPress={() => setUseNewAddress(true)}
          style={[styles.addressOption, useNewAddress ? styles.optionActive : null]}
        >
          <Ionicons
            color={useNewAddress ? theme.colors.primary : theme.colors.textSubtle}
            name={useNewAddress ? 'radio-button-on' : 'radio-button-off'}
            size={20}
          />
          <Text style={styles.addressLabel}>Deliver somewhere new</Text>
        </Pressable>

        {useNewAddress ? (
          <View style={styles.newAddressForm}>
            <Input
              label="Site / delivery address"
              onChangeText={setNewAddress}
              placeholder="12 Adeola Odeku Street, Victoria Island"
              value={newAddress}
            />
            <Input
              label="State"
              onChangeText={setNewState}
              placeholder="Lagos"
              value={newState}
            />
            <Pressable
              accessibilityRole="button"
              onPress={() => setSaveNewAddress((current) => !current)}
              style={styles.saveToggle}
            >
              <Ionicons
                color={
                  saveNewAddress ? theme.colors.primary : theme.colors.textSubtle
                }
                name={saveNewAddress ? 'checkbox' : 'square-outline'}
                size={20}
              />
              <Text style={styles.saveToggleText}>
                Save this address for next time
              </Text>
            </Pressable>
          </View>
        ) : null}
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Who receives the delivery?</Text>
        <Input
          label="Recipient name"
          onChangeText={setRecipientName}
          value={recipientName}
        />
        <Input
          keyboardType="phone-pad"
          label="Recipient phone"
          onChangeText={setRecipientPhone}
          placeholder="08012345678"
          value={recipientPhone}
        />
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Payment method</Text>
        {PAYMENT_METHODS.map((method) => {
          const active = paymentMethod === method.value;

          return (
            <Pressable
              accessibilityRole="button"
              key={method.value}
              onPress={() => setPaymentMethod(method.value)}
              style={[styles.paymentOption, active ? styles.optionActive : null]}
            >
              <View
                style={[
                  styles.paymentIcon,
                  active ? styles.paymentIconActive : null,
                ]}
              >
                <Ionicons
                  color={active ? theme.colors.white : theme.colors.primary}
                  name={method.icon}
                  size={18}
                />
              </View>
              <View style={styles.paymentCopy}>
                <Text style={styles.paymentLabel}>{method.label}</Text>
                <Text style={styles.paymentDescription}>{method.description}</Text>
              </View>
              <Ionicons
                color={active ? theme.colors.primary : theme.colors.textSubtle}
                name={active ? 'radio-button-on' : 'radio-button-off'}
                size={20}
              />
            </Pressable>
          );
        })}
        {paymentMethod === 'card' ? (
          <View style={styles.devNote}>
            <Ionicons
              color={theme.colors.warning}
              name="construct-outline"
              size={16}
            />
            <Text style={styles.devNoteText}>
              Card payments are in development mode — the order is recorded as
              awaiting payment until Paystack keys are added.
            </Text>
          </View>
        ) : null}
      </Card>

      <Card style={styles.card}>
        <Text style={styles.sectionTitle}>Order summary</Text>
        {cart.items.map((item) => (
          <View key={item.id} style={styles.summaryRow}>
            <Text numberOfLines={1} style={styles.summaryName}>
              {item.quantity} × {item.name}
            </Text>
            <Text style={styles.summaryPrice}>{formatCurrency(item.lineTotal)}</Text>
          </View>
        ))}
        <View style={[styles.summaryRow, styles.summaryTotalRow]}>
          <Text style={styles.summaryTotalLabel}>Total</Text>
          <Text style={styles.summaryTotal}>{formatCurrency(cart.total)}</Text>
        </View>

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <Button
          disabled={placing || cart.items.length === 0}
          loading={placing}
          onPress={placeOrder}
          size="lg"
          title={`Place order · ${formatCurrency(cart.total)}`}
        />
        <Text style={styles.protectionNote}>
          <Ionicons
            color={theme.colors.success}
            name="shield-checkmark"
            size={13}
          />{' '}
          Protected by delivery confirmation — you approve the handover code
          only when the materials arrive in good condition.
        </Text>
      </Card>
    </Screen>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  successBackdrop: {
    alignItems: 'center',
    backgroundColor: theme.colors.overlay,
    flex: 1,
    justifyContent: 'center',
    padding: theme.spacing.lg,
  },
  successCard: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderRadius: theme.radius.xl,
    gap: theme.spacing.sm,
    padding: theme.spacing.xl,
    width: '100%',
    ...theme.shadows.elevated,
  },
  successRing: {
    alignItems: 'center',
    backgroundColor: theme.colors.secondarySoft,
    borderRadius: theme.radius.pill,
    height: 108,
    justifyContent: 'center',
    marginBottom: theme.spacing.sm,
    width: 108,
  },
  successCircle: {
    alignItems: 'center',
    backgroundColor: theme.colors.secondary,
    borderRadius: theme.radius.pill,
    height: 84,
    justifyContent: 'center',
    width: 84,
  },
  successTitle: {
    color: theme.colors.text,
    fontSize: 22,
    fontWeight: '900',
  },
  successReference: {
    color: theme.colors.primaryDark,
    fontSize: 14,
    fontWeight: '900',
    letterSpacing: 0.5,
  },
  successMessage: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    marginBottom: theme.spacing.sm,
    textAlign: 'center',
  },
  card: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  addressOption: {
    alignItems: 'center',
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  optionActive: {
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.primary,
  },
  addressCopy: {
    flex: 1,
  },
  addressLabel: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '900',
  },
  addressText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    lineHeight: 19,
    marginTop: 2,
  },
  newAddressForm: {
    gap: theme.spacing.md,
  },
  saveToggle: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  saveToggleText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '700',
  },
  paymentOption: {
    alignItems: 'center',
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    padding: theme.spacing.md,
  },
  paymentIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  paymentIconActive: {
    backgroundColor: theme.colors.primary,
  },
  paymentCopy: {
    flex: 1,
  },
  paymentLabel: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '900',
  },
  paymentDescription: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    lineHeight: 18,
    marginTop: 2,
  },
  devNote: {
    alignItems: 'flex-start',
    backgroundColor: theme.colors.accentSoft,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
  },
  devNoteText: {
    color: theme.colors.text,
    flex: 1,
    fontSize: 12.5,
    lineHeight: 18,
  },
  summaryRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    justifyContent: 'space-between',
  },
  summaryName: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13.5,
    fontWeight: '700',
  },
  summaryPrice: {
    color: theme.colors.text,
    fontSize: 13.5,
    fontWeight: '900',
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
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    lineHeight: 20,
    textAlign: 'center',
  },
  protectionNote: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    lineHeight: 18,
    textAlign: 'center',
  },
});
