import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { Button, Card, Header, Input, Screen } from '../components';
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
    <Screen scroll={false} contentContainerStyle={styles.screen}>
      <Card style={styles.card}>
        <Header
          eyebrow="Password recovery"
          title="Forgot password"
          subtitle="Check the current password recovery status for your account."
        />
        <Input
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          onChangeText={setEmail}
          value={email}
        />
        {notice ? <Text style={styles.notice}>{notice}</Text> : null}
        <Button
          title="Send reset link"
          onPress={handleReset}
          loading={loading}
          disabled={!email.trim()}
        />
        <Button
          title="Back to login"
          onPress={() => navigation.navigate('Login')}
          variant="ghost"
        />
      </Card>
    </Screen>
  );
}

const styles = StyleSheet.create({
  screen: {
    justifyContent: 'center',
  },
  card: {
    gap: theme.spacing.md,
  },
  notice: {
    color: theme.colors.primary,
    fontSize: theme.typography.small,
    fontWeight: '800',
    textAlign: 'center',
  },
});
