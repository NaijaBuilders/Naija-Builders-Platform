import { Ionicons } from '@expo/vector-icons';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Button, Card, Input, Screen } from '../components';
import type { AuthStackParamList } from '../navigation/types';
import { authService } from '../services';
import { theme } from '../theme';

type ForgotNavigation = NativeStackNavigationProp<
  AuthStackParamList,
  'ForgotPassword'
>;

export function ForgotPasswordScreen() {
  const navigation = useNavigation<ForgotNavigation>();
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [notice, setNotice] = useState('');

  const handleReset = async () => {
    setLoading(true);
    setNotice('');

    try {
      const result = await authService.requestPasswordReset(email.trim());
      setNotice(result.message);
    } catch (reason) {
      setNotice(reason instanceof Error ? reason.message : 'Request failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Screen contentContainerStyle={styles.screen}>
      <View style={styles.iconCircle}>
        <Ionicons
          color={theme.colors.primary}
          name="lock-closed-outline"
          size={30}
        />
      </View>
      <Text style={styles.title}>Reset your password</Text>
      <Text style={styles.subtitle}>
        Enter the email linked to your account and we'll help you recover access.
      </Text>

      <Card style={styles.card}>
        <Input
          autoCapitalize="none"
          autoCorrect={false}
          keyboardType="email-address"
          label="Email"
          placeholder="you@example.com"
          onChangeText={setEmail}
          value={email}
        />
        {notice ? (
          <View style={styles.noticeBanner}>
            <Ionicons
              color={theme.colors.primary}
              name="information-circle"
              size={18}
            />
            <Text style={styles.notice}>{notice}</Text>
          </View>
        ) : null}
        <Button
          title="Send reset link"
          size="lg"
          onPress={handleReset}
          loading={loading}
          disabled={!email.trim()}
        />
      </Card>

      <Pressable
        accessibilityRole="button"
        onPress={() => navigation.navigate('Login')}
        style={styles.backRow}
      >
        <Ionicons color={theme.colors.primary} name="arrow-back" size={16} />
        <Text style={styles.backText}>Back to login</Text>
      </Pressable>
    </Screen>
  );
}

const styles = StyleSheet.create({
  screen: {
    justifyContent: 'center',
  },
  iconCircle: {
    alignItems: 'center',
    alignSelf: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    height: 72,
    justifyContent: 'center',
    marginBottom: theme.spacing.lg,
    width: 72,
  },
  title: {
    color: theme.colors.text,
    fontSize: theme.typography.title,
    fontWeight: '900',
    letterSpacing: -0.5,
    textAlign: 'center',
  },
  subtitle: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.body,
    lineHeight: 22,
    marginBottom: theme.spacing.lg,
    marginTop: theme.spacing.sm,
    textAlign: 'center',
  },
  card: {
    gap: theme.spacing.md,
  },
  noticeBanner: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.sm,
  },
  notice: {
    color: theme.colors.primaryDark,
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
    lineHeight: 18,
  },
  backRow: {
    alignItems: 'center',
    alignSelf: 'center',
    flexDirection: 'row',
    gap: theme.spacing.xs,
    marginTop: theme.spacing.lg,
  },
  backText: {
    color: theme.colors.primary,
    fontSize: 14,
    fontWeight: '900',
  },
});
