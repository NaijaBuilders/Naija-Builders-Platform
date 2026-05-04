import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Badge, Card, ErrorBanner, LoadingState, ScreenHeader } from '../components/ui';
import { api } from '../services/api';
import { colors, spacing } from '../styles/theme';

// Maps to GET/POST /api/mobile/subscription (Mobile/SubscriptionController).
export default function SubscriptionScreen() {
  const [plans, setPlans] = useState([]);
  const [activePlan, setActivePlan] = useState('standard');
  const [role, setRole] = useState('builder');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadPlans = async () => {
      try {
        const response = await api.getSubscription();
        setPlans(response.data.plans || []);
        setActivePlan(response.data.active_plan || 'standard');
        setRole(response.data.role || 'builder');
      } catch (err) {
        setError(err?.response?.data?.message || 'Unable to load subscription plans.');
      } finally {
        setIsLoading(false);
      }
    };

    loadPlans();
  }, []);

  const handleSelect = async (planCode) => {
    try {
      const response = await api.updateSubscription({ subscription_plan: planCode });
      setActivePlan(response.data.active_plan || planCode);
    } catch (err) {
      setError(err?.response?.data?.message || 'Unable to update plan.');
    }
  };

  return (
    <ScreenWrapper>
      <ScreenHeader title="Subscription" subtitle={role === 'supplier' ? 'Supplier marketplace plans and visibility tools.' : 'Buyer plans for saved products, priority messaging, and procurement workflows.'} />
      <ErrorBanner message={error} />
      {isLoading ? <LoadingState label="Loading plans..." /> : null}
      {plans.map((plan) => (
        <Card key={plan.code} style={styles.cardGap}>
          <View style={styles.planHeader}>
            <Text style={styles.cardTitle}>{plan.name}</Text>
            {activePlan === plan.code ? <Badge label="Current" tone="success" /> : null}
          </View>
          <Text style={styles.price}>{plan.price}</Text>
          <Text style={styles.meta}>{plan.description}</Text>
          {(plan.benefits || []).map((benefit) => <Text key={benefit} style={styles.benefit}>- {benefit}</Text>)}
          <AppButton
            label={activePlan === plan.code ? 'Current Plan' : 'Select Plan'}
            variant={activePlan === plan.code ? 'soft' : 'primary'}
            onPress={() => handleSelect(plan.code)}
          />
        </Card>
      ))}
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  cardGap: {
    marginBottom: spacing.md,
  },
  planHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: spacing.sm,
    justifyContent: 'space-between',
  },
  cardTitle: {
    color: colors.ink,
    flex: 1,
    fontSize: 18,
    fontWeight: '800',
  },
  price: {
    color: colors.accent,
    fontSize: 18,
    fontWeight: '800',
    marginTop: 6,
  },
  meta: {
    color: colors.muted,
    marginTop: 4,
  },
  benefit: {
    color: colors.ink,
    marginTop: 6,
  },
});
