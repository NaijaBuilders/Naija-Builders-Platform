import React, { useContext, useState } from 'react';
import { Pressable, StyleSheet, Switch, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Card, ErrorBanner, ScreenHeader, TextField } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, fonts, spacing } from '../styles/theme';

// Maps to POST /api/mobile/register (Mobile/AuthController@register).
export default function RegisterScreen({ navigation }) {
  const { signIn } = useContext(AuthContext);
  const [accountType, setAccountType] = useState('builder');
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    location: '',
    company: '',
    password: '',
    confirmPassword: '',
  });
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const updateField = (key, value) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const handleRegister = async () => {
    setError('');
    if (!form.name.trim() || !form.email.trim() || !form.phone.trim() || !form.location.trim()) {
      setError('Complete your name, email, phone, and location.');
      return;
    }

    if (!/^(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(form.password)) {
      setError('Password must be at least 8 characters and include a number and special character.');
      return;
    }

    if (!termsAccepted) {
      setError('Accept the Terms & Agreement before creating an account.');
      return;
    }

    setIsSubmitting(true);
    try {
      const response = await api.register({
        name: form.name,
        email: form.email,
        phone: form.phone,
        location: form.location,
        company: form.company,
        password: form.password,
        confirm_password: form.confirmPassword,
        account_type: accountType,
        terms_accepted: termsAccepted,
      });
      await signIn(response.data.token, response.data.user || null);
    } catch (err) {
      const message = err?.response?.data?.message || 'Registration failed.';
      setError(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <ScreenWrapper>
      <ScreenHeader title="Create Your Account" subtitle="Join as a buyer or supplier and keep your project moving." />
      <Card>
        <Text style={styles.sectionTitle}>Account Type</Text>
        <View style={styles.toggleRow}>
          {['builder', 'supplier'].map((type) => (
            <Pressable
              key={type}
              onPress={() => setAccountType(type)}
              style={[styles.toggleChip, accountType === type && styles.toggleChipActive]}
            >
              <Text style={[styles.toggleText, accountType === type && styles.toggleTextActive]}>
                {type === 'builder' ? 'Buyer' : 'Supplier'}
              </Text>
            </Pressable>
          ))}
        </View>

        <TextField label="Full Name" value={form.name} onChangeText={(value) => updateField('name', value)} />

        <TextField
          label="Email"
          autoCapitalize="none"
          keyboardType="email-address"
          value={form.email}
          onChangeText={(value) => updateField('email', value)}
        />

        <TextField label="Phone" keyboardType="phone-pad" value={form.phone} onChangeText={(value) => updateField('phone', value)} />

        <TextField label="Location" value={form.location} onChangeText={(value) => updateField('location', value)} />

        <TextField label={accountType === 'supplier' ? 'Business Name' : 'Company (Optional)'} value={form.company} onChangeText={(value) => updateField('company', value)} />

        <TextField
          label="Password"
          secureTextEntry
          value={form.password}
          onChangeText={(value) => updateField('password', value)}
        />

        <TextField
          label="Confirm Password"
          secureTextEntry
          value={form.confirmPassword}
          onChangeText={(value) => updateField('confirmPassword', value)}
        />

        <View style={styles.switchRow}>
          <Switch value={termsAccepted} onValueChange={setTermsAccepted} />
          <Pressable onPress={() => navigation.navigate('Terms')}>
            <Text style={styles.switchLabel}>I accept the Terms & Agreement</Text>
          </Pressable>
        </View>

        <ErrorBanner message={error} />

        <AppButton label={isSubmitting ? 'Creating...' : 'Create Account'} onPress={handleRegister} disabled={isSubmitting} />
      </Card>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  sectionTitle: {
    fontFamily: fonts.heading,
    fontSize: 16,
    marginBottom: 10,
  },
  toggleRow: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginBottom: 12,
  },
  toggleChip: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 18,
    paddingVertical: 6,
    paddingHorizontal: 14,
  },
  toggleChipActive: {
    backgroundColor: colors.accentSoft,
    borderColor: colors.accent,
  },
  toggleText: {
    color: colors.muted,
  },
  toggleTextActive: {
    color: colors.accent,
    fontWeight: '600',
  },
  switchRow: {
    marginTop: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  switchLabel: {
    color: colors.accent,
    fontWeight: '600',
  },
});
