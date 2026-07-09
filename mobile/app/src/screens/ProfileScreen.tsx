import { Ionicons } from '@expo/vector-icons';
import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import React, { useCallback, useState } from 'react';
import { Alert, Pressable, Share, StyleSheet, Text, View } from 'react-native';
import { Avatar, Button, Card, Screen, Skeleton } from '../components';
import { useAppState } from '../context/AppContext';
import { useCompany } from '../hooks/useMarketplaceData';
import type { MainTabParamList, ProfileStackParamList } from '../navigation/types';
import { userService, type ProfileStats } from '../services/userService';
import { theme } from '../theme';
import { capitalizeWords } from '../utils/format';
import { haptics } from '../utils/haptics';

type IconName = React.ComponentProps<typeof Ionicons>['name'];
type AccountType = 'buyer' | 'supplier' | 'service';
type DetailRow = { icon: IconName; label: string; value: string };

type ProfileNavigation = NativeStackNavigationProp<
  ProfileStackParamList,
  'ProfileMain'
>;

// Built per-render (not at module scope) so accent colors track the live
// theme after a light/dark switch.
function getAccountConfig(): Record<
  AccountType,
  { label: string; icon: IconName; accent: string; cta: string }
> {
  return {
    buyer: {
      label: 'Buyer',
      icon: 'cart',
      accent: theme.colors.primary,
      cta: 'Verify your account',
    },
    supplier: {
      label: 'Supplier',
      icon: 'storefront',
      accent: theme.colors.secondary,
      cta: 'Complete supplier KYC',
    },
    service: {
      label: 'Service Pro',
      icon: 'construct',
      accent: theme.colors.accent,
      cta: 'Complete provider KYC',
    },
  };
}

export function ProfileScreen() {
  const navigation = useNavigation<ProfileNavigation>();
  const { currentRole, signOut, user } = useAppState();
  const { data: company } = useCompany();
  const [stats, setStats] = useState<ProfileStats | null>(null);
  const tabNavigation = navigation.getParent<
    BottomTabNavigationProp<MainTabParamList>
  >();

  useFocusEffect(
    useCallback(() => {
      userService
        .getProfileStats()
        .then(setStats)
        .catch(() => undefined);
    }, [])
  );

  const confirmSignOut = useCallback(() => {
    Alert.alert('Sign out of NaijaBuilders?', undefined, [
      { style: 'cancel', text: 'Stay signed in' },
      { style: 'destructive', text: 'Sign out', onPress: () => signOut() },
    ]);
  }, [signOut]);

  const shareProfile = useCallback(() => {
    if (!user) {
      return;
    }

    haptics.tap();
    const identity = user.username ? `@${user.username}` : user.name;
    Share.share({
      message: `Find ${user.company || user.name} (${identity}) on NaijaBuilders — Nigeria's construction materials marketplace. https://naijabuilders.com`,
    }).catch(() => undefined);
  }, [user]);

  if (!user) {
    return (
      <Screen contentContainerStyle={styles.screen}>
        <View style={styles.heroSkeleton}>
          <Skeleton height={92} style={styles.skeletonAvatar} width={92} />
          <Skeleton height={20} width="55%" />
          <Skeleton height={13} width="40%" />
        </View>
        {[0, 1, 2].map((index) => (
          <Card key={`profile-skeleton-${index}`} style={styles.skeletonCard}>
            <Skeleton height={15} width="45%" />
            <Skeleton height={13} width="75%" />
            <Skeleton height={13} width="60%" />
          </Card>
        ))}
      </Screen>
    );
  }

  const accountType: AccountType =
    currentRole !== 'supplier' ? 'buyer' : user.offers_services ? 'service' : 'supplier';
  const config = getAccountConfig()[accountType];
  const serviceLabel = user.service_category
    ? capitalizeWords(user.service_category.replace(/[_-]+/g, ' '))
    : '';
  const isVerified = Boolean(
    company?.verified || user.is_verified_badge || user.kyc_status === 'approved'
  );
  const memberSince = user.created_at
    ? new Date(user.created_at).toLocaleDateString(undefined, {
        month: 'short',
        year: 'numeric',
      })
    : null;

  const heroSubtitle =
    accountType === 'supplier'
      ? company?.name || user.company || user.email
      : accountType === 'service'
        ? serviceLabel || 'Service professional'
        : user.email;

  const details: DetailRow[] =
    accountType === 'buyer'
      ? [
          { icon: 'mail-outline', label: 'Email', value: user.email },
          { icon: 'call-outline', label: 'Phone', value: user.phone || 'Not set' },
          {
            icon: 'location-outline',
            label: 'Location',
            value: user.location || 'Not set',
          },
          ...(memberSince
            ? [
                {
                  icon: 'calendar-outline' as IconName,
                  label: 'Member since',
                  value: memberSince,
                },
              ]
            : []),
        ]
      : accountType === 'supplier'
        ? [
            {
              icon: 'business-outline',
              label: 'Business',
              value: company?.name || user.company || 'Not set',
            },
            {
              icon: 'briefcase-outline',
              label: 'Industry',
              value: company?.industry || 'Not set',
            },
            {
              icon: 'location-outline',
              label: 'Location',
              value: company?.city || user.location || 'Not set',
            },
            { icon: 'call-outline', label: 'Phone', value: user.phone || 'Not set' },
            { icon: 'mail-outline', label: 'Email', value: user.email },
          ]
        : [
            {
              icon: 'construct-outline',
              label: 'Service',
              value: serviceLabel || 'Not set',
            },
            {
              icon: 'map-outline',
              label: 'Service area',
              value: user.location || company?.city || 'Not set',
            },
            { icon: 'call-outline', label: 'Phone', value: user.phone || 'Not set' },
            { icon: 'mail-outline', label: 'Email', value: user.email },
          ];

  const goToVerification = () => {
    if (accountType === 'buyer') {
      navigation.navigate('BuyerVerification');
    } else {
      navigation.navigate('SupplierOnboarding');
    }
  };

  return (
    <Screen contentContainerStyle={styles.screen}>
      <View style={styles.hero}>
        <Pressable
          accessibilityLabel="Share profile"
          accessibilityRole="button"
          hitSlop={10}
          onPress={shareProfile}
          style={[styles.gear, styles.shareButton]}
        >
          <Ionicons color={theme.colors.white} name="share-social-outline" size={20} />
        </Pressable>
        <Pressable
          accessibilityLabel="Settings"
          accessibilityRole="button"
          hitSlop={10}
          onPress={() => navigation.navigate('Settings')}
          style={styles.gear}
        >
          <Ionicons color={theme.colors.white} name="settings-outline" size={22} />
        </Pressable>

        <Pressable
          accessibilityLabel="Change profile photo"
          accessibilityRole="button"
          onPress={() => navigation.navigate('EditProfile')}
          style={styles.avatarRing}
        >
          <Avatar name={user.name} imageUri={user.profileImage} size={92} />
          <View style={[styles.typeBadge, { backgroundColor: config.accent }]}>
            <Ionicons color={theme.colors.white} name={config.icon} size={14} />
          </View>
          <View style={styles.cameraBadge}>
            <Ionicons color={theme.colors.primaryDark} name="camera" size={13} />
          </View>
        </Pressable>

        <Text style={styles.name}>{user.name}</Text>
        {user.username ? (
          <Text style={styles.username}>@{user.username}</Text>
        ) : null}
        <Text style={styles.heroSubtitle} numberOfLines={1}>
          {heroSubtitle}
        </Text>

        <View style={styles.pillRow}>
          <View style={[styles.pill, { backgroundColor: config.accent }]}>
            <Text style={styles.pillText}>{config.label}</Text>
          </View>
          {stats?.role === 'supplier' && stats.ratingAvg !== null ? (
            <View style={[styles.pill, styles.pillOutline]}>
              <Ionicons color={theme.colors.accent} name="star" size={13} />
              <Text style={styles.pillText}>
                {stats.ratingAvg.toFixed(1)} ({stats.ratingCount})
              </Text>
            </View>
          ) : null}
          <View
            style={[
              styles.pill,
              styles.pillOutline,
              isVerified ? styles.pillVerified : null,
            ]}
          >
            <Ionicons
              color={isVerified ? theme.colors.secondary : theme.colors.onPrimarySubtle}
              name={isVerified ? 'shield-checkmark' : 'shield-outline'}
              size={13}
            />
            <Text
              style={[
                styles.pillText,
                isVerified ? styles.pillVerifiedText : styles.pillOutlineText,
              ]}
            >
              {isVerified ? 'Verified' : 'Unverified'}
            </Text>
          </View>
        </View>
      </View>

      <Button
        title="Edit profile"
        variant="outline"
        onPress={() => navigation.navigate('EditProfile')}
        style={styles.editButton}
      />

      {stats ? (
        <View style={styles.metricRow}>
          {accountType === 'buyer' ? (
            <>
              <MetricTile
                label="Orders"
                onPress={() =>
                  tabNavigation?.navigate('Orders', { screen: 'OrdersMain' })
                }
                value={String(stats.ordersCount)}
              />
              <MetricTile
                label="Saved"
                onPress={() => navigation.navigate('SavedItems')}
                value={String(stats.savedCount)}
              />
              <MetricTile label="Reviews" value={String(stats.reviewsGiven)} />
            </>
          ) : (
            <>
              <MetricTile
                label="Listings"
                onPress={() =>
                  tabNavigation?.navigate('Browse', { screen: 'BrowseMain' })
                }
                value={String(stats.listingsCount)}
              />
              <MetricTile
                label="Orders"
                onPress={() =>
                  tabNavigation?.navigate('Orders', { screen: 'OrdersMain' })
                }
                value={String(stats.ordersCount)}
              />
              <MetricTile
                label="Rating"
                value={
                  stats.ratingAvg !== null ? stats.ratingAvg.toFixed(1) : '—'
                }
              />
            </>
          )}
        </View>
      ) : null}

      {accountType === 'buyer' ? (
        <View style={styles.statRow}>
          <StatChip
            icon="mail-outline"
            label="Email"
            ok={Boolean(user.email_confirmed)}
            onPress={goToVerification}
          />
          <StatChip
            icon="call-outline"
            label="Phone"
            ok={Boolean(user.phone_confirmed)}
            onPress={goToVerification}
          />
        </View>
      ) : (
        <View style={styles.statRow}>
          <StatChip
            icon="shield-checkmark-outline"
            label="KYC"
            ok={user.kyc_status === 'approved'}
            okText={capitalizeWords((user.kyc_status || 'pending').replace(/[_-]+/g, ' '))}
            pendingText={capitalizeWords((user.kyc_status || 'pending').replace(/[_-]+/g, ' '))}
            onPress={goToVerification}
          />
          <StatChip
            icon="mail-outline"
            label="Email"
            ok={Boolean(user.email_confirmed)}
            onPress={goToVerification}
          />
        </View>
      )}

      <Text style={styles.sectionTitle}>
        {accountType === 'buyer' ? 'Account details' : 'Business details'}
      </Text>
      <Card style={styles.detailCard}>
        {details.map((item, index) => (
          <View
            key={item.label}
            style={[
              styles.detailRow,
              index < details.length - 1 ? styles.rowBorder : null,
            ]}
          >
            <View
              style={[styles.detailIcon, { backgroundColor: tint(config.accent) }]}
            >
              <Ionicons color={config.accent} name={item.icon} size={18} />
            </View>
            <Text style={styles.detailLabel}>{item.label}</Text>
            <Text numberOfLines={1} style={styles.detailValue}>
              {item.value}
            </Text>
          </View>
        ))}
      </Card>

      {!isVerified ? (
        <Pressable
          accessibilityRole="button"
          onPress={goToVerification}
          style={[styles.ctaCard, { borderColor: config.accent }]}
        >
          <View style={[styles.ctaIcon, { backgroundColor: tint(config.accent) }]}>
            <Ionicons color={config.accent} name="shield-checkmark" size={22} />
          </View>
          <View style={styles.ctaCopy}>
            <Text style={styles.ctaTitle}>{config.cta}</Text>
            <Text style={styles.ctaText}>
              {accountType === 'buyer'
                ? 'Confirm your email, phone and ID for faster orders.'
                : 'Get the verified badge and unlock full marketplace access.'}
            </Text>
          </View>
          <Ionicons
            color={theme.colors.textSubtle}
            name="chevron-forward"
            size={20}
          />
        </Pressable>
      ) : null}

      <Card style={styles.menuCard}>
        <MenuRow
          border
          icon="heart-outline"
          label="Saved materials"
          onPress={() => navigation.navigate('SavedItems')}
        />
        <MenuRow
          border
          icon="location-outline"
          label="Delivery addresses"
          onPress={() => navigation.navigate('Addresses')}
        />
        <MenuRow
          border
          icon="help-circle-outline"
          label="Help & support"
          onPress={() => navigation.navigate('HelpSupport')}
        />
        <MenuRow
          border
          icon="settings-outline"
          label="Settings"
          onPress={() => navigation.navigate('Settings')}
        />
        <MenuRow
          danger
          icon="log-out-outline"
          label="Sign out"
          onPress={confirmSignOut}
        />
      </Card>
    </Screen>
  );
}

function MetricTile({
  value,
  label,
  onPress,
}: {
  value: string;
  label: string;
  onPress?: () => void;
}) {
  return (
    <Pressable
      accessibilityRole={onPress ? 'button' : undefined}
      disabled={!onPress}
      onPress={onPress}
      style={styles.metricTile}
    >
      <Text style={styles.metricValue}>{value}</Text>
      <Text style={styles.metricLabel}>{label}</Text>
    </Pressable>
  );
}

function MenuRow({
  icon,
  label,
  onPress,
  border,
  danger,
}: {
  icon: IconName;
  label: string;
  onPress: () => void;
  border?: boolean;
  danger?: boolean;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      style={[styles.menuRow, border ? styles.rowBorder : null]}
    >
      <View style={[styles.menuIcon, danger ? styles.menuIconDanger : null]}>
        <Ionicons
          color={danger ? theme.colors.danger : theme.colors.primary}
          name={icon}
          size={18}
        />
      </View>
      <Text style={[styles.menuLabel, danger ? styles.menuLabelDanger : null]}>
        {label}
      </Text>
      <Ionicons
        color={theme.colors.textSubtle}
        name="chevron-forward"
        size={20}
      />
    </Pressable>
  );
}

function StatChip({
  icon,
  label,
  ok,
  okText = 'Verified',
  pendingText = 'Pending',
  onPress,
}: {
  icon: IconName;
  label: string;
  ok: boolean;
  okText?: string;
  pendingText?: string;
  onPress?: () => void;
}) {
  return (
    <Pressable
      accessibilityRole={onPress ? 'button' : undefined}
      disabled={!onPress || ok}
      onPress={onPress}
      style={styles.statChip}
    >
      <View
        style={[
          styles.statIcon,
          { backgroundColor: ok ? theme.colors.secondarySoft : theme.colors.accentSoft },
        ]}
      >
        <Ionicons
          color={ok ? theme.colors.secondary : theme.colors.warning}
          name={icon}
          size={16}
        />
      </View>
      <View style={styles.statCopy}>
        <Text style={styles.statLabel}>{label}</Text>
        <Text
          numberOfLines={1}
          style={[
            styles.statValue,
            { color: ok ? theme.colors.success : theme.colors.warning },
          ]}
        >
          {ok ? okText : pendingText}
        </Text>
      </View>
      {!ok && onPress ? (
        <Ionicons
          color={theme.colors.textSubtle}
          name="chevron-forward"
          size={15}
        />
      ) : null}
    </Pressable>
  );
}

function tint(color: string): string {
  if (color === theme.colors.secondary) {
    return theme.colors.secondarySoft;
  }
  if (color === theme.colors.accent) {
    return theme.colors.accentSoft;
  }
  return theme.colors.primarySoft;
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  screen: {
    paddingTop: theme.spacing.sm,
  },
  hero: {
    alignItems: 'center',
    backgroundColor: theme.colors.heroSurface,
    borderRadius: theme.radius.xl,
    paddingBottom: theme.spacing.lg,
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.xl,
    ...theme.shadows.elevated,
  },
  gear: {
    alignItems: 'center',
    backgroundColor: theme.colors.glassLight,
    borderRadius: theme.radius.pill,
    height: 40,
    justifyContent: 'center',
    position: 'absolute',
    right: theme.spacing.md,
    top: theme.spacing.md,
    width: 40,
  },
  shareButton: {
    left: theme.spacing.md,
    right: undefined,
  },
  cameraBadge: {
    alignItems: 'center',
    backgroundColor: theme.colors.white,
    borderColor: theme.colors.primaryDark,
    borderRadius: theme.radius.pill,
    borderWidth: 2,
    bottom: 0,
    height: 26,
    justifyContent: 'center',
    left: 0,
    position: 'absolute',
    width: 26,
  },
  heroSkeleton: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderRadius: theme.radius.xl,
    gap: theme.spacing.md,
    marginBottom: theme.spacing.lg,
    paddingVertical: theme.spacing.xl,
    ...theme.shadows.soft,
  },
  skeletonAvatar: {
    borderRadius: theme.radius.pill,
  },
  skeletonCard: {
    gap: theme.spacing.md,
    marginBottom: theme.spacing.md,
  },
  metricRow: {
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginTop: theme.spacing.lg,
  },
  metricTile: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    flex: 1,
    paddingVertical: theme.spacing.md,
    ...theme.shadows.soft,
  },
  metricValue: {
    color: theme.colors.primaryDark,
    fontSize: 20,
    fontWeight: '900',
  },
  metricLabel: {
    color: theme.colors.textMuted,
    fontSize: 11.5,
    fontWeight: '800',
    letterSpacing: 0.4,
    marginTop: 2,
    textTransform: 'uppercase',
  },
  avatarRing: {
    alignItems: 'center',
    backgroundColor: theme.colors.glassLight,
    borderColor: theme.colors.glassBorder,
    borderRadius: theme.radius.xl,
    borderWidth: 3,
    justifyContent: 'center',
    marginBottom: theme.spacing.md,
    padding: 5,
  },
  typeBadge: {
    alignItems: 'center',
    borderColor: theme.colors.primaryDark,
    borderRadius: theme.radius.pill,
    borderWidth: 3,
    bottom: 0,
    height: 30,
    justifyContent: 'center',
    position: 'absolute',
    right: 0,
    width: 30,
  },
  name: {
    color: theme.colors.white,
    fontSize: 22,
    fontWeight: '900',
    letterSpacing: -0.4,
    textAlign: 'center',
  },
  username: {
    color: theme.colors.onPrimaryMuted,
    fontSize: 13,
    fontWeight: '800',
    marginTop: 2,
  },
  heroSubtitle: {
    color: theme.colors.onPrimarySubtle,
    fontSize: 13,
    marginTop: 4,
    maxWidth: '90%',
    textAlign: 'center',
  },
  pillRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginTop: theme.spacing.md,
  },
  pill: {
    alignItems: 'center',
    borderRadius: theme.radius.pill,
    flexDirection: 'row',
    gap: 5,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: 7,
  },
  pillOutline: {
    backgroundColor: theme.colors.glassLight,
    borderColor: theme.colors.glassBorder,
    borderWidth: 1,
  },
  pillVerified: {
    backgroundColor: theme.colors.white,
  },
  pillText: {
    color: theme.colors.white,
    fontSize: 12,
    fontWeight: '900',
    letterSpacing: 0.2,
  },
  pillOutlineText: {
    color: theme.colors.onPrimaryMuted,
  },
  pillVerifiedText: {
    color: theme.colors.success,
  },
  editButton: {
    marginTop: theme.spacing.lg,
  },
  statRow: {
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginTop: theme.spacing.lg,
  },
  statChip: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1,
    flex: 1,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
    ...theme.shadows.soft,
  },
  statIcon: {
    alignItems: 'center',
    borderRadius: theme.radius.md,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  statCopy: {
    flex: 1,
  },
  statLabel: {
    color: theme.colors.textSubtle,
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.4,
    textTransform: 'uppercase',
  },
  statValue: {
    fontSize: 13,
    fontWeight: '900',
    marginTop: 2,
  },
  sectionTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.section,
    fontWeight: '900',
    marginBottom: theme.spacing.md,
    marginTop: theme.spacing.lg,
  },
  detailCard: {
    paddingVertical: 0,
  },
  detailRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  rowBorder: {
    borderBottomColor: theme.colors.border,
    borderBottomWidth: 1,
  },
  detailIcon: {
    alignItems: 'center',
    borderRadius: theme.radius.md,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  detailLabel: {
    color: theme.colors.textMuted,
    fontSize: 14,
    fontWeight: '700',
  },
  detailValue: {
    color: theme.colors.text,
    flex: 1,
    fontSize: 14,
    fontWeight: '800',
    textAlign: 'right',
  },
  ctaCard: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderRadius: theme.radius.lg,
    borderWidth: 1.5,
    flexDirection: 'row',
    gap: theme.spacing.md,
    marginTop: theme.spacing.lg,
    padding: theme.spacing.md,
    ...theme.shadows.soft,
  },
  ctaIcon: {
    alignItems: 'center',
    borderRadius: theme.radius.md,
    height: 46,
    justifyContent: 'center',
    width: 46,
  },
  ctaCopy: {
    flex: 1,
  },
  ctaTitle: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  ctaText: {
    color: theme.colors.textMuted,
    fontSize: 12.5,
    lineHeight: 17,
    marginTop: 2,
  },
  menuCard: {
    marginTop: theme.spacing.lg,
    paddingVertical: 0,
  },
  menuRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
    paddingVertical: theme.spacing.md,
  },
  menuIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    height: 38,
    justifyContent: 'center',
    width: 38,
  },
  menuIconDanger: {
    backgroundColor: theme.colors.dangerSoft,
  },
  menuLabel: {
    color: theme.colors.text,
    flex: 1,
    fontSize: 15,
    fontWeight: '800',
  },
  menuLabelDanger: {
    color: theme.colors.danger,
  },
});
