import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET /api/mobile/dashboard/buyer (Mobile/DashboardController@buyer).
export default function BuyerDashboardScreen() {
  const [metrics, setMetrics] = useState(null);
  const [recommended, setRecommended] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadBuyerDashboard = async () => {
      try {
        const response = await api.getBuyerDashboard();
        setMetrics(response.data.metrics || null);
        setRecommended(response.data.recommended_materials || []);
      } catch (err) {
        setError('Unable to load buyer dashboard.');
      }
    };

    loadBuyerDashboard();
  }, []);

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Buyer Dashboard</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {!metrics ? null : (
        <View style={styles.card}>
          <Text style={styles.metric}>Cart Items: {metrics.cart_item_count}</Text>
          <Text style={styles.metric}>Orders: {metrics.order_count}</Text>
          <Text style={styles.metric}>Pending Orders: {metrics.pending_orders}</Text>
          <Text style={styles.metric}>Unread Messages: {metrics.unread_messages}</Text>
          <Text style={styles.metric}>Total Spent: NGN {metrics.total_spent}</Text>
        </View>
      )}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Recommended Materials</Text>
        {recommended.map((item) => (
          <Text key={item.id} style={styles.meta}>
            {item.name} - NGN {item.price}
          </Text>
        ))}
      </View>
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
    marginBottom: 12,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  metric: {
    color: colors.ink,
    marginBottom: 8,
  },
  sectionTitle: {
    fontFamily: fonts.heading,
    marginBottom: 8,
  },
  meta: {
    color: colors.muted,
    marginBottom: 4,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
});
