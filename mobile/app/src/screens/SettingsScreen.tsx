import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import React from 'react';
import { Alert, Pressable, StyleSheet, Switch, Text, View } from 'react-native';
import { Avatar, Card, FloatingBackButton, Header, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import { useThemeMode, type ThemeMode } from '../context/ThemeContext';
import type { ProfileStackParamList } from '../navigation/types';
import { userService } from '../services';
import { theme } from '../theme';
import type { UserPreferences } from '../types';
import { haptics } from '../utils/haptics';

const APP_VERSION = '0.1.0';

type IconName = React.ComponentProps<typeof Ionicons>['name'];
type PreferenceKey = keyof UserPreferences;

const THEME_OPTIONS: Array<{ value: ThemeMode; label: string; icon: IconName }> = [
  { value: 'light', label: 'Light', icon: 'sunny-outline' },
  { value: 'dark', label: 'Dark', icon: 'moon-outline' },
  { value: 'system', label: 'Auto', icon: 'phone-portrait-outline' },
];

type SettingsNavigation = NativeStackNavigationProp<
  ProfileStackParamList,
  'Settings'
>;

export function SettingsScreen() {
  const navigation = useNavigation<SettingsNavigation>();
  const { preferences, setPreferences, signOut, user } = useAppState();
  const { mode: themeMode, setMode: setThemeMode } = useThemeMode();

  const confirmSignOut = () => {
    Alert.alert('Sign out of NaijaBuilders?', undefined, [
      { style: 'cancel', text: 'Stay signed in' },
      { style: 'destructive', text: 'Sign out', onPress: () => signOut() },
    ]);
  };

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

      {user ? (
        <Pressable
          accessibilityRole="button"
          onPress={() => navigation.navigate('EditProfile')}
          style={styles.identityCard}
        >
          <Avatar imageUri={user.profileImage} name={user.name} size={52} />
          <View style={styles.identityCopy}>
            <Text numberOfLines={1} style={styles.identityName}>
              {user.name}
            </Text>
            <Text numberOfLines={1} style={styles.identityMeta}>
              {user.username ? `@${user.username} · ` : ''}
              {user.email}
            </Text>
          </View>
          <Ionicons
            color={theme.colors.textSubtle}
            name="chevron-forward"
            size={20}
          />
        </Pressable>
      ) : null}

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
          label="Change password"
          description="Update the password you sign in with"
          onPress={() => navigation.navigate('ChangePassword')}
        />
      </Card>

      <Text style={styles.groupLabel}>Notifications</Text>
      <Card style={styles.group}>
        <ToggleRow
          icon="notifications-outline"
          label="Push notifications"
          description="Order updates, messages and quotes on this device."
          value={preferences.push_notifications}
          onToggle={() => togglePreference('push_notifications')}
          border
        />
        <NavRow
          icon="options-outline"
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
        <View style={styles.row}>
          <View style={styles.iconCircle}>
            <Ionicons
              color={theme.colors.primary}
              name="contrast-outline"
              size={18}
            />
          </View>
          <View style={styles.rowCopy}>
            <Text style={styles.rowLabel}>Theme</Text>
            <Text style={styles.rowDescription}>Light, dark or match your device.</Text>
          </View>
        </View>
        <View style={styles.themeSwitcher}>
          {THEME_OPTIONS.map((option) => {
            const active = themeMode === option.value;
            return (
              <Pressable
                accessibilityRole="button"
                accessibilityState={{ selected: active }}
                key={option.value}
                onPress={() => {
                  haptics.tap();
                  setThemeMode(option.value);
                }}
                style={[
                  styles.themeOption,
                  active ? styles.themeOptionActive : null,
                ]}
              >
                <Ionicons
                  color={active ? theme.colors.white : theme.colors.textMuted}
                  name={option.icon}
                  size={16}
                />
                <Text
                  style={[
                    styles.themeOptionText,
                    active ? styles.themeOptionTextActive : null,
                  ]}
                >
                  {option.label}
                </Text>
              </Pressable>
            );
          })}
        </View>
      </Card>

      <Text style={styles.groupLabel}>Support</Text>
      <Card style={styles.group}>
        <NavRow
          icon="help-circle-outline"
          label="Help & support"
          description="Answers to common questions and how to reach us"
          onPress={() => navigation.navigate('HelpSupport')}
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
        onPress={confirmSignOut}
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
  identityCard: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginTop: theme.spacing.md,
    padding: theme.spacing.md,
    ...theme.shadows.soft,
  },
  identityCopy: {
    flex: 1,
  },
  identityName: {
    color: theme.colors.text,
    fontSize: 16,
    fontWeight: '900',
  },
  identityMeta: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    fontWeight: '700',
    marginTop: 2,
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
  themeSwitcher: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: 4,
    marginBottom: theme.spacing.md,
    padding: 4,
  },
  themeOption: {
    alignItems: 'center',
    borderRadius: theme.radius.sm,
    flex: 1,
    flexDirection: 'row',
    gap: 6,
    justifyContent: 'center',
    paddingVertical: theme.spacing.sm,
  },
  themeOptionActive: {
    backgroundColor: theme.colors.primary,
  },
  themeOptionText: {
    color: theme.colors.textMuted,
    fontSize: 13,
    fontWeight: '800',
  },
  themeOptionTextActive: {
    color: theme.colors.white,
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
    backgroundColor: theme.colors.dangerSoft,
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
