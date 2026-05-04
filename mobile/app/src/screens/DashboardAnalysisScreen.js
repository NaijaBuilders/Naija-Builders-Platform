import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { ErrorBanner, LoadingState, MetricCard, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { spacing } from '../styles/theme';

// Maps to GET /api/mobile/dashboard/analysis (Mobile/DashboardController@analysis).
export default function DashboardAnalysisScreen() {
  const [metrics, setMetrics] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadAnalysis = async () => {
      try {
        const response = await api.getDashboardAnalysis();
        setMetrics(response.data.metrics || null);
      } catch (err) {
        setError(err?.response?.data?.message || 'Unable to load analytics.');
      } finally {
        setIsLoading(false);
      }
    };

    loadAnalysis();
  }, []);

  return (
    <ScreenWrapper>
      <ScreenHeader title="Analytics Snapshot" subtitle="Mobile summary of listing health and inventory risk." />
      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading analytics..." /> : null}
      {metrics ? (
        <View style={styles.metricGrid}>
          <MetricCard label="Active Listing Rate" value={`${metrics.activeListingRate}%`} tone="success" />
          <MetricCard label="Low Stock Rate" value={`${metrics.lowStockRate}%`} tone="warning" />
          <MetricCard label="Out of Stock Rate" value={`${metrics.outOfStockRate}%`} />
          <MetricCard label="Unread Messages" value={metrics.unreadMessages} tone="accent" />
        </View>
      ) : null}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  metricGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
});
