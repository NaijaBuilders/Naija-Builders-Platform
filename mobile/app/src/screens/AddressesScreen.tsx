import { Ionicons } from '@expo/vector-icons';
import React, { useCallback, useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Input,
  Loader,
  Screen,
} from '../components';
import { addressService } from '../services';
import { theme } from '../theme';
import type { DeliveryAddress } from '../types';

export function AddressesScreen() {
  const [addresses, setAddresses] = useState<DeliveryAddress[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [label, setLabel] = useState('');
  const [address, setAddress] = useState('');
  const [state, setState] = useState('');
  const [contactName, setContactName] = useState('');
  const [contactPhone, setContactPhone] = useState('');
  const [makeDefault, setMakeDefault] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    try {
      setAddresses(await addressService.list());
      setError('');
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not load addresses.'
      );
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const saveAddress = async () => {
    if (address.trim().length < 5) {
      setError('Please enter the full address.');
      return;
    }

    setSaving(true);
    setError('');
    try {
      await addressService.create({
        label: label.trim() || 'Site address',
        address: address.trim(),
        state: state.trim() || undefined,
        contactName: contactName.trim() || undefined,
        contactPhone: contactPhone.trim() || undefined,
        isDefault: makeDefault,
      });
      setLabel('');
      setAddress('');
      setState('');
      setContactName('');
      setContactPhone('');
      setMakeDefault(false);
      setShowForm(false);
      await load();
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not save the address.'
      );
    } finally {
      setSaving(false);
    }
  };

  const removeAddress = async (addressId: string) => {
    setAddresses((current) => current.filter((item) => item.id !== addressId));
    try {
      await addressService.remove(addressId);
    } catch {
      load();
    }
  };

  const setDefault = async (item: DeliveryAddress) => {
    try {
      await addressService.update(item.id, {
        address: item.address,
        label: item.label || undefined,
        state: item.state || undefined,
        lga: item.lga || undefined,
        contactName: item.contactName || undefined,
        contactPhone: item.contactPhone || undefined,
        instructions: item.instructions || undefined,
        isDefault: true,
      });
      await load();
    } catch (reason) {
      setError(
        reason instanceof Error ? reason.message : 'Could not update default.'
      );
    }
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading addresses" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Logistics"
        title="Delivery addresses"
        subtitle="Save your sites once and reuse them at checkout."
      />

      {error ? <Text style={styles.error}>{error}</Text> : null}

      {addresses.map((item) => (
        <Card key={item.id} style={styles.addressCard}>
          <View style={styles.addressRow}>
            <View style={styles.addressIcon}>
              <Ionicons
                color={theme.colors.primary}
                name="location-outline"
                size={20}
              />
            </View>
            <View style={styles.addressCopy}>
              <Text style={styles.addressLabel}>
                {item.label || 'Saved address'}
                {item.isDefault ? '  ·  Default' : ''}
              </Text>
              <Text style={styles.addressText}>
                {[item.address, item.lga, item.state].filter(Boolean).join(', ')}
              </Text>
              {item.contactName ? (
                <Text style={styles.addressContact}>
                  {item.contactName}
                  {item.contactPhone ? ` · ${item.contactPhone}` : ''}
                </Text>
              ) : null}
            </View>
          </View>
          <View style={styles.addressActions}>
            {!item.isDefault ? (
              <Pressable
                accessibilityRole="button"
                onPress={() => setDefault(item)}
                style={styles.actionChip}
              >
                <Text style={styles.actionChipText}>Make default</Text>
              </Pressable>
            ) : null}
            <Pressable
              accessibilityRole="button"
              onPress={() => removeAddress(item.id)}
              style={[styles.actionChip, styles.actionChipDanger]}
            >
              <Text style={[styles.actionChipText, styles.actionChipDangerText]}>
                Remove
              </Text>
            </Pressable>
          </View>
        </Card>
      ))}

      {addresses.length === 0 ? (
        <Card style={styles.emptyCard}>
          <Text style={styles.emptyTitle}>No saved addresses</Text>
          <Text style={styles.emptyText}>
            Add your site or office address to speed up checkout.
          </Text>
        </Card>
      ) : null}

      {showForm ? (
        <Card style={styles.formCard}>
          <Text style={styles.formTitle}>New address</Text>
          <Input
            label="Label"
            onChangeText={setLabel}
            placeholder="Main site, Office…"
            value={label}
          />
          <Input
            label="Address"
            onChangeText={setAddress}
            placeholder="12 Adeola Odeku Street, Victoria Island"
            value={address}
          />
          <Input label="State" onChangeText={setState} placeholder="Lagos" value={state} />
          <Input
            label="Contact person (optional)"
            onChangeText={setContactName}
            value={contactName}
          />
          <Input
            keyboardType="phone-pad"
            label="Contact phone (optional)"
            onChangeText={setContactPhone}
            value={contactPhone}
          />
          <Pressable
            accessibilityRole="button"
            onPress={() => setMakeDefault((current) => !current)}
            style={styles.defaultToggle}
          >
            <Ionicons
              color={makeDefault ? theme.colors.primary : theme.colors.textSubtle}
              name={makeDefault ? 'checkbox' : 'square-outline'}
              size={20}
            />
            <Text style={styles.defaultToggleText}>Set as default address</Text>
          </Pressable>
          <View style={styles.formActions}>
            <Button
              onPress={() => setShowForm(false)}
              style={styles.formAction}
              title="Cancel"
              variant="outline"
            />
            <Button
              loading={saving}
              onPress={saveAddress}
              style={styles.formAction}
              title="Save address"
            />
          </View>
        </Card>
      ) : (
        <Button
          onPress={() => setShowForm(true)}
          size="lg"
          title="Add new address"
          variant="outline"
        />
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
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    marginBottom: theme.spacing.md,
    textAlign: 'center',
  },
  addressCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  addressRow: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  addressIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 42,
    justifyContent: 'center',
    width: 42,
  },
  addressCopy: {
    flex: 1,
  },
  addressLabel: {
    color: theme.colors.text,
    fontSize: 14.5,
    fontWeight: '900',
  },
  addressText: {
    color: theme.colors.textMuted,
    fontSize: 13.5,
    lineHeight: 20,
    marginTop: 2,
  },
  addressContact: {
    color: theme.colors.textSubtle,
    fontSize: 12.5,
    fontWeight: '700',
    marginTop: 4,
  },
  addressActions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  actionChip: {
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: 7,
  },
  actionChipText: {
    color: theme.colors.primaryDark,
    fontSize: 12.5,
    fontWeight: '900',
  },
  actionChipDanger: {
    backgroundColor: theme.colors.dangerSoft,
  },
  actionChipDangerText: {
    color: theme.colors.danger,
  },
  emptyCard: {
    alignItems: 'center',
    gap: theme.spacing.sm,
    marginBottom: theme.spacing.md,
    paddingVertical: theme.spacing.xl,
  },
  emptyTitle: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  emptyText: {
    color: theme.colors.textMuted,
    lineHeight: 21,
    textAlign: 'center',
  },
  formCard: {
    gap: theme.spacing.md,
  },
  formTitle: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  defaultToggle: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  defaultToggleText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '700',
  },
  formActions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  formAction: {
    flex: 1,
  },
});
