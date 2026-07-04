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

type Step = 'email' | 'reset' | 'done';

export function ForgotPasswordScreen() {
  const navigation = useNavigation<ForgotNavigation>();
  const [step, setStep] = useState<Step>('email');
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [notice, setNotice] = useState('');
  const [error, setError] = useState('');

  const sendCode = async () => {
    setLoading(true);
    setNotice('');
    setError('');

    try {
      const result = await authService.requestPasswordReset(email.trim());
      setNotice(
        result.debugCode
          ? `${result.message} Local test code: ${result.debugCode}`
          : result.message
      );
      setStep('reset');
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Request failed.');
    } finally {
      setLoading(false);
    }
  };

  const resetPassword = async () => {
    if (password !== confirmPassword) {
      setError('The passwords do not match.');
      return;
    }

    setLoading(true);
    setNotice('');
    setError('');

    try {
      const message = await authService.resetPassword(
        email.trim(),
        code.trim(),
        password
      );
      setNotice(message);
      setStep('done');
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Reset failed.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Screen contentContainerStyle={styles.screen}>
      <View style={styles.iconCircle}>
        <Ionicons
          color={theme.colors.primary}
          name={step === 'done' ? 'checkmark-circle-outline' : 'lock-closed-outline'}
          size={30}
        />
      </View>
      <Text style={styles.title}>
        {step === 'done' ? 'Password updated' : 'Reset your password'}
      </Text>
      <Text style={styles.subtitle}>
        {step === 'email'
          ? 'Enter the email linked to your account and we will send you a 6-digit code.'
          : step === 'reset'
            ? `Enter the code we sent to ${email.trim()} and choose a new password.`
            : 'You can now sign in with your new password.'}
      </Text>

      {step === 'email' ? (
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
          {error ? <Text style={styles.error}>{error}</Text> : null}
          <Button
            title="Send reset code"
            size="lg"
            onPress={sendCode}
            loading={loading}
            disabled={!email.trim()}
          />
        </Card>
      ) : null}

      {step === 'reset' ? (
        <Card style={styles.card}>
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
          <Input
            keyboardType="number-pad"
            label="6-digit code"
            maxLength={6}
            onChangeText={setCode}
            value={code}
          />
          <Input
            label="New password"
            onChangeText={setPassword}
            secureTextEntry
            value={password}
          />
          <Input
            label="Confirm new password"
            onChangeText={setConfirmPassword}
            secureTextEntry
            value={confirmPassword}
          />
          {error ? <Text style={styles.error}>{error}</Text> : null}
          <Button
            title="Set new password"
            size="lg"
            onPress={resetPassword}
            loading={loading}
            disabled={
              code.trim().length !== 6 ||
              password.length < 8 ||
              confirmPassword.length < 8
            }
          />
          <Pressable
            accessibilityRole="button"
            onPress={sendCode}
            style={styles.resendRow}
          >
            <Text style={styles.resendText}>Resend code</Text>
          </Pressable>
        </Card>
      ) : null}

      {step === 'done' ? (
        <Card style={styles.card}>
          {notice ? (
            <View style={styles.noticeBanner}>
              <Ionicons
                color={theme.colors.success}
                name="checkmark-circle"
                size={18}
              />
              <Text style={styles.notice}>{notice}</Text>
            </View>
          ) : null}
          <Button
            title="Back to login"
            size="lg"
            onPress={() => navigation.navigate('Login')}
          />
        </Card>
      ) : null}

      {step !== 'done' ? (
        <Pressable
          accessibilityRole="button"
          onPress={() => navigation.navigate('Login')}
          style={styles.backRow}
        >
          <Ionicons color={theme.colors.primary} name="arrow-back" size={16} />
          <Text style={styles.backText}>Back to login</Text>
        </Pressable>
      ) : null}
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
  error: {
    color: theme.colors.danger,
    fontWeight: '800',
    textAlign: 'center',
  },
  resendRow: {
    alignSelf: 'center',
  },
  resendText: {
    color: theme.colors.primary,
    fontSize: 13,
    fontWeight: '900',
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
