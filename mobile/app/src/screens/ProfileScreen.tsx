import type { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useCallback } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import {
  Avatar,
  Badge,
  Button,
  Card,
  FloatingBackButton,
  Header,
  Loader,
  Screen,
} from '../components';
import { useAppState } from '../context/AppContext';
import { useCompany } from '../hooks/useMarketplaceData';
import type { MainTabParamList, ProfileStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { UserRole } from '../types';

const roleLabels: Record<UserRole, string> = {
  buyer: 'Buyer',
  supplier: 'Supplier',
};

export function ProfileScreen() {
  const navigation =
    useNavigation<NativeStackNavigationProp<ProfileStackParamList, 'ProfileMain'>>();
  const { currentRole, signOut, user } = useAppState();
  const { data: company } = useCompany();
  const goHome = useCallback(() => {
    navigation
      .getParent<BottomTabNavigationProp<MainTabParamList>>()
      ?.navigate('Home', { screen: 'HomeMain' });
  }, [navigation]);

  if (!user) {
    return (
      <Screen
        scroll={false}
        contentContainerStyle={styles.center}
        floating={
          <FloatingBackButton fallback={goHome} hideWhenUnavailable={false} />
        }
      >
        <Loader label="Loading profile" />
      </Screen>
    );
  }

  return (
    <Screen
      contentContainerStyle={styles.contentWithFloatingBack}
      floating={
        <FloatingBackButton fallback={goHome} hideWhenUnavailable={false} />
      }
    >
      <Header
        eyebrow="Account"
        title="Profile"
        subtitle="Your account and company information."
      />

      <Card style={styles.profileCard}>
        <Avatar name={user.name} size={64} />
        <View style={styles.profileCopy}>
          <Text style={styles.name}>{user.name}</Text>
          <Text style={styles.email}>{user.email}</Text>
          <View style={styles.badgeRow}>
            <Badge label={roleLabels[currentRole]} tone="primary" />
            {company?.verified ? <Badge label="Verified" tone="success" /> : null}
          </View>
        </View>
      </Card>

      <Card style={styles.detailCard}>
        <View style={styles.detailRow}>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Company</Text>
            <Text numberOfLines={2} style={styles.detailValue}>
              {company?.name || user.company || 'Not set'}
            </Text>
          </View>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Industry</Text>
            <Text numberOfLines={2} style={styles.detailValue}>
              {company?.industry || 'Not set'}
            </Text>
          </View>
        </View>
        <View style={styles.detailRow}>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Location</Text>
            <Text numberOfLines={2} style={styles.detailValue}>
              {company?.city || user.location || 'Not set'}
            </Text>
          </View>
          <View style={styles.detailItem}>
            <Text style={styles.detailLabel}>Phone</Text>
            <Text numberOfLines={2} style={styles.detailValue}>
              {user.phone || 'Not set'}
            </Text>
          </View>
        </View>
      </Card>

      {currentRole === 'supplier' ? (
        <View style={styles.verificationAction}>
          <Button
            title="Supplier verification"
            onPress={() => navigation.navigate('SupplierOnboarding')}
            variant={company?.verified ? 'outline' : 'primary'}
          />
        </View>
      ) : null}
      {currentRole === 'buyer' ? (
        <View style={styles.verificationAction}>
          <Button
            title="Account verification"
            onPress={() => navigation.navigate('BuyerVerification')}
            variant={
              user.email_confirmed && user.phone_confirmed ? 'outline' : 'primary'
            }
          />
        </View>
      ) : null}

      <View style={styles.actions}>
        <Button
          title="Settings"
          onPress={() => navigation.navigate('Settings')}
          variant="primary"
          style={styles.actionButton}
        />
        <Button
          title="Sign out"
          onPress={signOut}
          variant="ghost"
          style={styles.actionButton}
        />
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  center: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentWithFloatingBack: {
    paddingTop: 86,
  },
  profileCard: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  profileCopy: {
    flex: 1,
  },
  name: {
    color: theme.colors.text,
    fontSize: 20,
    fontWeight: '900',
  },
  email: {
    color: theme.colors.textMuted,
    fontSize: 13,
    marginTop: 4,
  },
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: theme.spacing.xs,
    marginTop: theme.spacing.sm,
  },
  detailCard: {
    gap: theme.spacing.md,
    marginTop: theme.spacing.md,
  },
  detailRow: {
    flexDirection: 'row',
    gap: theme.spacing.md,
  },
  detailItem: {
    backgroundColor: theme.colors.surfaceMuted,
    borderRadius: theme.radius.md,
    flex: 1,
    padding: theme.spacing.md,
  },
  detailLabel: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  detailValue: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '800',
    marginTop: 5,
  },
  actions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginTop: theme.spacing.lg,
  },
  verificationAction: {
    marginTop: theme.spacing.md,
  },
  actionButton: {
    flex: 1,
  },
});
