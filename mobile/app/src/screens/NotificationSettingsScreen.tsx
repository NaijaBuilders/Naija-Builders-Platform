import { Ionicons } from '@expo/vector-icons';
import React, { useEffect, useMemo, useState } from 'react';
import { Pressable, StyleSheet, Switch, Text, View } from 'react-native';
import {
  Button,
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import { notificationService } from '../services';
import { theme } from '../theme';
import type {
  NotificationChannel,
  NotificationPreference,
  QuietHours,
} from '../types';

type IconName = React.ComponentProps<typeof Ionicons>['name'];

type EventMeta = {
  key: string;
  label: string;
  group: string;
  icon: IconName;
};

const EVENTS: EventMeta[] = [
  { key: 'rfq_received', label: 'RFQ received', group: 'Procurement', icon: 'document-text-outline' },
  { key: 'new_quote', label: 'New quote', group: 'Procurement', icon: 'pricetag-outline' },
  { key: 'quote_status', label: 'Quote updates', group: 'Procurement', icon: 'pricetags-outline' },
  { key: 'order_placed', label: 'Order placed', group: 'Orders & delivery', icon: 'cart-outline' },
  { key: 'order_status', label: 'Order status', group: 'Orders & delivery', icon: 'sync-outline' },
  { key: 'delivery_update', label: 'Delivery updates', group: 'Orders & delivery', icon: 'car-outline' },
  { key: 'delivery_confirm', label: 'Delivery confirmation', group: 'Orders & delivery', icon: 'checkmark-done-outline' },
  { key: 'invoice_issued', label: 'Invoices', group: 'Billing', icon: 'receipt-outline' },
  { key: 'payment_received', label: 'Payment received', group: 'Billing', icon: 'cash-outline' },
  { key: 'payment_due', label: 'Payment reminders', group: 'Billing', icon: 'alarm-outline' },
  { key: 'new_message', label: 'New messages', group: 'Messages', icon: 'chatbubble-ellipses-outline' },
  { key: 'price_drop', label: 'Price drops', group: 'Marketing', icon: 'trending-down-outline' },
  { key: 'back_in_stock', label: 'Back in stock', group: 'Marketing', icon: 'cube-outline' },
  { key: 'marketing', label: 'Promotions', group: 'Marketing', icon: 'megaphone-outline' },
];

const CHANNELS: Array<{ key: NotificationChannel; label: string; icon: IconName }> = [
  { key: 'push', label: 'Push', icon: 'notifications-outline' },
  { key: 'email', label: 'Email', icon: 'mail-outline' },
  { key: 'sms', label: 'SMS', icon: 'chatbox-outline' },
  { key: 'in_app', label: 'In-app', icon: 'phone-portrait-outline' },
];

const QUIET_PRESETS = [
  { label: '10pm – 7am', from: '22:00', to: '07:00' },
  { label: '9pm – 8am', from: '21:00', to: '08:00' },
  { label: '11pm – 6am', from: '23:00', to: '06:00' },
];

const GROUP_ORDER = ['Procurement', 'Orders & delivery', 'Billing', 'Messages', 'Marketing'];

type PrefMap = Record<string, NotificationPreference>;

export function NotificationSettingsScreen() {
  const [prefs, setPrefs] = useState<PrefMap>({});
  const [quiet, setQuiet] = useState<QuietHours>({
    enabled: false,
    from: '22:00',
    to: '07:00',
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    let mounted = true;

    notificationService
      .getPreferences()
      .then((response) => {
        if (!mounted) {
          return;
        }
        const map: PrefMap = {};
        response.preferences.forEach((pref) => {
          map[pref.event_key] = pref;
        });
        setPrefs(map);
        setQuiet(response.quiet_hours);
      })
      .catch((reason) => {
        if (mounted) {
          setError(
            reason instanceof Error ? reason.message : 'Could not load preferences.'
          );
        }
      })
      .finally(() => {
        if (mounted) {
          setLoading(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

  const grouped = useMemo(() => {
    return GROUP_ORDER.map((group) => ({
      group,
      events: EVENTS.filter((event) => event.group === group),
    })).filter((section) => section.events.length > 0);
  }, []);

  const toggleChannel = (eventKey: string, channel: NotificationChannel) => {
    setSaved(false);
    setPrefs((current) => {
      const existing =
        current[eventKey] ??
        ({ event_key: eventKey, push: false, email: false, sms: false, in_app: false } as NotificationPreference);
      return {
        ...current,
        [eventKey]: { ...existing, [channel]: !existing[channel] },
      };
    });
  };

  const handleSave = async () => {
    setSaving(true);
    setError('');
    setSaved(false);

    try {
      const response = await notificationService.updatePreferences(
        Object.values(prefs),
        quiet
      );
      const map: PrefMap = {};
      response.preferences.forEach((pref) => {
        map[pref.event_key] = pref;
      });
      setPrefs(map);
      setQuiet(response.quiet_hours);
      setSaved(true);
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Could not save.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={<FloatingBackButton />}
      >
        <Loader label="Loading notifications" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Notifications"
        title="Alerts"
        subtitle="Choose how you want to be notified for each event."
      />

      <Card style={styles.quietCard}>
        <View style={styles.quietHeader}>
          <View style={styles.quietIcon}>
            <Ionicons color={theme.colors.primary} name="moon-outline" size={18} />
          </View>
          <View style={styles.quietCopy}>
            <Text style={styles.quietTitle}>Quiet hours</Text>
            <Text style={styles.quietText}>Mute non-critical alerts overnight.</Text>
          </View>
          <Switch
            value={quiet.enabled}
            onValueChange={(value) => {
              setSaved(false);
              setQuiet((current) => ({ ...current, enabled: value }));
            }}
            thumbColor={theme.colors.white}
            trackColor={{ false: theme.colors.borderStrong, true: theme.colors.primary }}
          />
        </View>
        {quiet.enabled ? (
          <View style={styles.presetRow}>
            {QUIET_PRESETS.map((preset) => {
              const active = quiet.from === preset.from && quiet.to === preset.to;
              return (
                <Pressable
                  key={preset.label}
                  onPress={() => {
                    setSaved(false);
                    setQuiet((current) => ({
                      ...current,
                      from: preset.from,
                      to: preset.to,
                    }));
                  }}
                  style={[styles.preset, active ? styles.presetActive : null]}
                >
                  <Text
                    style={[
                      styles.presetText,
                      active ? styles.presetTextActive : null,
                    ]}
                  >
                    {preset.label}
                  </Text>
                </Pressable>
              );
            })}
          </View>
        ) : null}
      </Card>

      {grouped.map((section) => (
        <View key={section.group}>
          <Text style={styles.groupLabel}>{section.group}</Text>
          <Card style={styles.group}>
            {section.events.map((event, index) => {
              const pref = prefs[event.key];
              return (
                <View
                  key={event.key}
                  style={[
                    styles.eventRow,
                    index < section.events.length - 1 ? styles.eventBorder : null,
                  ]}
                >
                  <View style={styles.eventHeader}>
                    <View style={styles.eventIcon}>
                      <Ionicons
                        color={theme.colors.primary}
                        name={event.icon}
                        size={16}
                      />
                    </View>
                    <Text style={styles.eventLabel}>{event.label}</Text>
                  </View>
                  <View style={styles.channelRow}>
                    {CHANNELS.map((channel) => {
                      const on = Boolean(pref?.[channel.key]);
                      return (
                        <Pressable
                          key={channel.key}
                          accessibilityRole="button"
                          onPress={() => toggleChannel(event.key, channel.key)}
                          style={[styles.channel, on ? styles.channelOn : null]}
                        >
                          <Ionicons
                            color={on ? theme.colors.white : theme.colors.textSubtle}
                            name={channel.icon}
                            size={13}
                          />
                          <Text
                            style={[
                              styles.channelText,
                              on ? styles.channelTextOn : null,
                            ]}
                          >
                            {channel.label}
                          </Text>
                        </Pressable>
                      );
                    })}
                  </View>
                </View>
              );
            })}
          </Card>
        </View>
      ))}

      {error ? (
        <View style={styles.banner}>
          <Ionicons color={theme.colors.danger} name="alert-circle" size={18} />
          <Text style={styles.bannerText}>{error}</Text>
        </View>
      ) : null}
      {saved ? (
        <View style={[styles.banner, styles.bannerSuccess]}>
          <Ionicons
            color={theme.colors.success}
            name="checkmark-circle"
            size={18}
          />
          <Text style={[styles.bannerText, styles.bannerSuccessText]}>
            Notification preferences saved.
          </Text>
        </View>
      ) : null}

      <Button
        title="Save changes"
        size="lg"
        onPress={handleSave}
        loading={saving}
        style={styles.save}
      />
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
  quietCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  quietHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  quietIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  quietCopy: {
    flex: 1,
  },
  quietTitle: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  quietText: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    marginTop: 2,
  },
  presetRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  preset: {
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.pill,
    borderWidth: 1,
    flex: 1,
    paddingVertical: 8,
  },
  presetActive: {
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.primary,
  },
  presetText: {
    color: theme.colors.textMuted,
    fontSize: 12,
    fontWeight: '800',
    textAlign: 'center',
  },
  presetTextActive: {
    color: theme.colors.primaryDark,
  },
  groupLabel: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '900',
    letterSpacing: 0.6,
    marginBottom: theme.spacing.sm,
    marginTop: theme.spacing.md,
    textTransform: 'uppercase',
  },
  group: {
    paddingVertical: 0,
  },
  eventRow: {
    gap: theme.spacing.sm,
    paddingVertical: theme.spacing.md,
  },
  eventBorder: {
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
  },
  eventHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  eventIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.sm,
    height: 30,
    justifyContent: 'center',
    width: 30,
  },
  eventLabel: {
    color: theme.colors.text,
    fontSize: 14,
    fontWeight: '800',
  },
  channelRow: {
    flexDirection: 'row',
    gap: theme.spacing.xs,
  },
  channel: {
    alignItems: 'center',
    backgroundColor: theme.colors.surfaceMuted,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.sm,
    borderWidth: 1,
    flex: 1,
    flexDirection: 'row',
    gap: 3,
    justifyContent: 'center',
    paddingVertical: 7,
  },
  channelOn: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  channelText: {
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '800',
  },
  channelTextOn: {
    color: theme.colors.white,
  },
  banner: {
    alignItems: 'center',
    backgroundColor: theme.colors.dangerSoft,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginTop: theme.spacing.md,
    padding: theme.spacing.sm,
  },
  bannerText: {
    color: theme.colors.danger,
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
  },
  bannerSuccess: {
    backgroundColor: theme.colors.secondarySoft,
  },
  bannerSuccessText: {
    color: theme.colors.success,
  },
  save: {
    marginTop: theme.spacing.lg,
  },
});
