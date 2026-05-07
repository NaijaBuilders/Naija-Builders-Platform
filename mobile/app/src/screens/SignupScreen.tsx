import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Badge, Button, Card, Header, Input, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import type { AuthStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { UserRole } from '../types';

type SignupNavigation = NativeStackNavigationProp<AuthStackParamList, 'Signup'>;

export function SignupScreen() {
  const navigation = useNavigation<SignupNavigation>();
  const { register } = useAppState();
  const [role, setRole] = useState<UserRole>('buyer');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [location, setLocation] = useState('');
  const [company, setCompany] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleCreateAccount = async () => {
    setLoading(true);
    setError('');

    try {
      if (password !== confirmPassword) {
        throw new Error('Passwords do not match.');
      }

      await register({
        company: company.trim() || undefined,
        confirmPassword,
        email: email.trim(),
        location: location.trim(),
        name: name.trim(),
        password,
        phone: phone.trim(),
        role,
        termsAccepted,
      });
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Registration failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Screen>
      <Header
        eyebrow="New account"
        title="Signup"
        subtitle="Create a buyer or supplier account."
      />

      <Card style={styles.card}>
        <Input label="Full name" onChangeText={setName} value={name} />
        <Input
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          onChangeText={setEmail}
          value={email}
        />
        <Input
          keyboardType="phone-pad"
          label="Phone"
          onChangeText={setPhone}
          value={phone}
        />
        <Input label="Location" onChangeText={setLocation} value={location} />
        <Input
          label="Company"
          onChangeText={setCompany}
          value={company}
        />
        <Input
          label="Password"
          onChangeText={setPassword}
          secureTextEntry
          value={password}
        />
        <Input
          label="Confirm password"
          onChangeText={setConfirmPassword}
          secureTextEntry
          value={confirmPassword}
        />

        <Text style={styles.label}>Account type</Text>
        <View style={styles.roleRow}>
          {(['buyer', 'supplier'] as UserRole[]).map((item) => (
            <Pressable
              key={item}
              onPress={() => setRole(item)}
              style={[
                styles.roleButton,
                role === item ? styles.roleButtonActive : null,
              ]}
            >
              <Badge
                label={item === 'buyer' ? 'Buyer' : 'Supplier'}
                tone={role === item ? 'primary' : 'neutral'}
              />
            </Pressable>
          ))}
        </View>

        <Pressable
          onPress={() => setTermsAccepted((current) => !current)}
          style={styles.termsRow}
        >
          <View style={[styles.checkbox, termsAccepted ? styles.checkboxActive : null]}>
            {termsAccepted ? <Text style={styles.checkmark}>OK</Text> : null}
          </View>
          <Text style={styles.termsText}>I accept the NaijaBuilders terms.</Text>
        </Pressable>

        {error ? <Text style={styles.error}>{error}</Text> : null}
        <Button
          title="Create account"
          onPress={handleCreateAccount}
          loading={loading}
          disabled={
            !name.trim() ||
            !email.trim() ||
            !phone.trim() ||
            !location.trim() ||
            password.length < 8 ||
            !confirmPassword ||
            !termsAccepted
          }
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
  card: {
    gap: theme.spacing.md,
  },
  label: {
    color: theme.colors.text,
    fontSize: 13,
    fontWeight: '900',
  },
  roleRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  roleButton: {
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    borderWidth: 1,
    flex: 1,
    padding: theme.spacing.sm,
  },
  roleButtonActive: {
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.primary,
  },
  termsRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  checkbox: {
    alignItems: 'center',
    borderColor: theme.colors.borderStrong,
    borderRadius: 6,
    borderWidth: 1,
    height: 24,
    justifyContent: 'center',
    width: 24,
  },
  checkboxActive: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  checkmark: {
    color: theme.colors.white,
    fontSize: 9,
    fontWeight: '900',
  },
  termsText: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13,
    lineHeight: 18,
  },
  error: {
    color: theme.colors.danger,
    fontSize: theme.typography.small,
    fontWeight: '800',
    textAlign: 'center',
  },
});
