import React, { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to GET/POST /api/mobile/subscription (Mobile/SubscriptionController).
export default function SubscriptionScreen() {
  const [plans, setPlans] = useState([]);
  const [activePlan, setActivePlan] = useState('standard');
  const [error, setError] = useState('');

  useEffect(() => {
    const loadPlans = async () => {
      try {
        const response = await api.getSubscription();
        setPlans(response.data.plans || []);
        setActivePlan(response.data.active_plan || 'standard');
      } catch (err) {
        setError('Unable to load subscription plans.');
      }
    };

    loadPlans();
  }, []);

  const handleSelect = async (planCode) => {
    try {
      const response = await api.updateSubscription({ subscription_plan: planCode });
      setActivePlan(response.data.active_plan || planCode);
    } catch (err) {
      setError('Unable to update plan.');
    }
  };

  return (
    <ScreenWrapper>
      <Text style={styles.title}>Subscription</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {plans.map((plan) => (
        <View key={plan.code} style={styles.card}>
          <Text style={styles.cardTitle}>{plan.name}</Text>
          <Text style={styles.meta}>{plan.price}</Text>
          <Text style={styles.meta}>{plan.description}</Text>
          <Pressable
            style={[styles.primaryButton, activePlan === plan.code && styles.primaryButtonActive]}
            onPress={() => handleSelect(plan.code)}
          >
            <Text
              style={[
                styles.primaryButtonText,
                activePlan === plan.code && styles.primaryButtonTextActive,
              ]}
            >
              {activePlan === plan.code ? 'Current Plan' : 'Select Plan'}
            </Text>
          </Pressable>
        </View>
      ))}
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
  cardTitle: {
    fontFamily: fonts.heading,
    fontSize: 16,
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  primaryButton: {
    marginTop: 12,
    backgroundColor: colors.accent,
    paddingVertical: 10,
    borderRadius: 12,
    alignItems: 'center',
  },
  primaryButtonActive: {
    backgroundColor: colors.accentSoft,
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  primaryButtonTextActive: {
    color: colors.accent,
  },
  error: {
    color: '#B23A3A',
    marginBottom: 8,
  },
});
