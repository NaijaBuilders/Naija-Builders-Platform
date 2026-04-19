import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/dashboard/analysis (Mobile/DashboardController@analysis).
export default function DashboardAnalysisScreen() {
  const [metrics, setMetrics] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadAnalysis = async () => {
      try {
        const response = await api.getDashboardAnalysis();
        setMetrics(response.data.metrics || null);
      } catch (err) {
        setError('Unable to load analytics.');
      }
    };

    loadAnalysis();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Analytics Snapshot</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {!metrics ? null : (
        <View style={styles.card}>
          <Text style={styles.metric}>Active Listing Rate: {metrics.activeListingRate}%</Text>
          <Text style={styles.metric}>Low Stock Rate: {metrics.lowStockRate}%</Text>
          <Text style={styles.metric}>Out of Stock Rate: {metrics.outOfStockRate}%</Text>
          <Text style={styles.metric}>Unread Messages: {metrics.unreadMessages}</Text>
        </View>
      )}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  title: {
    fontSize: 24,
    fontFamily: fonts.heading,
    color: colors.ink,
    marginBottom: 12,
  },
  card: {
    padding: 16,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  metric: {
    color: colors.ink,
    marginBottom: 8,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
});
