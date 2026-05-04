import React, { useContext, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import ScreenWrapper from '../components/ScreenWrapper';
import { AppButton, Card, ErrorBanner, ScreenHeader, TextField } from '../components/ui';
import { AuthContext } from '../contexts/AuthContext';
import { api } from '../services/api';
import { colors } from '../styles/theme';

// Maps to POST /api/mobile/login (Mobile/AuthController@login).
export default function LoginScreen({ navigation }) {
  const { signIn } = useContext(AuthContext);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async () => {
    setError('');
    if (!email.trim() || !password) {
      setError('Enter your email and password to continue.');
      return;
    }

    setIsSubmitting(true);
    try {
      const response = await api.login({ email, password });
      await signIn(response.data.token, response.data.user || null);
    } catch (err) {
      const message = err?.response?.data?.message || 'Login failed.';
      setError(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <ScreenWrapper>
      <ScreenHeader title="Welcome Back" subtitle="Sign in to manage listings, messages, cart, saved products, and orders." />
      <Card>
        <TextField
          label="Email"
          autoCapitalize="none"
          keyboardType="email-address"
          placeholder="you@example.com"
          value={email}
          onChangeText={setEmail}
        />
        <TextField
          label="Password"
          secureTextEntry
          placeholder="Enter your password"
          value={password}
          onChangeText={setPassword}
        />
        <ErrorBanner message={error} />
        <AppButton label={isSubmitting ? 'Signing in...' : 'Sign In'} onPress={handleLogin} disabled={isSubmitting} />
        <View style={styles.row}>
          <Pressable onPress={() => navigation.navigate('Register')}>
            <Text style={styles.linkText}>Create account</Text>
          </Pressable>
          <Pressable onPress={() => navigation.navigate('ForgotPassword')}>
            <Text style={styles.linkText}>Forgot password</Text>
          </Pressable>
        </View>
      </Card>
    </ScreenWrapper>
  );
}

const styles = StyleSheet.create({
  row: {
    marginTop: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  linkText: {
    color: colors.accent,
    fontWeight: '600',
  },
});
