import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/dashboard (Mobile/DashboardController@index).
export default function DashboardScreen() {
  const [metrics, setMetrics] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadDashboard = async () => {
      try {
        const response = await api.getDashboard();
        setMetrics(response.data.metrics || null);
      } catch (err) {
        setError('Unable to load dashboard.');
      }
    };

    loadDashboard();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Supplier Dashboard</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {!metrics ? null : (
        <View style={styles.card}>
          <Text style={styles.metric}>Total Listings: {metrics.totalListings}</Text>
          <Text style={styles.metric}>Active Listings: {metrics.activeListings}</Text>
          <Text style={styles.metric}>Total Stock: {metrics.totalStock}</Text>
          <Text style={styles.metric}>Unread Messages: {metrics.unreadMessages}</Text>
          <Text style={styles.metric}>Inventory Value: NGN {metrics.inventoryValue}</Text>
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
