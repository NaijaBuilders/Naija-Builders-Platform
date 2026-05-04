import React, { useContext, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { Badge, Card, EmptyState, ErrorBanner, LoadingState, MetricCard, QuickAction, ScreenHeader } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';
import { formatMoney, humanize } from '../utils/format';

// Maps to GET /api/mobile/dashboard (Mobile/DashboardController@index).
export default function DashboardScreen({ navigation }) {
  const { user } = useContext(AuthContext);
  const [metrics, setMetrics] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadDashboard = async (refreshing = false) => {
    refreshing ? setIsRefreshing(true) : setIsLoading(true);
    setError('');
    try {
      const response = await api.getDashboard();
      setMetrics(response.data.metrics || null);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to load dashboard.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadDashboard();
  }, []);

  const kycStatus = String(user?.kyc_status || 'pending');
  const kycTone = kycStatus === 'approved' ? 'success' : kycStatus === 'submitted' ? 'warning' : 'danger';

  return (
    <ScreenWrapper refreshing={isRefreshing} onRefresh={() => loadDashboard(true)}>
      <ScreenHeader title={`Hi, ${user?.name || 'Supplier'}`} subtitle="Manage listings, stock, messages, and supplier visibility from mobile." />
      <Card style={styles.kycCard}>
        <View style={styles.rowBetween}>
          <View>
            <Text style={styles.cardTitle}>Supplier KYC</Text>
            <Text style={styles.meta}>Listings unlock when KYC is approved.</Text>
          </View>
          <Badge label={humanize(kycStatus)} tone={kycTone} />
        </View>
      </Card>

      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading supplier dashboard..." /> : null}
      {!isLoading && !metrics ? <EmptyState title="Dashboard Unavailable" body="Pull to refresh or try signing in again." /> : null}
      {metrics ? (
        <View style={styles.metricGrid}>
          <MetricCard label="Total Listings" value={metrics.totalListings} tone="accent" />
          <MetricCard label="Active Listings" value={metrics.activeListings} tone="success" />
          <MetricCard label="Total Stock" value={metrics.totalStock} />
          <MetricCard label="Unread Messages" value={metrics.unreadMessages} tone="warning" />
          <MetricCard label="Inventory Value" value={formatMoney(metrics.inventoryValue)} />
        </View>
      ) : null}

      <View style={styles.quickGrid}>
        <QuickAction label="Create Listing" detail="Add materials with price, stock, and images." onPress={() => navigation.navigate('CreateListing')} />
        <QuickAction label="View Listings" detail="Manage published and inactive products." onPress={() => navigation.navigate('Listings')} />
        <QuickAction label="Supplier KYC" detail="Review business and bank details." onPress={() => navigation.navigate('SupplierKyc')} />
        <QuickAction label="Messages" detail="Reply to buyer enquiries quickly." onPress={() => navigation.navigate('Messages')} />
        <QuickAction label="Analytics" detail="Review listing health and stock risk." onPress={() => navigation.navigate('DashboardAnalysis')} />
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  kycCard: {
    marginBottom: spacing.md,
  },
  rowBetween: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: spacing.sm,
    justifyContent: 'space-between',
  },
  cardTitle: {
    color: colors.ink,
    fontWeight: '800',
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  metricGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
});
