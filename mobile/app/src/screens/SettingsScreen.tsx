import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import React from 'react';
import { Pressable, StyleSheet, Switch, Text, View } from 'react-native';
import { Card, FloatingBackButton, Header, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import type { ProfileStackParamList } from '../navigation/types';
import { userService } from '../services';
import { theme } from '../theme';
import type { UserPreferences } from '../types';

const APP_VERSION = '0.1.0';

type IconName = React.ComponentProps<typeof Ionicons>['name'];
type PreferenceKey = keyof UserPreferences;

type SettingsNavigation = NativeStackNavigationProp<
  ProfileStackParamList,
  'Settings'
>;

export function SettingsScreen() {
  const navigation = useNavigation<SettingsNavigation>();
  const { preferences, setPreferences, signOut } = useAppState();

  const togglePreference = async (key: PreferenceKey) => {
    const next = { ...preferences, [key]: !preferences[key] };
    setPreferences(next);

    try {
      const saved = await userService.updatePreferences(next);
      setPreferences(saved);
    } catch {
      setPreferences(preferences);
    }
  };

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={<FloatingBackButton />}
    >
      <Header
        eyebrow="Account"
        title="Settings"
        subtitle="Manage your account, notifications and app preferences."
      />

      <Text style={styles.groupLabel}>Account</Text>
      <Card style={styles.group}>
        <NavRow
          icon="person-outline"
          label="Edit profile"
          description="Name, photo, contact and business details"
          onPress={() => navigation.navigate('EditProfile')}
          border
        />
        <NavRow
          icon="lock-closed-outline"
          label="Password & security"
          description="Manage how you sign in"
          right={<SoonBadge />}
        />
      </Card>

      <Text style={styles.groupLabel}>Notifications</Text>
      <Card style={styles.group}>
        <NavRow
          icon="notifications-outline"
          label="Notification preferences"
          description="Push, email, SMS and in-app per event"
          onPress={() => navigation.navigate('NotificationSettings')}
        />
      </Card>

      <Text style={styles.groupLabel}>Payments</Text>
      <Card style={styles.group}>
        <NavRow
          icon="card-outline"
          label="Payment methods"
          description="Cards, bank transfer, payout account"
          right={<SoonBadge />}
        />
      </Card>

      <Text style={styles.groupLabel}>Logistics</Text>
      <Card style={styles.group}>
        <NavRow
          icon="location-outline"
          label="Delivery addresses"
          description="Saved sites reused at checkout"
          onPress={() => navigation.navigate('Addresses')}
        />
      </Card>

      <Text style={styles.groupLabel}>Shortlist</Text>
      <Card style={styles.group}>
        <NavRow
          icon="heart-outline"
          label="Saved materials"
          description="Products you have bookmarked"
          onPress={() => navigation.navigate('SavedItems')}
        />
      </Card>

      <Text style={styles.groupLabel}>Display</Text>
      <Card style={styles.group}>
        <ToggleRow
          icon="grid-outline"
          label="Compact cards"
          description="Denser lists for repeat workflows."
          value={preferences.compact_cards}
          onToggle={() => togglePreference('compact_cards')}
          border
        />
        <NavRow
          icon="moon-outline"
          label="Theme"
          description="Light, dark or system"
          right={
            <View style={styles.themeValueRow}>
              <Text style={styles.themeValue}>Light</Text>
              <SoonBadge />
            </View>
          }
        />
      </Card>

      <Text style={styles.groupLabel}>Privacy</Text>
      <Card style={styles.group}>
        <NavRow
          icon="shield-checkmark-outline"
          label="Data & privacy"
          description="Export data, consent, delete account"
          onPress={() => navigation.navigate('DataPrivacy')}
        />
      </Card>

      <Text style={styles.groupLabel}>About</Text>
      <Card style={styles.group}>
        <NavRow
          icon="document-text-outline"
          label="Terms & privacy"
          onPress={() => navigation.navigate('Terms')}
          border
        />
        <View style={styles.infoRow}>
          <View style={styles.iconCircle}>
            <Ionicons
              color={theme.colors.primary}
              name="information-circle-outline"
              size={18}
            />
          </View>
          <Text style={styles.rowLabel}>App version</Text>
          <Text style={styles.versionValue}>{APP_VERSION}</Text>
        </View>
      </Card>

      <Pressable
        accessibilityRole="button"
        onPress={signOut}
        style={styles.signOut}
      >
        <Ionicons color={theme.colors.danger} name="log-out-outline" size={18} />
        <Text style={styles.signOutText}>Sign out</Text>
      </Pressable>
    </Screen>
  );
}

function ToggleRow({
  icon,
  label,
  description,
  value,
  onToggle,
  border,
}: {
  icon: IconName;
  label: string;
  description: string;
  value: boolean;
  onToggle: () => void;
  border?: boolean;
}) {
  return (
    <View style={[styles.row, border ? styles.rowBorder : null]}>
      <View style={styles.iconCircle}>
        <Ionicons color={theme.colors.primary} name={icon} size={18} />
      </View>
      <View style={styles.rowCopy}>
        <Text style={styles.rowLabel}>{label}</Text>
        <Text style={styles.rowDescription}>{description}</Text>
      </View>
      <Switch
        onValueChange={onToggle}
        thumbColor={theme.colors.white}
        trackColor={{
          false: theme.colors.borderStrong,
          true: theme.colors.primary,
        }}
        value={value}
      />
    </View>
  );
}

function NavRow({
  icon,
  label,
  description,
  onPress,
  right,
  border,
}: {
  icon: IconName;
  label: string;
  description?: string;
  onPress?: () => void;
  right?: React.ReactNode;
  border?: boolean;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      disabled={!onPress}
      onPress={onPress}
      style={[styles.row, border ? styles.rowBorder : null]}
    >
      <View style={styles.iconCircle}>
        <Ionicons color={theme.colors.primary} name={icon} size={18} />
      </View>
      <View style={styles.rowCopy}>
        <Text style={styles.rowLabel}>{label}</Text>
        {description ? (
          <Text style={styles.rowDescription}>{description}</Text>
        ) : null}
      </View>
      {right ?? (
        <Ionicons
          color={theme.colors.textSubtle}
          name="chevron-forward"
          size={20}
        />
      )}
    </Pressable>
  );
}

function SoonBadge() {
  return (
    <View style={styles.soonBadge}>
      <Text style={styles.soonText}>Soon</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  contentWithFloatingBack: {
    paddingTop: 70,
  },
  groupLabel: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '900',
    letterSpacing: 0.6,
    marginBottom: theme.spacing.sm,
    marginTop: theme.spacing.lg,
    textTransform: 'uppercase',
  },
  group: {
    paddingVertical: 0,
  },
  row: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  infoRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  rowBorder: {
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
  },
  iconCircle: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 40,
    justifyContent: 'center',
    width: 40,
  },
  rowCopy: {
    flex: 1,
  },
  rowLabel: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '800',
  },
  rowDescription: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    lineHeight: 17,
    marginTop: 2,
  },
  versionValue: {
    color: theme.colors.textMuted,
    fontSize: 14,
    fontWeight: '800',
  },
  themeValueRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  themeValue: {
    color: theme.colors.textMuted,
    fontSize: 14,
    fontWeight: '800',
  },
  soonBadge: {
    backgroundColor: theme.colors.accentSoft,
    borderRadius: theme.radius.pill,
    paddingHorizontal: theme.spacing.sm,
    paddingVertical: 4,
  },
  soonText: {
    color: theme.colors.warning,
    fontSize: 11,
    fontWeight: '900',
  },
  signOut: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.lg,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    justifyContent: 'center',
    marginTop: theme.spacing.xl,
    paddingVertical: theme.spacing.md,
  },
  signOutText: {
    color: theme.colors.danger,
    fontSize: 15,
    fontWeight: '900',
  },
});
