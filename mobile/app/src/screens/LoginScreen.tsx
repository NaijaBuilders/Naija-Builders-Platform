import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useNavigation } from '@react-navigation/native';
import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Button, Card, Header, Input, Screen } from '../components';
import { useAppState } from '../context/AppContext';
import { API_BASE_URL, apiClient, getApiBaseUrl, setApiBaseUrl } from '../services';
import { theme } from '../theme';
import type { AuthStackParamList } from '../navigation/types';

type LoginNavigation = NativeStackNavigationProp<AuthStackParamList, 'Login'>;

export function LoginScreen() {
  const navigation = useNavigation<LoginNavigation>();
  const { login } = useAppState();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [checkingBackend, setCheckingBackend] = useState(false);
  const [backendStatus, setBackendStatus] = useState('');
  const [apiUrl, setApiUrl] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    getApiBaseUrl().then(setApiUrl).catch(() => undefined);
  }, []);

  const handleLogin = async () => {
    setLoading(true);
    setError('');

    try {
      await setApiBaseUrl(apiUrl);
      await login({ email: email.trim(), password });
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  const handleTestBackend = async () => {
    setCheckingBackend(true);
    setBackendStatus('');

    try {
      const savedUrl = await setApiBaseUrl(apiUrl);
      await apiClient.get('/health');
      setBackendStatus(`Backend connected: ${savedUrl}`);
    } catch (reason) {
      setBackendStatus(
        reason instanceof Error
          ? reason.message
          : `Could not reach ${apiUrl}`
      );
    } finally {
      setCheckingBackend(false);
    }
  };

  const handleUseStagingApi = async () => {
    const savedUrl = await setApiBaseUrl(API_BASE_URL);
    setApiUrl(savedUrl);
    setBackendStatus('');
    setError('');
  };

  return (
    <Screen scroll={false} contentContainerStyle={styles.screen}>
      <Card style={styles.card}>
        <Header
          eyebrow="Welcome back"
          title="Login"
          subtitle="Use your NaijaBuilders account email and password."
        />
        <Input
          autoCapitalize="none"
          autoCorrect={false}
          label="Backend API URL"
          onChangeText={setApiUrl}
          value={apiUrl}
        />
        <Button
          title="Use staging API"
          onPress={handleUseStagingApi}
          variant="outline"
        />
        <Input
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          onChangeText={setEmail}
          value={email}
        />
        <Input
          label="Password"
          onChangeText={setPassword}
          secureTextEntry
          value={password}
        />
        {error ? <Text style={styles.error}>{error}</Text> : null}
        {backendStatus ? <Text style={styles.status}>{backendStatus}</Text> : null}
        <Button
          title="Login"
          onPress={handleLogin}
          loading={loading}
          disabled={!apiUrl.trim() || !email.trim() || !password}
        />
        <View style={styles.actions}>
          <Button
            title="Create account"
            onPress={() => navigation.navigate('Signup')}
            variant="ghost"
            style={styles.linkButton}
          />
          <Button
            title="Forgot password"
            onPress={() => navigation.navigate('ForgotPassword')}
            variant="ghost"
            style={styles.linkButton}
          />
        </View>
        <Button
          title="Test backend"
          onPress={handleTestBackend}
          loading={checkingBackend}
          variant="outline"
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
  actions: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
  },
  linkButton: {
    flex: 1,
  },
  error: {
    color: theme.colors.danger,
    fontSize: theme.typography.small,
    fontWeight: '800',
    textAlign: 'center',
  },
  status: {
    color: theme.colors.textMuted,
    fontSize: theme.typography.small,
    fontWeight: '700',
    lineHeight: 18,
    textAlign: 'center',
  },
});
