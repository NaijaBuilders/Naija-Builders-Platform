import { Ionicons } from '@expo/vector-icons';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Button, Card, Input, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import type { AuthStackParamList } from '../navigation/types';
import { theme } from '../theme';
import type { SignupAccountType } from '../types';

type SignupNavigation = NativeStackNavigationProp<AuthStackParamList, 'Signup'>;

type IconName = React.ComponentProps<typeof Ionicons>['name'];

const signupAccountTypes: Array<{
  label: string;
  value: SignupAccountType;
  icon: IconName;
  blurb: string;
}> = [
  { value: 'buyer', label: 'Buyer', icon: 'cart-outline', blurb: 'Purchase materials' },
  { value: 'supplier', label: 'Supplier', icon: 'storefront-outline', blurb: 'Sell materials' },
  {
    value: 'service_provider',
    label: 'Offer services',
    icon: 'construct-outline',
    blurb: 'Trades & pros',
  },
];

export function SignupScreen() {
  const navigation = useNavigation<SignupNavigation>();
  const { register } = useAppState();
  const [role, setRole] = useState<SignupAccountType>('buyer');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [username, setUsername] = useState('');
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
        username: username.trim() || undefined,
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
      <View style={styles.hero}>
        <Text style={styles.eyebrow}>NEW ACCOUNT</Text>
        <Text style={styles.heroTitle}>Create your account</Text>
        <Text style={styles.heroSubtitle}>
          Join NaijaBuilders as a buyer, supplier, or service professional.
        </Text>
      </View>

      <Text style={styles.sectionLabel}>I want to</Text>
      <View style={styles.roleRow}>
        {signupAccountTypes.map((item) => {
          const active = role === item.value;
          return (
            <Pressable
              key={item.value}
              onPress={() => setRole(item.value)}
              style={[styles.roleCard, active ? styles.roleCardActive : null]}
            >
              <View
                style={[styles.roleIcon, active ? styles.roleIconActive : null]}
              >
                <Ionicons
                  color={active ? theme.colors.white : theme.colors.primary}
                  name={item.icon}
                  size={20}
                />
              </View>
              <Text style={styles.roleLabel}>{item.label}</Text>
              <Text style={styles.roleBlurb}>{item.blurb}</Text>
            </Pressable>
          );
        })}
      </View>

      <Card style={styles.card}>
        <Text style={styles.cardSection}>Personal details</Text>
        <Input label="Full name" placeholder="John Doe" onChangeText={setName} value={name} />
        <Input
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          placeholder="you@example.com"
          onChangeText={setEmail}
          value={email}
        />
        <Input
          autoCapitalize="none"
          autoCorrect={false}
          label="Username (optional)"
          placeholder="your_username"
          onChangeText={setUsername}
          value={username}
        />
        <Text style={styles.helperText}>
          Sign in with this later. Leave blank and we'll create one from your
          email.
        </Text>
        <Input
          keyboardType="phone-pad"
          label="Phone"
          placeholder="+234 800 000 0000"
          onChangeText={setPhone}
          value={phone}
        />
        <Input
          label="Location"
          placeholder="Lagos"
          onChangeText={setLocation}
          value={location}
        />
        <Input
          label="Company (optional)"
          placeholder="Your business name"
          onChangeText={setCompany}
          value={company}
        />

        <View style={styles.divider} />
        <Text style={styles.cardSection}>Security</Text>
        <Input
          label="Password"
          placeholder="At least 8 characters"
          onChangeText={setPassword}
          secureTextEntry
          value={password}
        />
        <Input
          label="Confirm password"
          placeholder="Re-enter password"
          onChangeText={setConfirmPassword}
          secureTextEntry
          value={confirmPassword}
        />

        {role === 'service_provider' ? (
          <View style={styles.infoBanner}>
            <Ionicons
              color={theme.colors.primary}
              name="information-circle-outline"
              size={18}
            />
            <Text style={styles.infoText}>
              You'll use the supplier workspace, and NaijaBuilders can route
              service enquiries to you after review.
            </Text>
          </View>
        ) : null}

        <Pressable
          onPress={() => setTermsAccepted((current) => !current)}
          style={styles.termsRow}
        >
          <View
            style={[styles.checkbox, termsAccepted ? styles.checkboxActive : null]}
          >
            {termsAccepted ? (
              <Ionicons color={theme.colors.white} name="checkmark" size={14} />
            ) : null}
          </View>
          <Text style={styles.termsText}>
            I accept the NaijaBuilders terms and privacy policy.
          </Text>
        </Pressable>

        {error ? (
          <View style={styles.errorBanner}>
            <Ionicons color={theme.colors.danger} name="alert-circle" size={18} />
            <Text style={styles.errorText}>{error}</Text>
          </View>
        ) : null}

        <Button
          title="Create account"
          size="lg"
          onPress={handleCreateAccount}
          loading={loading}
          disabled={
            !name.trim() ||
            !email.trim() ||
            !phone.trim() ||
            (role !== 'buyer' && !location.trim()) ||
            password.length < 8 ||
            !confirmPassword ||
            !termsAccepted
          }
        />
      </Card>

      <View style={styles.loginRow}>
        <Text style={styles.loginText}>Already have an account? </Text>
        <Pressable
          accessibilityRole="button"
          onPress={() => navigation.navigate('Login')}
        >
          <Text style={styles.loginLink}>Sign in</Text>
        </Pressable>
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  hero: {
    marginBottom: theme.spacing.lg,
  },
  eyebrow: {
    color: theme.colors.primary,
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 1.2,
    marginBottom: 6,
  },
  heroTitle: {
    color: theme.colors.text,
    fontSize: theme.typography.title,
    fontWeight: '900',
    letterSpacing: -0.5,
  },
  heroSubtitle: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.body,
    lineHeight: 22,
    marginTop: theme.spacing.xs,
  },
  sectionLabel: {
    color: theme.colors.textSubtle,
    fontSize: 12,
    fontWeight: '900',
    letterSpacing: 0.6,
    marginBottom: theme.spacing.sm,
    textTransform: 'uppercase',
  },
  roleRow: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
    marginBottom: theme.spacing.lg,
  },
  roleCard: {
    alignItems: 'center',
    backgroundColor: theme.colors.surface,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.lg,
    borderWidth: 1.5,
    flex: 1,
    gap: 4,
    paddingHorizontal: theme.spacing.xs,
    paddingVertical: theme.spacing.md,
  },
  roleCardActive: {
    backgroundColor: theme.colors.primarySoft,
    borderColor: theme.colors.primary,
  },
  roleIcon: {
    alignItems: 'center',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.pill,
    height: 40,
    justifyContent: 'center',
    marginBottom: 4,
    width: 40,
  },
  roleIconActive: {
    backgroundColor: theme.colors.primary,
  },
  roleLabel: {
    color: theme.colors.text,
    fontSize: 13,
    fontWeight: '900',
    textAlign: 'center',
  },
  roleBlurb: {
    color: theme.colors.textMuted,
    fontSize: 11,
    textAlign: 'center',
  },
  card: {
    gap: theme.spacing.md,
  },
  cardSection: {
    color: theme.colors.text,
    fontSize: 15,
    fontWeight: '900',
  },
  helperText: {
    color: theme.colors.textMuted,
    fontSize: 12,
    lineHeight: 17,
    marginTop: -8,
  },
  divider: {
    backgroundColor: theme.colors.border,
    height: 1,
    marginVertical: theme.spacing.xs,
  },
  infoBanner: {
    alignItems: 'flex-start',
    backgroundColor: theme.colors.primarySoft,
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.sm,
  },
  infoText: {
    color: theme.colors.primaryDark,
    flex: 1,
    fontSize: 13,
    lineHeight: 18,
  },
  termsRow: {
    alignItems: 'center',
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  checkbox: {
    alignItems: 'center',
    borderColor: theme.colors.borderStrong,
    borderRadius: 7,
    borderWidth: 2,
    height: 26,
    justifyContent: 'center',
    width: 26,
  },
  checkboxActive: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  termsText: {
    color: theme.colors.textMuted,
    flex: 1,
    fontSize: 13,
    lineHeight: 18,
  },
  errorBanner: {
    alignItems: 'center',
    backgroundColor: '#FFE9E5',
    borderRadius: theme.radius.md,
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.sm,
  },
  errorText: {
    color: theme.colors.danger,
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
  },
  loginRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: theme.spacing.lg,
  },
  loginText: {
    color: theme.colors.textMuted,
    fontSize: 14,
  },
  loginLink: {
    color: theme.colors.primary,
    fontSize: 14,
    fontWeight: '900',
  },
});
