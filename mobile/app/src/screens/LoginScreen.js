import React, { useContext, useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors, fonts } from '../styles/theme';

// Maps to POST /api/mobile/login (Mobile/AuthController@login).
export default function LoginScreen({ navigation }) {
  const { signIn } = useContext(AuthContext);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleLogin = async () => {
    setError('');
    try {
      const response = await api.login({ email, password });
      await signIn(response.data.token);
    } catch (err) {
      const message = err?.response?.data?.message || 'Login failed.';
      setError(message);
    }
  };

  return (
    <ScreenWrapper>
      <View style={styles.hero}>
        <Text style={styles.title}>Welcome Back</Text>
        <Text style={styles.subtitle}>Sign in to manage listings, messages, and orders.</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.label}>Email</Text>
        <TextInput
          style={styles.input}
          autoCapitalize="none"
          keyboardType="email-address"
          placeholder="you@example.com"
          value={email}
          onChangeText={setEmail}
        />
        <Text style={styles.label}>Password</Text>
        <TextInput
          style={styles.input}
          secureTextEntry
          placeholder="Enter your password"
          value={password}
          onChangeText={setPassword}
        />
        {error ? <Text style={styles.error}>{error}</Text> : null}
        <Pressable style={styles.primaryButton} onPress={handleLogin}>
          <Text style={styles.primaryButtonText}>Sign In</Text>
        </Pressable>
        <View style={styles.row}>
          <Pressable onPress={() => navigation.navigate('Register')}>
            <Text style={styles.linkText}>Create account</Text>
          </Pressable>
          <Pressable onPress={() => navigation.navigate('ForgotPassword')}>
            <Text style={styles.linkText}>Forgot password</Text>
          </Pressable>
        </View>
      </View>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  hero: {
    marginBottom: 18,
  },
  title: {
    fontSize: 30,
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
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 18,
  },
  label: {
    marginTop: 12,
    marginBottom: 6,
    fontSize: 13,
    letterSpacing: 0.4,
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
  primaryButton: {
    marginTop: 16,
    backgroundColor: colors.accent,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  row: {
    marginTop: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  linkText: {
    color: colors.accent,
    fontWeight: '600',
  },
  error: {
    marginTop: 10,
    color: '#B23A3A',
  },
});
