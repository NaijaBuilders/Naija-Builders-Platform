import React, { useContext, useState } from 'react';
import { Pressable, StyleSheet, Switch, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to POST /api/mobile/register (Mobile/AuthController@register).
export default function RegisterScreen() {
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
  const [error, setError] = useState('');

  const updateField = (key, value) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const handleRegister = async () => {
    setError('');
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
      await signIn(response.data.token);
    } catch (err) {
      const message = err?.response?.data?.message || 'Registration failed.';
      setError(message);
    }
  };

  return (
    <ScreenWrapper>
      <View style={styles.hero}>
        <Text style={styles.title}>Create Your Account</Text>
        <Text style={styles.subtitle}>Join NaijaBuilders as a buyer or supplier.</Text>
      </View>
      <View style={styles.card}>
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

        <Text style={styles.label}>Full Name</Text>
        <TextInput style={styles.input} value={form.name} onChangeText={(value) => updateField('name', value)} />

        <Text style={styles.label}>Email</Text>
        <TextInput
          style={styles.input}
          autoCapitalize="none"
          keyboardType="email-address"
          value={form.email}
          onChangeText={(value) => updateField('email', value)}
        />

        <Text style={styles.label}>Phone</Text>
        <TextInput style={styles.input} value={form.phone} onChangeText={(value) => updateField('phone', value)} />

        <Text style={styles.label}>Location</Text>
        <TextInput style={styles.input} value={form.location} onChangeText={(value) => updateField('location', value)} />

        <Text style={styles.label}>Company</Text>
        <TextInput style={styles.input} value={form.company} onChangeText={(value) => updateField('company', value)} />

        <Text style={styles.label}>Password</Text>
        <TextInput
          style={styles.input}
          secureTextEntry
          value={form.password}
          onChangeText={(value) => updateField('password', value)}
        />

        <Text style={styles.label}>Confirm Password</Text>
        <TextInput
          style={styles.input}
          secureTextEntry
          value={form.confirmPassword}
          onChangeText={(value) => updateField('confirmPassword', value)}
        />

        <View style={styles.switchRow}>
          <Switch value={termsAccepted} onValueChange={setTermsAccepted} />
          <Text style={styles.switchLabel}>I accept the Terms & Agreement</Text>
        </View>

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <Pressable style={styles.primaryButton} onPress={handleRegister}>
          <Text style={styles.primaryButtonText}>Create Account</Text>
        </Pressable>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  hero: {
    marginBottom: 18,
  },
  title: {
    fontSize: 28,
    fontFamily: fonts.heading,
    color: colors.ink,
  },
  subtitle: {
    marginTop: 6,
    color: colors.muted,
    fontFamily: fonts.body,
  },
  card: {
    padding: 18,
    backgroundColor: colors.card,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: colors.border,
  },
  sectionTitle: {
    fontFamily: fonts.heading,
    fontSize: 16,
    marginBottom: 10,
  },
  toggleRow: {
    flexDirection: 'row',
    gap: 10,
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
  label: {
    marginTop: 12,
    marginBottom: 6,
    fontSize: 12,
    textTransform: 'uppercase',
    color: colors.muted,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: 12,
    backgroundColor: '#FFFDF9',
    fontFamily: fonts.body,
  },
  switchRow: {
    marginTop: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  switchLabel: {
    color: colors.muted,
  },
  primaryButton: {
    marginTop: 18,
    backgroundColor: colors.accent,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  error: {
    marginTop: 10,
    color: '#B23A3A',
  },
});
